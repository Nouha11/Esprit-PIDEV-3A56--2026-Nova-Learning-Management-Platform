<?php

namespace App\Controller\Front\StudySession;

use App\Entity\StudySession\Course;
use App\Form\StudySession\CourseType;
use App\Repository\StudySession\CourseRepository;
use App\Service\StudySession\CourseService;
use App\Service\StudySession\EnrollmentService;
use App\Service\StudySession\PlanningService;
use App\Service\StudySession\StudySessionService;
use App\Service\StudySession\EnergyMonitorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/courses')]
class CourseController extends AbstractController
{
    public function __construct(
        private CourseService $courseService,
        private PlanningService $planningService,
        private StudySessionService $studySessionService,
        private EnrollmentService $enrollmentService,
        private EnergyMonitorService $energyMonitorService
    ) {
    }

    #[Route('/', name: 'course_index')]
    public function index(
        CourseRepository $repository,
        Request $request
    ): Response {
        $difficulty = $request->query->get('difficulty');
        $category = $request->query->get('category');
        $isPublished = $request->query->get('published') !== null 
            ? (bool)$request->query->get('published') 
            : true; // Default to showing only published courses

        // Use service to get filtered courses
        $courses = $this->courseService->findByFilters($difficulty, $category, $isPublished);

        // Get unique categories for filter dropdown
        $allCourses = $repository->findAll();
        $categories = array_unique(array_map(fn($c) => $c->getCategory(), $allCourses));
        sort($categories);

        // Get enrollment status for each course if user is a student
        $enrolledCourses = [];
        $pendingRequests = [];
        $currentEnergy = 100;
        
        if ($this->isGranted('ROLE_STUDENT')) {
            $user = $this->getUser();
            $currentEnergy = $this->energyMonitorService->getCurrentEnergy($user);
            
            foreach ($courses as $course) {
                $enrolledCourses[$course->getId()] = $this->enrollmentService->isEnrolled($user, $course);
                $pendingRequest = $this->enrollmentService->getPendingRequest($user, $course);
                $pendingRequests[$course->getId()] = $pendingRequest !== null;
            }
        }

        return $this->render('front/course/index.html.twig', [
            'courses' => $courses,
            'categories' => $categories,
            'current_difficulty' => $difficulty,
            'current_category' => $category,
            'current_published' => $isPublished,
            'enrolled_courses' => $enrolledCourses,
            'pending_requests' => $pendingRequests,
            'currentEnergy' => $currentEnergy
        ]);
    }

    #[Route('/new', name: 'course_new')]
    #[IsGranted('ROLE_TUTOR')]
    public function new(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $course = new Course();
        $course->setCreatedBy($this->getUser()); // Set the creator

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->courseService->createCourse($course);
                
                // Handle PDF uploads
                $pdfFiles = $form->get('pdfResources')->getData();
                if ($pdfFiles) {
                    $uploadedCount = 0;
                    foreach ($pdfFiles as $pdfFile) {
                        if ($pdfFile) {
                            // Validate file
                            if ($pdfFile->getSize() > 10 * 1024 * 1024) {
                                $this->addFlash('warning', 'File too large: ' . $pdfFile->getClientOriginalName() . ' (max 10MB)');
                                continue;
                            }
                            
                            if ($pdfFile->getMimeType() !== 'application/pdf') {
                                $this->addFlash('warning', 'Invalid file type: ' . $pdfFile->getClientOriginalName() . ' (PDF only)');
                                continue;
                            }
                            
                            try {
                                // Get file info BEFORE moving (temp file will be deleted after move)
                                $originalFilename = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
                                $fileSize = $pdfFile->getSize();
                                $mimeType = $pdfFile->getMimeType();
                                $clientOriginalName = $pdfFile->getClientOriginalName();
                                
                                // Generate unique filename
                                $safeFilename = preg_replace('/[^A-Za-z0-9_\-]/', '', $originalFilename);
                                if (empty($safeFilename)) {
                                    $safeFilename = 'document';
                                }
                                $newFilename = $safeFilename.'-'.uniqid().'.'.$pdfFile->guessExtension();

                                // Move file to uploads directory
                                $uploadsDir = $this->getParameter('kernel.project_dir').'/public/uploads/resources';
                                if (!is_dir($uploadsDir)) {
                                    mkdir($uploadsDir, 0777, true);
                                }
                                $pdfFile->move($uploadsDir, $newFilename);

                                // Create Resource entity using saved values
                                $resource = new \App\Entity\StudySession\Resource();
                                $resource->setFilename($clientOriginalName);
                                $resource->setStoredFilename($newFilename);
                                $resource->setFileSize($fileSize);
                                $resource->setMimeType($mimeType);
                                $resource->setCourse($course);
                                $resource->setStudySession(null); // Course resources don't need study session

                                $em->persist($resource);
                                $uploadedCount++;
                            } catch (\Exception $e) {
                                $this->addFlash('warning', 'Failed to upload file: ' . $pdfFile->getClientOriginalName() . ' - Error: ' . $e->getMessage());
                            }
                        }
                    }
                    
                    if ($uploadedCount > 0) {
                        $em->flush();
                        $this->addFlash('success', sprintf('Course created successfully with %d PDF resource(s)', $uploadedCount));
                    } else {
                        $this->addFlash('success', 'Course created successfully');
                    }
                } else {
                    $this->addFlash('success', 'Course created successfully');
                }
                
                return $this->redirectToRoute('course_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to create course: ' . $e->getMessage());
            }
        }

        return $this->render('front/course/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'course_show')]
    public function show(
        Course $course,
        Request $request
    ): Response {
        // Get planning sessions for this course with optional filters
        $status = $request->query->get('status');
        $dateFrom = $request->query->get('dateFrom') 
            ? new \DateTimeImmutable($request->query->get('dateFrom')) 
            : null;
        $dateTo = $request->query->get('dateTo') 
            ? new \DateTimeImmutable($request->query->get('dateTo')) 
            : null;

        // Get all plannings for this course
        $allPlannings = $course->getPlannings();
        
        // Filter plannings if filters are applied
        $plannings = $allPlannings;
        if ($status || $dateFrom || $dateTo) {
            $plannings = array_filter($allPlannings->toArray(), function($planning) use ($status, $dateFrom, $dateTo) {
                if ($status && $planning->getStatus() !== $status) {
                    return false;
                }
                if ($dateFrom && $planning->getScheduledDate() < $dateFrom) {
                    return false;
                }
                if ($dateTo && $planning->getScheduledDate() > $dateTo) {
                    return false;
                }
                return true;
            });
        }

        // Get study session statistics for this course
        $studySessions = [];
        foreach ($course->getPlannings() as $planning) {
            foreach ($planning->getStudySessions() as $session) {
                $studySessions[] = $session;
            }
        }

        // Calculate course statistics
        $totalSessions = count($studySessions);
        $totalXP = array_sum(array_map(fn($s) => $s->getXpEarned() ?? 0, $studySessions));
        $avgDuration = $totalSessions > 0 
            ? round(array_sum(array_map(fn($s) => $s->getDuration(), $studySessions)) / $totalSessions, 2)
            : 0;
        
        // Get current energy for students
        $currentEnergy = 100;
        if ($this->isGranted('ROLE_STUDENT')) {
            $user = $this->getUser();
            $currentEnergy = $this->energyMonitorService->getCurrentEnergy($user);
        }

        return $this->render('front/course/detail.html.twig', [
            'course' => $course,
            'plannings' => $plannings,
            'current_status' => $status,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'total_sessions' => $totalSessions,
            'total_xp' => $totalXP,
            'avg_duration' => $avgDuration,
            'currentEnergy' => $currentEnergy
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
            try {
                $this->courseService->updateCourse($course);
                
                // Handle PDF uploads
                $pdfFiles = $form->get('pdfResources')->getData();
                if ($pdfFiles) {
                    $uploadedCount = 0;
                    foreach ($pdfFiles as $pdfFile) {
                        if ($pdfFile) {
                            // Validate file
                            if ($pdfFile->getSize() > 10 * 1024 * 1024) {
                                $this->addFlash('warning', 'File too large: ' . $pdfFile->getClientOriginalName() . ' (max 10MB)');
                                continue;
                            }
                            
                            if ($pdfFile->getMimeType() !== 'application/pdf') {
                                $this->addFlash('warning', 'Invalid file type: ' . $pdfFile->getClientOriginalName() . ' (PDF only)');
                                continue;
                            }
                            
                            try {
                                // Get file info BEFORE moving (temp file will be deleted after move)
                                $originalFilename = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
                                $fileSize = $pdfFile->getSize();
                                $mimeType = $pdfFile->getMimeType();
                                $clientOriginalName = $pdfFile->getClientOriginalName();
                                
                                // Generate unique filename
                                $safeFilename = preg_replace('/[^A-Za-z0-9_\-]/', '', $originalFilename);
                                if (empty($safeFilename)) {
                                    $safeFilename = 'document';
                                }
                                $newFilename = $safeFilename.'-'.uniqid().'.'.$pdfFile->guessExtension();

                                // Move file to uploads directory
                                $uploadsDir = $this->getParameter('kernel.project_dir').'/public/uploads/resources';
                                if (!is_dir($uploadsDir)) {
                                    mkdir($uploadsDir, 0777, true);
                                }
                                $pdfFile->move($uploadsDir, $newFilename);

                                // Create Resource entity using saved values
                                $resource = new \App\Entity\StudySession\Resource();
                                $resource->setFilename($clientOriginalName);
                                $resource->setStoredFilename($newFilename);
                                $resource->setFileSize($fileSize);
                                $resource->setMimeType($mimeType);
                                $resource->setCourse($course);
                                $resource->setStudySession(null);

                                $em->persist($resource);
                                $uploadedCount++;
                            } catch (\Exception $e) {
                                $this->addFlash('warning', 'Failed to upload file: ' . $pdfFile->getClientOriginalName() . ' - Error: ' . $e->getMessage());
                            }
                        }
                    }
                    
                    if ($uploadedCount > 0) {
                        $em->flush();
                        $this->addFlash('success', sprintf('Course updated successfully with %d new PDF resource(s)', $uploadedCount));
                    } else {
                        $this->addFlash('success', 'Course updated successfully');
                    }
                } else {
                    $this->addFlash('success', 'Course updated successfully');
                }
                
                return $this->redirectToRoute('course_show', ['id' => $course->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to update course: ' . $e->getMessage());
            }
        }

        return $this->render('front/course/edit.html.twig', [
            'form' => $form->createView(),
            'course' => $course
        ]);
    }

    #[Route('/{id}/delete', name: 'course_delete')]
    #[IsGranted('ROLE_TUTOR')]
    public function delete(Course $course): Response
    {
        try {
            $this->courseService->deleteCourse($course);
            $this->addFlash('success', 'Course deleted successfully');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to delete course: ' . $e->getMessage());
            return $this->redirectToRoute('course_show', ['id' => $course->getId()]);
        }

        return $this->redirectToRoute('course_index');
    }
}
