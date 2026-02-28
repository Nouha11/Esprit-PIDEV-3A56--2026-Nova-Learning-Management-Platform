<?php

namespace App\Controller\Front\users;

use App\Entity\users\StudentProfile;
use App\Repository\StudentProfileRepository;
use App\Service\ProfileCompletionService;
use App\Service\LoginHistoryService;
use App\Service\UserActivityService;
use App\Service\AIActivitySummaryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/student')]
final class StudentController extends AbstractController
{
    public function __construct(
        private ProfileCompletionService $profileCompletionService,
        private LoginHistoryService $loginHistoryService,
        private UserActivityService $userActivityService,
        private AIActivitySummaryService $activitySummaryService
    ) {}

    #[Route('/profile', name: 'app_student_profile', methods: ['GET'])]
    public function profile(): Response
    {
        $user = $this->getUser();
        
        // ADDED: PHPStan User Type Verification
        if (!$user instanceof \App\Entity\users\User) {
            return $this->redirectToRoute('app_login');
        }

        $student = $user->getStudentProfile();
        $completion = $this->profileCompletionService->calculateStudentCompletion($student);

        return $this->render('front/users/student/index.html.twig', [
            'student' => $student,
            'completion' => $completion,
        ]);
    }

    #[Route('/profile/edit', name: 'app_student_profile_edit', methods: ['GET', 'POST'])]
    public function editProfile(
        Request $request, 
        EntityManagerInterface $entityManager, 
        TranslatorInterface $translator,
        \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $user = $this->getUser();
        
        // ADDED: PHPStan User Type Verification
        if (!$user instanceof \App\Entity\users\User) {
            return $this->redirectToRoute('app_login');
        }

        $student = $user->getStudentProfile();

        if (!$student) {
            $this->addFlash('error', 'Student profile not found');
            return $this->redirectToRoute('app_home');
        }

        $completion = $this->profileCompletionService->calculateStudentCompletion($student);

        if ($request->isMethod('POST')) {
            // Get current locale for translations
            $locale = $request->getSession()->get('_locale', 'en');
            
            // Handle password change if provided
            $currentPassword = $request->request->get('current_password');
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');
            
            if ($currentPassword || $newPassword || $confirmPassword) {
                // Validate password change
                if (!$currentPassword) {
                    $this->addFlash('error', $translator->trans('Current password is required', [], 'validators', $locale));
                } elseif (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $this->addFlash('error', $translator->trans('Current password is incorrect', [], 'validators', $locale));
                } elseif (!$newPassword) {
                    $this->addFlash('error', $translator->trans('New password is required', [], 'validators', $locale));
                } elseif (strlen($newPassword) < 8) {
                    $this->addFlash('error', $translator->trans('Password must be at least 8 characters', [], 'validators', $locale));
                } elseif ($newPassword !== $confirmPassword) {
                    $this->addFlash('error', $translator->trans('Passwords do not match', [], 'validators', $locale));
                } else {
                    // Update password
                    $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                    $user->setPassword($hashedPassword);
                    $entityManager->flush();
                    
                    $this->addFlash('success', $translator->trans('Password updated successfully', [], 'validators', $locale));
                    return $this->redirectToRoute('app_student_profile');
                }
                
                // FIXED: Get session directly from request instead of container to avoid "Service not found" error
                /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
                $session = $request->getSession();
                
                // If password validation failed, return early
                if ($session->getFlashBag()->has('error')) {
                    return $this->render('front/users/student/edit.html.twig', [
                        'student' => $student,
                        'completion' => $completion,
                    ]);
                }
            }
            
            // Handle avatar upload
            $avatarFile = $request->files->get('avatarFile');
            if ($avatarFile) {
                $student->setAvatarFile($avatarFile);
            }
            
            // Validate required fields
            $firstName = trim($request->request->get('firstName'));
            $lastName = trim($request->request->get('lastName'));
            $university = trim($request->request->get('university'));
            
            $errors = [];
            
            if (empty($firstName)) {
                $errors[] = $translator->trans('First name is required', [], 'validators', $locale);
            } elseif (strlen($firstName) < 2) {
                $errors[] = $translator->trans('First name must be at least {{ limit }} characters', ['{{ limit }}' => 2], 'validators', $locale);
            }
            
            if (empty($lastName)) {
                $errors[] = $translator->trans('Last name is required', [], 'validators', $locale);
            } elseif (strlen($lastName) < 2) {
                $errors[] = $translator->trans('Last name must be at least {{ limit }} characters', ['{{ limit }}' => 2], 'validators', $locale);
            }
            
            if (empty($university)) {
                $errors[] = $translator->trans('University is required', [], 'validators', $locale);
            } elseif (strlen($university) < 3) {
                $errors[] = $translator->trans('University must be at least {{ limit }} characters', ['{{ limit }}' => 3], 'validators', $locale);
            }
            
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                // Clear the file from entity to prevent serialization issues
                $student->setAvatarFile(null);
                return $this->render('front/users/student/edit.html.twig', [
                    'student' => $student,
                    'completion' => $completion,
                ]);
            }
            
            $student->setFirstName($firstName);
            $student->setLastName($lastName);
            $student->setBio($request->request->get('bio'));
            $student->setUniversity($university);
            $student->setMajor($request->request->get('major'));
            $student->setAcademicLevel($request->request->get('academicLevel'));
            
            // Convert interests string to array
            $interestsString = $request->request->get('interests');
            if ($interestsString) {
                $interestsArray = array_filter(array_map('trim', explode(',', $interestsString)));
                $student->setInterests($interestsArray);
            } else {
                $student->setInterests(null);
            }
            
            $entityManager->flush();
            
            // Clear the file from entity after flush to prevent serialization issues
            $student->setAvatarFile(null);

            $successMessage = $translator->trans('Student profile updated successfully.', [], 'validators', $locale);
            $this->addFlash('success', $successMessage);
            return $this->redirectToRoute('app_student_profile');
        }

        return $this->render('front/users/student/edit.html.twig', [
            'student' => $student,
            'completion' => $completion,
        ]);
    }

    #[Route('/dashboard', name: 'app_student_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        $user = $this->getUser();
        
        // ADDED: PHPStan User Type Verification
        if (!$user instanceof \App\Entity\users\User) {
            return $this->redirectToRoute('app_login');
        }

        $student = $user->getStudentProfile();
        $completion = $this->profileCompletionService->calculateStudentCompletion($student);
        
        // Get recent login history
        $recentLogins = $this->loginHistoryService->getRecentLogins($user, 5);
        
        // Get recent activities
        $recentActivities = $this->userActivityService->getRecentActivities($user, 10);
        
        // Get smart activity summary
        $activitySummary = $this->activitySummaryService->generateSummary($user);

        return $this->render('front/users/student/dashboard.html.twig', [
            'student' => $student,
            'completion' => $completion,
            'recentLogins' => $recentLogins,
            'recentActivities' => $recentActivities,
            'activitySummary' => $activitySummary,
        ]);
    }

    #[Route('/courses', name: 'app_student_courses', methods: ['GET'])]
    public function courses(): Response
    {
        $user = $this->getUser();
        
        // ADDED: PHPStan User Type Verification
        if (!$user instanceof \App\Entity\users\User) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('front/users/student/courses.html.twig', [
            'user' => $user,
        ]);
    }
}