<?php

namespace App\Controller\Front\users;

use App\Entity\users\TutorProfile;
use App\Entity\users\User; // Make sure this path matches your User entity
use App\Repository\TutorProfileRepository;
use App\Service\ProfileCompletionService;
use App\Service\LoginHistoryService;
use App\Service\UserActivityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted; // Added for security
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/tutor')]
#[IsGranted('ROLE_TUTOR')] // Restrict this whole controller to Tutors only
final class TutorController extends AbstractController
{
    public function __construct(
        private ProfileCompletionService $profileCompletionService,
        private LoginHistoryService $loginHistoryService,
        private UserActivityService $userActivityService
    ) {}

    #[Route('/profile', name: 'app_tutor_profile', methods: ['GET'])]
    public function profile(): Response
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $tutor = $user->getTutorProfile();
        $completion = $this->profileCompletionService->calculateTutorCompletion($tutor);

        return $this->render('front/users/tutor/index.html.twig', [
            'tutor' => $tutor,
            'completion' => $completion,
        ]);
    }

    #[Route('/profile/edit', name: 'app_tutor_profile_edit', methods: ['GET', 'POST'])]
    public function editProfile(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $tutor = $user->getTutorProfile();

        if (!$tutor) {
            $this->addFlash('error', 'Tutor profile not found');
            return $this->redirectToRoute('app_home');
        }

        $completion = $this->profileCompletionService->calculateTutorCompletion($tutor);

        if ($request->isMethod('POST')) {
            // Get current locale for translations
            $locale = $request->getSession()->get('_locale', 'en');
            
            // Handle avatar upload
            $avatarFile = $request->files->get('avatarFile');
            if ($avatarFile) {
                $tutor->setAvatarFile($avatarFile);
            }
            
            // Validate required fields
            $firstName = trim($request->request->get('firstName'));
            $lastName = trim($request->request->get('lastName'));
            $expertiseString = trim($request->request->get('expertise'));
            $yearsOfExperience = $request->request->get('yearsOfExperience');
            
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
            
            if (empty($expertiseString)) {
                $errors[] = $translator->trans('Expertise is required', [], 'validators', $locale);
            } elseif (strlen($expertiseString) < 3) {
                $errors[] = $translator->trans('Expertise must be at least {{ limit }} characters', ['{{ limit }}' => 3], 'validators', $locale);
            }
            
            if ($yearsOfExperience === null || $yearsOfExperience === '') {
                $errors[] = $translator->trans('Years of experience is required', [], 'validators', $locale);
            } elseif ($yearsOfExperience < 0 || $yearsOfExperience > 50) {
                $errors[] = $translator->trans('Years of experience must be between {{ min }} and {{ max }}', ['{{ min }}' => 0, '{{ max }}' => 50], 'validators', $locale);
            }
            
            // Handle password change if provided
            $currentPassword = $request->request->get('current_password');
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');
            
            if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
                if (empty($currentPassword)) {
                    $errors[] = $translator->trans('Current password is required to change password', [], 'validators', $locale);
                } elseif (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $errors[] = $translator->trans('Current password is incorrect', [], 'validators', $locale);
                } elseif (empty($newPassword)) {
                    $errors[] = $translator->trans('New password is required', [], 'validators', $locale);
                } elseif (strlen($newPassword) < 8) {
                    $errors[] = $translator->trans('New password must be at least {{ limit }} characters', ['{{ limit }}' => 8], 'validators', $locale);
                } elseif ($newPassword !== $confirmPassword) {
                    $errors[] = $translator->trans('Passwords do not match', [], 'validators', $locale);
                }
                
                if (empty($errors)) {
                    // Update password
                    $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                    $user->setPassword($hashedPassword);
                    
                    // FIXED: Removed undefined methods setResetToken and setResetTokenExpiresAt
                    
                    $this->addFlash('success', $translator->trans('Password changed successfully', [], 'validators', $locale));
                }
            }
            
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                // Clear the file from entity to prevent serialization issues
                $tutor->setAvatarFile(null);
                return $this->render('front/users/tutor/edit.html.twig', [
                    'tutor' => $tutor,
                    'completion' => $completion,
                ]);
            }
            
            $tutor->setFirstName($firstName);
            $tutor->setLastName($lastName);
            $tutor->setBio($request->request->get('bio'));
            
            // Convert expertise string to array
            if ($expertiseString) {
                $expertiseArray = array_filter(array_map('trim', explode(',', $expertiseString)));
                $tutor->setExpertise($expertiseArray);
            } else {
                $tutor->setExpertise(null);
            }
            
            $tutor->setQualifications($request->request->get('qualifications'));
            $tutor->setYearsOfExperience((int)$yearsOfExperience);
            $tutor->setHourlyRate($request->request->get('hourlyRate'));
            $tutor->setIsAvailable($request->request->get('isAvailable') === '1');
            
            $entityManager->flush();
            
            // Clear the file from entity after flush to prevent serialization issues
            $tutor->setAvatarFile(null);

            $successMessage = $translator->trans('Tutor profile updated successfully.', [], 'validators', $locale);
            $this->addFlash('success', $successMessage);
            return $this->redirectToRoute('app_tutor_profile');
        }

        return $this->render('front/users/tutor/edit.html.twig', [
            'tutor' => $tutor,
            'completion' => $completion,
        ]);
    }

    #[Route('/dashboard', name: 'app_tutor_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $tutor = $user->getTutorProfile();
        $completion = $this->profileCompletionService->calculateTutorCompletion($tutor);
        
        // Get recent login history
        $recentLogins = $this->loginHistoryService->getRecentLogins($user, 5);
        
        // Get recent activities
        $recentActivities = $this->userActivityService->getRecentActivities($user, 10);

        return $this->render('front/users/tutor/dashboard.html.twig', [
            'tutor' => $tutor,
            'completion' => $completion,
            'recentLogins' => $recentLogins,
            'recentActivities' => $recentActivities,
        ]);
    }

    #[Route('/sessions', name: 'app_tutor_sessions', methods: ['GET'])]
    public function sessions(): Response
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('front/users/tutor/sessions.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/availability', name: 'app_tutor_availability', methods: ['GET', 'POST'])]
    public function availability(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $tutor = $user->getTutorProfile();

        if ($request->isMethod('POST') && $tutor) {
            $tutor->setIsAvailable($request->request->get('isAvailable') === '1');
            $entityManager->flush();

            $this->addFlash('success', 'Availability updated successfully.');
            return $this->redirectToRoute('app_tutor_dashboard');
        }

        return $this->render('front/users/tutor/availability.html.twig', [
            'tutor' => $tutor,
        ]);
    }
}