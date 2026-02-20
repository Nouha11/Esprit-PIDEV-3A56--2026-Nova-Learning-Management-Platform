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
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\EmailVerificationService;
use App\Service\PasswordResetService;

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
        UserRepository $userRepository,
        TranslatorInterface $translator,
        EmailVerificationService $emailService
    ): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            // Get current locale for translations
            $locale = $request->getSession()->get('_locale', 'en');
            
            $username = $request->request->get('username');
            $email = $request->request->get('email');

            // Check if username already exists
            $existingUserByUsername = $userRepository->findOneBy(['username' => $username]);
            if ($existingUserByUsername) {
                $this->addFlash('error', $translator->trans('This username is already taken', [], 'validators', $locale));
                return $this->render('security/signup_student.html.twig', [
                    'formData' => $request->request->all()
                ]);
            }

            // Check if email already exists
            $existingUserByEmail = $userRepository->findOneBy(['email' => $email]);
            if ($existingUserByEmail) {
                $this->addFlash('error', $translator->trans('This email is already registered', [], 'validators', $locale));
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
            $user->setIsVerified(false); // Email not verified yet
            
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

                // Send verification email in the user's selected language
                $emailService->sendVerificationEmail($user, $locale);

                $this->addFlash('success', $translator->trans('Student account created successfully! Please check your email to verify your account.', [], 'validators', $locale));
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('An error occurred while creating your account. Please try again.', [], 'validators', $locale));
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
        UserRepository $userRepository,
        TranslatorInterface $translator,
        EmailVerificationService $emailService
    ): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            // Get current locale for translations
            $locale = $request->getSession()->get('_locale', 'en');
            
            $username = $request->request->get('username');
            $email = $request->request->get('email');

            // Check if username already exists
            $existingUserByUsername = $userRepository->findOneBy(['username' => $username]);
            if ($existingUserByUsername) {
                $this->addFlash('error', $translator->trans('This username is already taken', [], 'validators', $locale));
                return $this->render('security/signup_tutor.html.twig', [
                    'formData' => $request->request->all()
                ]);
            }

            // Check if email already exists
            $existingUserByEmail = $userRepository->findOneBy(['email' => $email]);
            if ($existingUserByEmail) {
                $this->addFlash('error', $translator->trans('This email is already registered', [], 'validators', $locale));
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
            $user->setIsVerified(false); // Email not verified yet
            
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

                // Send verification email in the user's selected language
                $emailService->sendVerificationEmail($user, $locale);

                $this->addFlash('success', $translator->trans('Tutor account created successfully! Please check your email to verify your account.', [], 'validators', $locale));
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('An error occurred while creating your account. Please try again.', [], 'validators', $locale));
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

    #[Route('/verify-email/{token}', name: 'app_verify_email')]
    public function verifyEmail(
        string $token,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response
    {
        // Get current locale for translated messages
        $locale = $request->getSession()->get('_locale', 'en');

        // Find user by verification token
        $user = $userRepository->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            $this->addFlash('error', $locale === 'fr' 
                ? 'Lien de vérification invalide.' 
                : 'Invalid verification link.');
            return $this->redirectToRoute('app_login');
        }

        // Check if token has expired
        $now = new \DateTime();
        if ($user->getVerificationTokenExpiresAt() < $now) {
            $this->addFlash('error', $locale === 'fr' 
                ? 'Le lien de vérification a expiré. Veuillez demander un nouveau lien.' 
                : 'Verification link has expired. Please request a new one.');
            return $this->redirectToRoute('app_login');
        }

        // Verify the user
        $user->setIsVerified(true);
        $user->setVerificationToken(null);
        $user->setVerificationTokenExpiresAt(null);
        
        $entityManager->flush();

        $this->addFlash('success', $locale === 'fr' 
            ? 'Votre email a été vérifié avec succès ! Vous pouvez maintenant vous connecter.' 
            : 'Your email has been verified successfully! You can now log in.');
        
        return $this->redirectToRoute('app_login');
    }

    #[Route('/resend-verification', name: 'app_resend_verification', methods: ['POST'])]
    public function resendVerification(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        EmailVerificationService $emailService
    ): Response
    {
        $locale = $request->getSession()->get('_locale', 'en');
        $email = $request->request->get('email');

        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $this->addFlash('error', $locale === 'fr' 
                ? 'Aucun compte trouvé avec cet email.' 
                : 'No account found with this email.');
            return $this->redirectToRoute('app_login');
        }

        if ($user->isVerified()) {
            $this->addFlash('info', $locale === 'fr' 
                ? 'Votre compte est déjà vérifié.' 
                : 'Your account is already verified.');
            return $this->redirectToRoute('app_login');
        }

        // Send new verification email
        $emailService->sendVerificationEmail($user, $locale);
        $entityManager->flush();

        $this->addFlash('success', $locale === 'fr' 
            ? 'Un nouvel email de vérification a été envoyé.' 
            : 'A new verification email has been sent.');
        
        return $this->redirectToRoute('app_login');
    }

    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        PasswordResetService $passwordResetService
    ): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            $locale = $request->getSession()->get('_locale', 'en');
            $email = $request->request->get('email');

            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                // Send password reset email
                $passwordResetService->sendPasswordResetEmail($user, $locale);
                $entityManager->flush();
            }

            // Always show success message (security best practice)
            $this->addFlash('success', $locale === 'fr' 
                ? 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.' 
                : 'If an account exists with this email, a reset link has been sent.');
            
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(
        string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $locale = $request->getSession()->get('_locale', 'en');

        // Find user by reset token
        $user = $userRepository->findOneBy(['resetPasswordToken' => $token]);

        if (!$user) {
            $this->addFlash('error', $locale === 'fr' 
                ? 'Lien de réinitialisation invalide.' 
                : 'Invalid reset link.');
            return $this->redirectToRoute('app_login');
        }

        // Check if token has expired
        $now = new \DateTime();
        if ($user->getResetPasswordTokenExpiresAt() < $now) {
            $this->addFlash('error', $locale === 'fr' 
                ? 'Le lien de réinitialisation a expiré. Veuillez demander un nouveau lien.' 
                : 'Reset link has expired. Please request a new one.');
            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirmPassword');

            // Validate passwords
            if (empty($password) || strlen($password) < 8) {
                $this->addFlash('error', $locale === 'fr' 
                    ? 'Le mot de passe doit contenir au moins 8 caractères.' 
                    : 'Password must be at least 8 characters.');
                return $this->render('security/reset_password.html.twig', ['token' => $token]);
            }

            if ($password !== $confirmPassword) {
                $this->addFlash('error', $locale === 'fr' 
                    ? 'Les mots de passe ne correspondent pas.' 
                    : 'Passwords do not match.');
                return $this->render('security/reset_password.html.twig', ['token' => $token]);
            }

            // Update password
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
            $user->setResetPasswordToken(null);
            $user->setResetPasswordTokenExpiresAt(null);
            
            $entityManager->flush();

            $this->addFlash('success', $locale === 'fr' 
                ? 'Votre mot de passe a été réinitialisé avec succès ! Vous pouvez maintenant vous connecter.' 
                : 'Your password has been reset successfully! You can now log in.');
            
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', ['token' => $token]);
    }
}
