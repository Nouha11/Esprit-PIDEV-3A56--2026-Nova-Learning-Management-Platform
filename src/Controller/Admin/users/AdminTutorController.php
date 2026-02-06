<?php

namespace App\Controller\Admin\users;

use App\Entity\users\TutorProfile;
use App\Repository\TutorProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/tutors')]
final class AdminTutorController extends AbstractController
{
    #[Route('/', name: 'app_admin_tutors_list', methods: ['GET'])]
    public function index(TutorProfileRepository $tutorRepository): Response
    {
        $tutors = $tutorRepository->findAll();
        
        return $this->render('admin/users/admin_tutor/index.html.twig', [
            'tutors' => $tutors,
        ]);
    }

    #[Route('/new', name: 'app_admin_tutors_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $tutor = new TutorProfile();
            $tutor->setFirstName($request->request->get('firstName'));
            $tutor->setLastName($request->request->get('lastName'));
            $tutor->setBio($request->request->get('bio'));
            $tutor->setExpertise($request->request->get('expertise'));
            $tutor->setQualifications($request->request->get('qualifications'));
            $tutor->setYearsOfExperience((int)$request->request->get('yearsOfExperience'));
            $tutor->setHourlyRate($request->request->get('hourlyRate'));
            $tutor->setIsAvailable($request->request->get('isAvailable') === '1');

            $entityManager->persist($tutor);
            $entityManager->flush();

            $this->addFlash('success', 'Tutor profile created successfully.');
            return $this->redirectToRoute('app_admin_tutors_list');
        }

        return $this->render('admin/users/admin_tutor/new.html.twig');
    }

    #[Route('/{id}', name: 'app_admin_tutors_show', methods: ['GET'])]
    public function show(TutorProfile $tutor): Response
    {
        return $this->render('admin/users/admin_tutor/show.html.twig', [
            'tutor' => $tutor,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_tutors_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TutorProfile $tutor, EntityManagerInterface $entityManager): Response
    {
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
            return $this->redirectToRoute('app_admin_tutors_list');
        }

        return $this->render('admin/users/admin_tutor/edit.html.twig', [
            'tutor' => $tutor,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_tutors_delete', methods: ['POST'])]
    public function delete(Request $request, TutorProfile $tutor, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tutor->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tutor);
            $entityManager->flush();
            $this->addFlash('success', 'Tutor profile deleted successfully.');
        }

        return $this->redirectToRoute('app_admin_tutors_list');
    }

    #[Route('/{id}/toggle-availability', name: 'app_admin_tutors_toggle_availability', methods: ['POST'])]
    public function toggleAvailability(TutorProfile $tutor, EntityManagerInterface $entityManager): Response
    {
        $tutor->setIsAvailable(!$tutor->isAvailable());
        $entityManager->flush();

        $this->addFlash('success', 'Tutor availability updated successfully.');
        return $this->redirectToRoute('app_admin_tutors_list');
    }
}
