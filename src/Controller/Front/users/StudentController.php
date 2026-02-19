<?php

namespace App\Controller\Front\users;

use App\Entity\users\StudentProfile;
use App\Repository\StudentProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/student')]
final class StudentController extends AbstractController
{
    #[Route('/profile', name: 'app_student_profile', methods: ['GET'])]
    public function profile(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $student = $user->getStudentProfile();

        return $this->render('front/users/student/index.html.twig', [
            'student' => $student,
        ]);
    }

    #[Route('/profile/edit', name: 'app_student_profile_edit', methods: ['GET', 'POST'])]
    public function editProfile(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $student = $user->getStudentProfile();

        if (!$student) {
            $this->addFlash('error', 'Student profile not found');
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            // Get current locale for translations
            $locale = $request->getSession()->get('_locale', 'en');
            
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
                return $this->render('front/users/student/edit.html.twig', [
                    'student' => $student,
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

            $successMessage = $translator->trans('Student profile updated successfully.', [], 'validators', $locale);
            $this->addFlash('success', $successMessage);
            return $this->redirectToRoute('app_student_profile');
        }

        return $this->render('front/users/student/edit.html.twig', [
            'student' => $student,
        ]);
    }

    #[Route('/dashboard', name: 'app_student_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $student = $user->getStudentProfile();

        return $this->render('front/users/student/dashboard.html.twig', [
            'student' => $student,
        ]);
    }

    #[Route('/courses', name: 'app_student_courses', methods: ['GET'])]
    public function courses(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('front/users/student/courses.html.twig', [
            'user' => $user,
        ]);
    }
}
