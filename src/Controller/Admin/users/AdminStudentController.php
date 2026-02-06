<?php

namespace App\Controller\Admin\users;

use App\Entity\users\StudentProfile;
use App\Repository\StudentProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/students')]
final class AdminStudentController extends AbstractController
{
    #[Route('/', name: 'app_admin_students_list', methods: ['GET'])]
    public function index(StudentProfileRepository $studentRepository): Response
    {
        $students = $studentRepository->findAll();
        
        return $this->render('admin/users/admin_student/index.html.twig', [
            'students' => $students,
        ]);
    }

    #[Route('/new', name: 'app_admin_students_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $student = new StudentProfile();
            $student->setFirstName($request->request->get('firstName'));
            $student->setLastName($request->request->get('lastName'));
            $student->setBio($request->request->get('bio'));
            $student->setUniversity($request->request->get('university'));
            $student->setMajor($request->request->get('major'));
            $student->setAcademicLevel($request->request->get('academicLevel'));
            $student->setInterests($request->request->get('interests'));

            $entityManager->persist($student);
            $entityManager->flush();

            $this->addFlash('success', 'Student profile created successfully.');
            return $this->redirectToRoute('app_admin_students_list');
        }

        return $this->render('admin/users/admin_student/new.html.twig');
    }

    #[Route('/{id}', name: 'app_admin_students_show', methods: ['GET'])]
    public function show(StudentProfile $student): Response
    {
        return $this->render('admin/users/admin_student/show.html.twig', [
            'student' => $student,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_students_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StudentProfile $student, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $student->setFirstName($request->request->get('firstName'));
            $student->setLastName($request->request->get('lastName'));
            $student->setBio($request->request->get('bio'));
            $student->setUniversity($request->request->get('university'));
            $student->setMajor($request->request->get('major'));
            $student->setAcademicLevel($request->request->get('academicLevel'));
            $student->setInterests($request->request->get('interests'));

            $entityManager->flush();

            $this->addFlash('success', 'Student profile updated successfully.');
            return $this->redirectToRoute('app_admin_students_list');
        }

        return $this->render('admin/users/admin_student/edit.html.twig', [
            'student' => $student,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_students_delete', methods: ['POST'])]
    public function delete(Request $request, StudentProfile $student, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$student->getId(), $request->request->get('_token'))) {
            $entityManager->remove($student);
            $entityManager->flush();
            $this->addFlash('success', 'Student profile deleted successfully.');
        }

        return $this->redirectToRoute('app_admin_students_list');
    }
}
