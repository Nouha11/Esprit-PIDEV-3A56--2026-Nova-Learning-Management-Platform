<?php

namespace App\Controller\Front\StudySession;

use App\Entity\StudySession\Course;
use App\Repository\StudySession\CourseRepository;
use App\Service\StudySession\EnrollmentService;
use App\Service\StudySession\EnergyMonitorService;
use App\Service\StudySession\CourseResourceService;
use App\Service\StudySession\PomodoroService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/course')]
#[IsGranted('ROLE_STUDENT')]
class CourseSessionController extends AbstractController
{
    public function __construct(
        private EnrollmentService $enrollmentService,
        private EnergyMonitorService $energyMonitorService,
        private CourseResourceService $courseResourceService,
        private PomodoroService $pomodoroService,
        private CourseRepository $courseRepository
    ) {}

    /**
     * Display the course session view
     */
    #[Route('/{courseId}/session', name: 'course_session_view', methods: ['GET'])]
    public function view(int $courseId): Response
    {
        $user = $this->getUser();
        
        // Load course entity
        $course = $this->courseRepository->find($courseId);
        if (!$course) {
            throw $this->createNotFoundException('Course not found');
        }

        // Verify user is enrolled in course
        if (!$this->enrollmentService->isEnrolled($user, $course)) {
            $this->addFlash('error', 'You are not enrolled in this course.');
            return $this->redirectToRoute('course_index');
        }

        // Load student profile
        $studentProfile = $user->getStudentProfile();
        if (!$studentProfile) {
            throw new \RuntimeException('Student profile not found');
        }

        // Check current energy level and show warning if low
        $currentEnergy = $this->energyMonitorService->getCurrentEnergy($user);
        if ($currentEnergy > 0 && $currentEnergy <= 20) {
            $this->addFlash('warning', 'Your energy is running low! Consider playing a mini game to restore it.');
        }

        // Load course resources
        $resources = $this->courseResourceService->getCourseResources($course);

        // Check if there are new resources (this is a placeholder - you'd need to track "seen" resources)
        if (count($resources) > 0) {
            // You could add logic here to check if resources are new
            // For now, we'll skip this flash message to avoid spam
        }

        // Initialize Pomodoro timer state
        $pomodoroState = [
            'duration' => 25 * 60, // 25 minutes in seconds
            'pomodoroCount' => 0,
            'status' => 'ready'
        ];

        // Show success message on first course start (check if this is their first time)
        // This could be tracked with a session variable or database flag
        $session = $this->container->get('request_stack')->getCurrentRequest()->getSession();
        $firstStartKey = 'course_' . $courseId . '_first_start';
        if (!$session->has($firstStartKey)) {
            $this->addFlash('success', 'Course started! Good luck with your session.');
            $session->set($firstStartKey, true);
        }

        return $this->render('front/course/view.html.twig', [
            'course' => $course,
            'studentProfile' => $studentProfile,
            'resources' => $resources,
            'pomodoroState' => $pomodoroState,
            'currentEnergy' => $currentEnergy
        ]);
    }

    /**
     * Check energy level via AJAX
     */
    #[Route('/{courseId}/session/energy-check', name: 'course_session_energy_check', methods: ['GET'])]
    public function checkEnergy(int $courseId): JsonResponse
    {
        $user = $this->getUser();
        
        // Get student profile
        $studentProfile = $user->getStudentProfile();
        if (!$studentProfile) {
            throw new \RuntimeException('Student profile not found');
        }

        $currentEnergy = $this->energyMonitorService->getCurrentEnergy($user);
        $isDepleted = $this->energyMonitorService->isEnergyDepleted($user);

        return new JsonResponse([
            'energy' => $currentEnergy,
            'depleted' => $isDepleted
        ]);
    }

    /**
     * Complete a course and award rewards
     * This is called when a student finishes all course content
     */
    #[Route('/{courseId}/complete', name: 'course_complete', methods: ['POST'])]
    public function completeCourse(int $courseId): Response
    {
        $user = $this->getUser();
        
        // Load course entity
        $course = $this->courseRepository->find($courseId);
        if (!$course) {
            throw $this->createNotFoundException('Course not found');
        }

        // Verify user is enrolled
        if (!$this->enrollmentService->isEnrolled($user, $course)) {
            $this->addFlash('error', 'You are not enrolled in this course.');
            return $this->redirectToRoute('course_index');
        }

        $studentProfile = $user->getStudentProfile();
        if (!$studentProfile) {
            throw new \RuntimeException('Student profile not found');
        }

        // Award XP and tokens for course completion
        // These values could be configured per course or use defaults
        $xpReward = 100; // Default XP for completing a course
        $tokenReward = 50; // Default tokens for completing a course

        $xpBefore = $studentProfile->getTotalXP();
        $tokensBefore = $studentProfile->getTotalTokens();

        // Award rewards
        $studentProfile->addXP($xpReward);
        $studentProfile->addTokens($tokenReward);

        // Mark course as completed
        $course->setStatus('COMPLETED');
        $course->setProgress(100);

        // Persist changes
        $this->courseRepository->getEntityManager()->flush();

        // Build success message
        $message = sprintf(
            'Your session has been saved! You earned %d XP and %d tokens!',
            $xpReward,
            $tokenReward
        );
        $this->addFlash('success', $message);

        // Check for badges (placeholder - you'd implement badge logic here)
        // Example: if ($studentProfile->getTotalXP() >= 1000) { ... }

        return $this->redirectToRoute('course_index');
    }

    /**
     * Download a course resource (PDF)
     */
    #[Route('/{courseId}/resource/{resourceId}/download', name: 'course_resource_download', methods: ['GET'])]
    public function downloadResource(int $courseId, int $resourceId): Response
    {
        $user = $this->getUser();
        
        // Load course entity
        $course = $this->courseRepository->find($courseId);
        if (!$course) {
            throw $this->createNotFoundException('Course not found');
        }

        // Verify user is enrolled
        if (!$this->enrollmentService->isEnrolled($user, $course)) {
            $this->addFlash('error', 'You must be enrolled in this course to download resources.');
            return $this->redirectToRoute('course_index');
        }

        // Find the resource
        $resource = null;
        foreach ($course->getResources() as $res) {
            if ($res->getId() === $resourceId) {
                $resource = $res;
                break;
            }
        }

        if (!$resource) {
            throw $this->createNotFoundException('Resource not found for this course');
        }

        // Get file path
        $filePath = $this->courseResourceService->getResourcePath($resource);
        
        if (!file_exists($filePath)) {
            $this->addFlash('error', 'Resource file not found.');
            return $this->redirectToRoute('course_session_view', ['courseId' => $courseId]);
        }

        // Serve the PDF with correct headers
        $response = new \Symfony\Component\HttpFoundation\BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->setContentDisposition(
            \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $resource->getFilename()
        );

        return $response;
    }
}
