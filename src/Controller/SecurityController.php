<?php

namespace App\Controller;

use App\Entity\users\User;
use App\Entity\users\StudentProfile;
use App\Entity\users\TutorProfile;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Redirect if already logged in
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/signup', name: 'app_signup')]
    public function signupChoice(): Response
    {
        // Redirect if already logged in
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/signup_choice.html.twig');
    }

    #[Route('/signup/student', name: 'app_signup_student', methods: ['GET', 'POST'])]
    public function signupStudent(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            // Create User
            $user = new User();
            $user->setUsername($request->request->get('username'));
            $user->setEmail($request->request->get('email'));
            
            // Hash password using Symfony's password hasher
            $plaintextPassword = $request->request->get('password');
            $hashedPassword = $passwordHasher->hashPassword($user, $plaintextPassword);
            $user->setPassword($hashedPassword);
            
            $user->setRole('ROLE_STUDENT');
            $user->setIsActive(true);

            // Create Student Profile
            $studentProfile = new StudentProfile();
            $studentProfile->setFirstName($request->request->get('firstName'));
            $studentProfile->setLastName($request->request->get('lastName'));
            $studentProfile->setUniversity($request->request->get('university'));
            $studentProfile->setMajor($request->request->get('major'));
            $studentProfile->setAcademicLevel($request->request->get('academicLevel'));

            try {
                $entityManager->persist($user);
                $entityManager->persist($studentProfile);
                $entityManager->flush();

                $this->addFlash('success', 'Student account created successfully! Please login.');
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
            }
        }

        return $this->render('security/signup_student.html.twig');
    }

    #[Route('/signup/tutor', name: 'app_signup_tutor', methods: ['GET', 'POST'])]
    public function signupTutor(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            // Create User
            $user = new User();
            $user->setUsername($request->request->get('username'));
            $user->setEmail($request->request->get('email'));
            
            // Hash password using Symfony's password hasher
            $plaintextPassword = $request->request->get('password');
            $hashedPassword = $passwordHasher->hashPassword($user, $plaintextPassword);
            $user->setPassword($hashedPassword);
            
            $user->setRole('ROLE_TUTOR');
            $user->setIsActive(true);

            // Create Tutor Profile
            $tutorProfile = new TutorProfile();
            $tutorProfile->setFirstName($request->request->get('firstName'));
            $tutorProfile->setLastName($request->request->get('lastName'));
            $tutorProfile->setExpertise($request->request->get('expertise'));
            $tutorProfile->setQualifications($request->request->get('qualifications'));
            $tutorProfile->setYearsOfExperience((int)$request->request->get('yearsOfExperience'));
            $tutorProfile->setHourlyRate($request->request->get('hourlyRate'));
            $tutorProfile->setIsAvailable(true);

            try {
                $entityManager->persist($user);
                $entityManager->persist($tutorProfile);
                $entityManager->flush();

                $this->addFlash('success', 'Tutor account created successfully! Please login.');
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
            }
        }

        return $this->render('security/signup_tutor.html.twig');
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(): Response
    {
        // Redirect to homepage after login
        return $this->redirectToRoute('app_home');
    }
}
