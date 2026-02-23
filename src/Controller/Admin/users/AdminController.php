<?php

namespace App\Controller\Admin\users;

use App\Entity\users\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/users')]
final class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_users_list', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        
        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/new', name: 'app_admin_users_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository): Response
    {
        if ($request->isMethod('POST')) {
            $username = trim($request->request->get('username'));
            $email = trim($request->request->get('email'));
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirmPassword');
            $role = $request->request->get('role');
            
            $errors = [];
            
            // Validate required fields
            if (empty($username)) {
                $errors[] = 'Username is required';
            } elseif (strlen($username) < 3) {
                $errors[] = 'Username must be at least 3 characters';
            } elseif ($userRepository->findOneBy(['username' => $username])) {
                $errors[] = 'Username already exists';
            }
            
            if (empty($email)) {
                $errors[] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            } elseif ($userRepository->findOneBy(['email' => $email])) {
                $errors[] = 'Email already exists';
            }
            
            if (empty($password)) {
                $errors[] = 'Password is required';
            } elseif (strlen($password) < 8) {
                $errors[] = 'Password must be at least 8 characters';
            }
            
            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }
            
            if (empty($role)) {
                $errors[] = 'Role is required';
            } elseif (!in_array($role, ['ROLE_STUDENT', 'ROLE_TUTOR', 'ROLE_ADMIN'])) {
                $errors[] = 'Invalid role selected';
            }
            
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('admin/users/admin/new.html.twig', [
                    'formData' => $request->request->all()
                ]);
            }
            
            $user = new User();
            $user->setUsername($username);
            $user->setEmail($email);
            
            // Hash password
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
            
            $user->setRole($role);
            $user->setIsActive($request->request->get('isActive') === '1');
            $user->setIsVerified(false); // Require email verification

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User created successfully.');
            return $this->redirectToRoute('app_admin_users_list');
        }

        return $this->render('admin/users/admin/new.html.twig');
    }

    #[Route('/{id}', name: 'app_admin_users_show', methods: ['GET'])]
    public function show(User $user, EntityManagerInterface $entityManager): Response
    {
        // Get login history
        $loginHistory = $entityManager->getRepository(\App\Entity\users\LoginHistory::class)
            ->findBy(['user' => $user], ['createdAt' => 'DESC'], 10);
        
        // Get recent activities
        $recentActivities = $entityManager->getRepository(\App\Entity\users\UserActivity::class)
            ->findBy(['user' => $user], ['createdAt' => 'DESC'], 10);
        
        return $this->render('admin/users/admin/show.html.twig', [
            'user' => $user,
            'loginHistory' => $loginHistory,
            'recentActivities' => $recentActivities,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_users_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($request->isMethod('POST')) {
            $user->setUsername($request->request->get('username'));
            $user->setEmail($request->request->get('email'));
            $user->setRole($request->request->get('role'));
            $user->setIsActive($request->request->get('isActive') === '1');

            if ($request->request->get('password')) {
                $plaintextPassword = $request->request->get('password');
                $hashedPassword = $passwordHasher->hashPassword($user, $plaintextPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            $this->addFlash('success', 'User updated successfully.');
            return $this->redirectToRoute('app_admin_users_list');
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_users_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            try {
                // Manually remove related entities that don't have cascade delete
                
                // Remove study streak if exists
                $studyStreak = $entityManager->getRepository(\App\Entity\StudySession\StudyStreak::class)
                    ->findOneBy(['user' => $user]);
                if ($studyStreak) {
                    $entityManager->remove($studyStreak);
                }
                
                // Remove enrollment requests
                $enrollmentRequests = $entityManager->getRepository(\App\Entity\StudySession\EnrollmentRequest::class)
                    ->findBy(['student' => $user]);
                foreach ($enrollmentRequests as $request) {
                    $entityManager->remove($request);
                }
                
                // Remove quiz reports
                $quizReports = $entityManager->getRepository(\App\Entity\Quiz\QuizReport::class)
                    ->findBy(['reportedBy' => $user]);
                foreach ($quizReports as $report) {
                    $entityManager->remove($report);
                }
                
                // Remove library loans
                $loans = $entityManager->getRepository(\App\Entity\Library\Loan::class)
                    ->findBy(['user' => $user]);
                foreach ($loans as $loan) {
                    $entityManager->remove($loan);
                }
                
                // Remove digital purchases
                $purchases = $entityManager->getRepository(\App\Entity\Library\DigitalPurchase::class)
                    ->findBy(['user' => $user]);
                foreach ($purchases as $purchase) {
                    $entityManager->remove($purchase);
                }
                
                // Remove user library entries
                $userLibraries = $entityManager->getRepository(\App\Entity\Library\UserLibrary::class)
                    ->findBy(['user' => $user]);
                foreach ($userLibraries as $userLibrary) {
                    $entityManager->remove($userLibrary);
                }
                
                // Finally remove the user (cascade will handle profiles, sessions, activities, notifications, login history)
                $entityManager->remove($user);
                $entityManager->flush();
                
                $this->addFlash('success', 'User deleted successfully.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to delete user: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('app_admin_users_list');
    }

    #[Route('/{id}/toggle-status', name: 'app_admin_users_toggle_status', methods: ['POST'])]
    public function toggleStatus(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setIsActive(!$user->isActive());
        $entityManager->flush();

        $this->addFlash('success', 'User status updated successfully.');
        return $this->redirectToRoute('app_admin_users_list');
    }

    #[Route('/{id}/ban', name: 'app_admin_users_ban_form', methods: ['GET'])]
    public function banForm(User $user): Response
    {
        return $this->render('admin/users/ban.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/ban', name: 'app_admin_users_ban', methods: ['POST'])]
    public function banUser(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $reason = $request->request->get('ban_reason', 'No reason provided');
        
        $user->setIsBanned(true);
        $user->setBanReason($reason);
        $user->setBannedAt(new \DateTime());
        $user->setIsActive(false); // Also deactivate the account
        
        $entityManager->flush();

        $this->addFlash('success', 'User has been banned successfully.');
        return $this->redirectToRoute('app_admin_users_list');
    }

    #[Route('/{id}/unban', name: 'app_admin_users_unban', methods: ['POST'])]
    public function unbanUser(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setIsBanned(false);
        $user->setBanReason(null);
        $user->setBannedAt(null);
        $user->setIsActive(true); // Reactivate the account
        
        $entityManager->flush();

        $this->addFlash('success', 'User has been unbanned successfully.');
        return $this->redirectToRoute('app_admin_users_list');
    }

    #[Route('/bulk-action', name: 'app_admin_users_bulk_action', methods: ['POST'])]
    public function bulkAction(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $action = $request->request->get('bulk_action');
        $userIds = $request->request->all('user_ids');

        if (empty($userIds)) {
            $this->addFlash('error', 'No users selected.');
            return $this->redirectToRoute('app_admin_users_list');
        }

        $users = $userRepository->findBy(['id' => $userIds]);
        $count = 0;

        foreach ($users as $user) {
            switch ($action) {
                case 'activate':
                    $user->setIsActive(true);
                    $count++;
                    break;
                case 'deactivate':
                    $user->setIsActive(false);
                    $count++;
                    break;
                case 'ban':
                    $user->setIsBanned(true);
                    $user->setBanReason('Bulk ban action');
                    $user->setBannedAt(new \DateTime());
                    $user->setIsActive(false);
                    $count++;
                    break;
                case 'unban':
                    $user->setIsBanned(false);
                    $user->setBanReason(null);
                    $user->setBannedAt(null);
                    $user->setIsActive(true);
                    $count++;
                    break;
                case 'delete':
                    $entityManager->remove($user);
                    $count++;
                    break;
            }
        }

        $entityManager->flush();

        $this->addFlash('success', "Bulk action completed successfully. {$count} user(s) affected.");
        return $this->redirectToRoute('app_admin_users_list');
    }
}
