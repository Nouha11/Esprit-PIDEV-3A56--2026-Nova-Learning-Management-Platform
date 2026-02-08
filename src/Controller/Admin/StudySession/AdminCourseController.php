<?php

namespace App\Controller\Admin\StudySession;

use App\Entity\StudySession\Course;
use App\Form\Admin\CourseFormType;
use App\Repository\StudySession\CourseRepository;
use App\Service\StudySession\CourseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/courses')]
//#[IsGranted('ROLE_ADMIN')]
class AdminCourseController extends AbstractController
{
    public function __construct(
        private CourseService $courseService,
        private CourseRepository $courseRepository
    ) {
    }

    /**
     * List all courses
     */
    #[Route('', name: 'admin_course_index', methods: ['GET'])]
    public function index(): Response
    {
        $courses = $this->courseRepository->findAll();
        
        return $this->render('admin/course/index.html.twig', [
            'courses' => $courses,
        ]);
    }

    /**
     * Create new course
     */
    #[Route('/new', name: 'admin_course_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseFormType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->courseService->createCourse($course);
                $this->addFlash('success', 'Course created successfully!');
                return $this->redirectToRoute('admin_course_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to create course: ' . $e->getMessage());
            }
        }

        return $this->render('admin/course/new.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Show course details
     */
    #[Route('/{id}', name: 'admin_course_show', methods: ['GET'])]
    public function show(Course $course): Response
    {
        return $this->render('admin/course/show.html.twig', [
            'course' => $course,
        ]);
    }

    /**
     * Edit course
     */
    #[Route('/{id}/edit', name: 'admin_course_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Course $course): Response
    {
        $form = $this->createForm(CourseFormType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->courseService->updateCourse($course);
                $this->addFlash('success', 'Course updated successfully!');
                return $this->redirectToRoute('admin_course_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to update course: ' . $e->getMessage());
            }
        }

        return $this->render('admin/course/edit.html.twig', [
            'form' => $form,
            'course' => $course,
        ]);
    }

    /**
     * Delete course
     */
    #[Route('/{id}/delete', name: 'admin_course_delete', methods: ['POST'])]
    public function delete(Request $request, Course $course): Response
    {
        if ($this->isCsrfTokenValid('delete'.$course->getId(), $request->request->get('_token'))) {
            try {
                $this->courseService->deleteCourse($course);
                $this->addFlash('success', 'Course deleted successfully!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to delete course: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('admin_course_index');
    }

    /**
     * Toggle course publication status
     */
    #[Route('/{id}/toggle-publish', name: 'admin_course_toggle_publish', methods: ['POST'])]
    public function togglePublish(Request $request, Course $course): Response
    {
        if ($this->isCsrfTokenValid('toggle'.$course->getId(), $request->request->get('_token'))) {
            try {
                $this->courseService->togglePublish($course);
                $status = $course->isPublished() ? 'published' : 'unpublished';
                $this->addFlash('success', "Course {$status} successfully!");
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to toggle publication: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('admin_course_index');
    }
}
