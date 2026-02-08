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
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public function signupStudent(
        Request $request, 
        EntityManagerInterface $entityManager, 
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        UserRepository $userRepository
    ): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            $username = $request->request->get('username');
            $email = $request->request->get('email');

            // Check if username already exists
            $existingUserByUsername = $userRepository->findOneBy(['username' => $username]);
            if ($existingUserByUsername) {
                $this->addFlash('error', 'This username is already taken. Please choose another one.');
                return $this->render('security/signup_student.html.twig', [
                    'formData' => $request->request->all()
                ]);
            }

            // Check if email already exists
            $existingUserByEmail = $userRepository->findOneBy(['email' => $email]);
            if ($existingUserByEmail) {
                $this->addFlash('error', 'This email is already registered. Please use another email or login.');
                return $this->render('security/signup_student.html.twig', [
                    'formData' => $request->request->all()
                ]);
            }

            // Create User
            $user = new User();
            $user->setUsername($username);
            $user->setEmail($email);
            
            // Hash password
            $plaintextPassword = $request->request->get('password');
            $hashedPassword = $passwordHasher->hashPassword($user, $plaintextPassword);
            $user->setPassword($hashedPassword);
            
            $user->setRole('ROLE_STUDENT');
            $user->setIsActive(true);
            
            // Create StudentProfile
            $student = new StudentProfile();
            $student->setFirstName($request->request->get('firstName'));
            $student->setLastName($request->request->get('lastName'));
            $student->setUniversity($request->request->get('university'));
            $student->setMajor($request->request->get('major'));
            $student->setAcademicLevel($request->request->get('academicLevel'));

            // Link profile to user
            $user->setStudentProfile($student);

            // Validate the user entity
            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                return $this->render('security/signup_student.html.twig', [
                    'formData' => $request->request->all()
                ]);
            }

            try {
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Student account created successfully! Please login.');
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while creating your account. Please try again.');
            }
        }

        return $this->render('security/signup_student.html.twig');
    }

    #[Route('/signup/tutor', name: 'app_signup_tutor', methods: ['GET', 'POST'])]
    public function signupTutor(
        Request $request, 
        EntityManagerInterface $entityManager, 
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        UserRepository $userRepository
    ): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            $username = $request->request->get('username');
            $email = $request->request->get('email');

            // Check if username already exists
            $existingUserByUsername = $userRepository->findOneBy(['username' => $username]);
            if ($existingUserByUsername) {
                $this->addFlash('error', 'This username is already taken. Please choose another one.');
                return $this->render('security/signup_tutor.html.twig', [
                    'formData' => $request->request->all()
                ]);
            }

            // Check if email already exists
            $existingUserByEmail = $userRepository->findOneBy(['email' => $email]);
            if ($existingUserByEmail) {
                $this->addFlash('error', 'This email is already registered. Please use another email or login.');
                return $this->render('security/signup_tutor.html.twig', [
                    'formData' => $request->request->all()
                ]);
            }

            // Create User
            $user = new User();
            $user->setUsername($username);
            $user->setEmail($email);
            
            // Hash password
            $plaintextPassword = $request->request->get('password');
            $hashedPassword = $passwordHasher->hashPassword($user, $plaintextPassword);
            $user->setPassword($hashedPassword);
            
            $user->setRole('ROLE_TUTOR');
            $user->setIsActive(true);
            
            // Create TutorProfile
            $tutor = new TutorProfile();
            $tutor->setFirstName($request->request->get('firstName'));
            $tutor->setLastName($request->request->get('lastName'));
            
            // Convert expertise string to array
            $expertiseString = $request->request->get('expertise');
            $expertiseArray = array_filter(array_map('trim', preg_split('/[,\n]+/', $expertiseString)));
            $tutor->setExpertise($expertiseArray);
            
            $tutor->setQualifications($request->request->get('qualifications'));
            $tutor->setYearsOfExperience((int)$request->request->get('yearsOfExperience'));
            $tutor->setHourlyRate($request->request->get('hourlyRate'));
            $tutor->setIsAvailable(true);

            // Link profile to user
            $user->setTutorProfile($tutor);

            // Validate the user entity
            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                return $this->render('security/signup_tutor.html.twig', [
                    'formData' => $request->request->all()
                ]);
            }

            try {
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Tutor account created successfully! Please login.');
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while creating your account. Please try again.');
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
