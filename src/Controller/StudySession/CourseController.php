<?php

namespace App\Controller\StudySession;

use App\Entity\StudySession\Course;
use App\Form\StudySession\CourseType;
use App\Repository\StudySession\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/courses')]
class CourseController extends AbstractController
{
    #[Route('/', name: 'course_index')]
    public function index(
        CourseRepository $repository,
        Request $request
    ): Response {
        $difficulty = $request->query->get('difficulty');
        $category = $request->query->get('category');

        $courses = $repository->findByFilters($difficulty, $category);

        return $this->render('course/index.html.twig', [
            'courses' => $courses
        ]);
    }

    #[Route('/new', name: 'course_new')]
    #[IsGranted('ROLE_TUTOR')]
    public function new(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $course = new Course();
        $course->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($course);
            $em->flush();

            $this->addFlash('success', 'Course created successfully');
            return $this->redirectToRoute('course_index');
        }

        return $this->render('course/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}/edit', name: 'course_edit')]
    #[IsGranted('ROLE_TUTOR')]
    public function edit(
        Course $course,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Course updated');
            return $this->redirectToRoute('course_index');
        }

        return $this->render('course/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}/delete', name: 'course_delete')]
    #[IsGranted('ROLE_TUTOR')]
    public function delete(
        Course $course,
        EntityManagerInterface $em
    ): Response {
        $em->remove($course);
        $em->flush();

        $this->addFlash('success', 'Course deleted');
        return $this->redirectToRoute('course_index');
    }
}
