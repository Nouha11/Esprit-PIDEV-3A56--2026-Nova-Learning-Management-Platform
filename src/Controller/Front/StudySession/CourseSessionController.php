<?php

namespace App\Controller\Front\StudySession;

use App\Entity\StudySession\Course;
use App\Repository\StudySession\CourseRepository;
use App\Service\StudySession\EnrollmentService;
use App\Service\StudySession\EnergyMonitorService;
use App\Service\StudySession\CourseResourceService;
use Doctrine\ORM\EntityManagerInterface;
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
        private CourseRepository $courseRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Display the course session view
     */
    #[Route('/{courseId}/session', name: 'course_session_view', methods: ['GET'])]
    public function view(int $courseId): Response
    {
        $user = $this->getUser();
        
        if (!$user instanceof \App\Entity\users\User) {
            throw $this->createAccessDeniedException('You must be logged in.');
        }
        
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

        // Check current energy level
        $currentEnergy = $this->energyMonitorService->getCurrentEnergy($user);
        
        // Block access if energy is depleted
        if ($currentEnergy <= 0) {
            $this->addFlash('error', 'Your energy is depleted! Play mini games to restore it or wait 30 minutes for auto-refill.');
            return $this->redirectToRoute('course_show', ['id' => $courseId]);
        }
        
        // Show warning if energy is low
        // FIXED: PHPStan noted that $currentEnergy > 0 is always true here because of the block above.
        if ($currentEnergy <= 20) {
            $this->addFlash('warning', 'Your energy is running low! Consider playing a mini game to restore it.');
        }

        // Load course resources
        $resources = $this->courseResourceService->getCourseResources($course);

        // Initialize Pomodoro timer state
        $pomodoroState = [
            'duration' => 25 * 60, // 25 minutes in seconds
            'pomodoroCount' => 0,
            'status' => 'ready'
        ];

        // Show success message on first course start
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
            'currentEnergy' => $currentEnergy,
            'timeUntilRefill' => $this->energyMonitorService->getTimeUntilNextRefill($user)
        ]);
    }

    /**
     * Check energy level via AJAX
     */
    #[Route('/{courseId}/session/energy-check', name: 'course_session_energy_check', methods: ['GET'])]
    public function checkEnergy(int $courseId): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof \App\Entity\users\User) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }
        
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
     * Deplete energy during study session
     */
    #[Route('/{courseId}/session/deplete-energy', name: 'course_session_deplete_energy', methods: ['POST'])]
    public function depleteEnergy(int $courseId): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof \App\Entity\users\User) {
            return new JsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        // Verify course exists and user is enrolled
        $course = $this->courseRepository->find($courseId);
        if (!$course || !$this->enrollmentService->isEnrolled($user, $course)) {
            return new JsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        // Get amount from request (default to 1)
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $data = json_decode($request->getContent(), true);
        $amount = $data['amount'] ?? 1;
        
        // Deplete energy
        $this->energyMonitorService->depleteEnergy($user, $amount);
        
        $currentEnergy = $this->energyMonitorService->getCurrentEnergy($user);
        $isDepleted = $this->energyMonitorService->isEnergyDepleted($user);
        
        return new JsonResponse([
            'success' => true,
            'energy' => $currentEnergy,
            'depleted' => $isDepleted
        ]);
    }
    
    /**
     * Update course progress based on Pomodoro completion
     */
    #[Route('/{courseId}/session/update-progress', name: 'course_session_update_progress', methods: ['POST'])]
    public function updateProgress(int $courseId): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof \App\Entity\users\User) {
            return new JsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        // Verify course exists and user is enrolled
        $course = $this->courseRepository->find($courseId);
        if (!$course || !$this->enrollmentService->isEnrolled($user, $course)) {
            return new JsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        // Get data from request
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $data = json_decode($request->getContent(), true);
        $pomodoroCount = $data['pomodoroCount'] ?? 0;
        $minutesStudied = $data['minutesStudied'] ?? null;
        
        // Get current progress
        $currentProgress = $course->getProgress() ?? 0;
        
        // Calculate progress
        if ($minutesStudied !== null) {
            // Gradual progress: add 1% per minute studied
            // This accumulates progress instead of resetting
            $newProgress = min(100, $currentProgress + 1);
        } else {
            // Full Pomodoro completion: each Pomodoro = 25% progress
            $newProgress = min(100, $pomodoroCount * 25);
        }
        
        // Update course progress
        $course->setProgress($newProgress);
        
        // Update course status based on progress
        if ($newProgress >= 100) {
            $course->setStatus('COMPLETED');
        } elseif ($newProgress > 0) {
            $course->setStatus('IN_PROGRESS');
        }
        
        // Persist changes (FIXED: Using injected EntityManager instead of protected repository method)
        $this->entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'progress' => $newProgress,
            'status' => $course->getStatus()
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
        
        if (!$user instanceof \App\Entity\users\User) {
            throw $this->createAccessDeniedException('You must be logged in.');
        }
        
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

        // Persist changes (FIXED: Using injected EntityManager instead of protected repository method)
        $this->entityManager->flush();

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
        
        if (!$user instanceof \App\Entity\users\User) {
            throw $this->createAccessDeniedException('You must be logged in.');
        }
        
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