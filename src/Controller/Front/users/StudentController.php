<?php

namespace App\Controller\Front\users;

use App\Entity\users\StudentProfile;
use App\Repository\StudentProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
    public function editProfile(Request $request, EntityManagerInterface $entityManager): Response
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
            $student->setFirstName($request->request->get('firstName'));
            $student->setLastName($request->request->get('lastName'));
            $student->setBio($request->request->get('bio'));
            $student->setUniversity($request->request->get('university'));
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

            $this->addFlash('success', 'Student profile updated successfully.');
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
