<?php

namespace App\Controller\Front\users;

use App\Entity\users\TutorProfile;
use App\Repository\TutorProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tutor')]
final class TutorController extends AbstractController
{
    #[Route('/profile', name: 'app_tutor_profile', methods: ['GET'])]
    public function profile(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $tutor = $user->getTutorProfile();

        return $this->render('front/users/tutor/index.html.twig', [
            'tutor' => $tutor,
        ]);
    }

    #[Route('/profile/edit', name: 'app_tutor_profile_edit', methods: ['GET', 'POST'])]
    public function editProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $tutor = $user->getTutorProfile();

        if (!$tutor) {
            $this->addFlash('error', 'Tutor profile not found');
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            $tutor->setFirstName($request->request->get('firstName'));
            $tutor->setLastName($request->request->get('lastName'));
            $tutor->setBio($request->request->get('bio'));
            $tutor->setExpertise($request->request->get('expertise'));
            $tutor->setQualifications($request->request->get('qualifications'));
            $tutor->setYearsOfExperience((int)$request->request->get('yearsOfExperience'));
            $tutor->setHourlyRate($request->request->get('hourlyRate'));
            $tutor->setIsAvailable($request->request->get('isAvailable') === '1');
            
            $entityManager->flush();

            $this->addFlash('success', 'Tutor profile updated successfully.');
            return $this->redirectToRoute('app_tutor_profile');
        }

        return $this->render('front/users/tutor/edit.html.twig', [
            'tutor' => $tutor,
        ]);
    }

    #[Route('/dashboard', name: 'app_tutor_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $tutor = $user->getTutorProfile();

        return $this->render('front/users/tutor/dashboard.html.twig', [
            'tutor' => $tutor,
        ]);
    }

    #[Route('/sessions', name: 'app_tutor_sessions', methods: ['GET'])]
    public function sessions(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
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
        
        if (!$user) {
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
