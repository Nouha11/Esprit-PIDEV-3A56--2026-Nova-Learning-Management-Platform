<?php

namespace App\Controller\Front\users;

use App\Entity\users\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route('/profile', name: 'app_user_profile', methods: ['GET'])]
    public function profile(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('front/users/user/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/edit', name: 'app_user_profile_edit', methods: ['GET', 'POST'])]
    public function editProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($request->isMethod('POST')) {
            $user->setUsername($request->request->get('username'));
            $user->setEmail($request->request->get('email'));

            if ($request->request->get('password')) {
                $user->setPassword(password_hash($request->request->get('password'), PASSWORD_BCRYPT));
            }

            $entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('front/users/user/edit.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/dashboard', name: 'app_user_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('front/users/user/dashboard.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/settings', name: 'app_user_settings', methods: ['GET', 'POST'])]
    public function settings(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($request->isMethod('POST')) {
            $user->setEmail($request->request->get('email'));

            $entityManager->flush();

            $this->addFlash('success', 'Settings updated successfully.');
            return $this->redirectToRoute('app_user_settings');
        }

        return $this->render('front/users/user/settings.html.twig', [
            'user' => $user,
        ]);
    }
}
