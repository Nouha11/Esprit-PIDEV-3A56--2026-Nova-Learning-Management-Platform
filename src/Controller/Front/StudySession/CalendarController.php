<?php

namespace App\Controller\Front\StudySession;

use App\Entity\StudySession\Planning;
use App\Entity\StudySession\StudySession;
use App\Form\StudySession\StudySessionType;
use App\Repository\StudySession\StudySessionRepository;
use App\Repository\StudySession\PlanningRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/study-session/calendar')]
#[IsGranted('ROLE_STUDENT')]
class CalendarController extends AbstractController
{
    public function __construct(
        private StudySessionRepository $studySessionRepository,
        private PlanningRepository $planningRepository,
        private EntityManagerInterface $entityManager,
        private CsrfTokenManagerInterface $csrfTokenManager
    ) {}

    /**
     * Render the calendar view with all study sessions
     */
    #[Route('/', name: 'calendar_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('front/study_session/calendar.html.twig');
    }

    /**
     * Get calendar events data in JSON format for FullCalendar
     */
    #[Route('/events', name: 'calendar_events', methods: ['GET'])]
    public function events(Request $request): JsonResponse
    {
        $user = $this->getUser();
        
        // Get date range from query parameters (FullCalendar sends start and end)
        $start = $request->query->get('start');
        $end = $request->query->get('end');
        
        $dateFrom = $start ? new \DateTimeImmutable($start) : null;
        $dateTo = $end ? new \DateTimeImmutable($end) : null;
        
        // Get all study sessions for the user within the date range
        $studySessions = $this->studySessionRepository->findByFilters(
            userId: $user->getId(),
            dateFrom: $dateFrom,
            dateTo: $dateTo
        );
        
        // Get all plannings within the date range
        $plannings = $this->planningRepository->findByFilters(
            dateFrom: $dateFrom,
            dateTo: $dateTo
        );
        
        // Filter plannings to only those belonging to courses created by the user
        $userPlannings = array_filter($plannings, function($planning) use ($user) {
            return $planning->getCourse()?->getCreatedBy() === $user;
        });
        
        $events = [];
        $processedPlanningIds = [];
        
        // Add study sessions to events
        foreach ($studySessions as $session) {
            $planning = $session->getPlanning();
            $isCompleted = $session->getCompletedAt() !== null;
            
            if ($planning) {
                $processedPlanningIds[] = $planning->getId();
            }
            
            // Combine scheduled date and time for the event
            $scheduledDate = $planning?->getScheduledDate();
            $scheduledTime = $planning?->getScheduledTime();
            
            if ($scheduledDate && $scheduledTime) {
                $startDateTime = \DateTimeImmutable::createFromFormat(
                    'Y-m-d H:i:s',
                    $scheduledDate->format('Y-m-d') . ' ' . $scheduledTime->format('H:i:s')
                );
                
                $duration = $session->getDuration() ?? $planning->getPlannedDuration() ?? 60;
                $endDateTime = $startDateTime->modify("+{$duration} minutes");
                
                $events[] = [
                    'id' => 'session_' . $session->getId(),
                    'title' => $planning?->getTitle() ?? 'Study Session',
                    'start' => $startDateTime->format('Y-m-d\TH:i:s'),
                    'end' => $endDateTime->format('Y-m-d\TH:i:s'),
                    'backgroundColor' => $isCompleted ? '#28a745' : '#007bff',
                    'borderColor' => $isCompleted ? '#28a745' : '#007bff',
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'session',
                        'sessionId' => $session->getId(),
                        'planningId' => $planning?->getId(),
                        'status' => $isCompleted ? 'completed' : 'planned',
                        'xp' => $session->getXpEarned(),
                        'mood' => $session->getMood(),
                        'energyLevel' => $session->getEnergyLevel(),
                    ],
                ];
            }
        }
        
        // Add plannings without study sessions (scheduled but not yet started)
        foreach ($userPlannings as $planning) {
            if (in_array($planning->getId(), $processedPlanningIds)) {
                continue; // Skip plannings that already have study sessions
            }
            
            $scheduledDate = $planning->getScheduledDate();
            $scheduledTime = $planning->getScheduledTime();
            
            if ($scheduledDate && $scheduledTime) {
                $startDateTime = \DateTimeImmutable::createFromFormat(
                    'Y-m-d H:i:s',
                    $scheduledDate->format('Y-m-d') . ' ' . $scheduledTime->format('H:i:s')
                );
                
                $duration = $planning->getPlannedDuration() ?? 60;
                $endDateTime = $startDateTime->modify("+{$duration} minutes");
                
                $events[] = [
                    'id' => 'planning_' . $planning->getId(),
                    'title' => $planning->getTitle(),
                    'start' => $startDateTime->format('Y-m-d\TH:i:s'),
                    'end' => $endDateTime->format('Y-m-d\TH:i:s'),
                    'backgroundColor' => '#6c757d', // Gray for scheduled but not started
                    'borderColor' => '#6c757d',
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'planning',
                        'planningId' => $planning->getId(),
                        'status' => 'scheduled',
                    ],
                ];
            }
        }
        
        return new JsonResponse($events);
    }

    /**
     * Update session datetime via drag-and-drop
     */
    #[Route('/update-datetime', name: 'calendar_update_datetime', methods: ['POST'])]
    public function updateDateTime(Request $request): JsonResponse
    {
        $user = $this->getUser();
        
        // Get data from request
        $data = json_decode($request->getContent(), true);
        $eventId = $data['id'] ?? null;
        $newStart = $data['start'] ?? null;
        $newEnd = $data['end'] ?? null;
        
        if (!$eventId || !$newStart) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Missing required parameters.',
            ], 400);
        }
        
        try {
            // Parse new datetime
            $newStartDateTime = new \DateTimeImmutable($newStart);
            
            // Determine if this is a session or planning event
            if (str_starts_with($eventId, 'session_')) {
                // Handle study session update
                $sessionId = (int) str_replace('session_', '', $eventId);
                $studySession = $this->studySessionRepository->find($sessionId);
                
                if (!$studySession) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'Study session not found.',
                    ], 404);
                }
                
                // Ensure user owns this session
                if ($studySession->getUser() !== $user) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'You do not have permission to update this session.',
                    ], 403);
                }
                
                // Update the associated planning's scheduled date and time
                $planning = $studySession->getPlanning();
                if ($planning) {
                    $planning->setScheduledDate($newStartDateTime);
                    $planning->setScheduledTime($newStartDateTime);
                    
                    // Calculate new duration if end time is provided
                    if ($newEnd) {
                        $newEndDateTime = new \DateTimeImmutable($newEnd);
                        $durationMinutes = ($newEndDateTime->getTimestamp() - $newStartDateTime->getTimestamp()) / 60;
                        $planning->setPlannedDuration((int) $durationMinutes);
                        $studySession->setDuration((int) $durationMinutes);
                    }
                    
                    $this->entityManager->flush();
                    
                    return new JsonResponse([
                        'success' => true,
                        'message' => 'Session datetime updated successfully.',
                    ]);
                }
                
                return new JsonResponse([
                    'success' => false,
                    'error' => 'No planning associated with this session.',
                ], 400);
                
            } elseif (str_starts_with($eventId, 'planning_')) {
                // Handle planning update
                $planningId = (int) str_replace('planning_', '', $eventId);
                $planning = $this->planningRepository->find($planningId);
                
                if (!$planning) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'Planning not found.',
                    ], 404);
                }
                
                // Ensure user owns this planning (through course)
                if ($planning->getCourse()?->getCreatedBy() !== $user) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'You do not have permission to update this planning.',
                    ], 403);
                }
                
                // Update planning's scheduled date and time
                $planning->setScheduledDate($newStartDateTime);
                $planning->setScheduledTime($newStartDateTime);
                
                // Calculate new duration if end time is provided
                if ($newEnd) {
                    $newEndDateTime = new \DateTimeImmutable($newEnd);
                    $durationMinutes = ($newEndDateTime->getTimestamp() - $newStartDateTime->getTimestamp()) / 60;
                    $planning->setPlannedDuration((int) $durationMinutes);
                }
                
                $this->entityManager->flush();
                
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Planning datetime updated successfully.',
                ]);
                
            } else {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Invalid event ID format.',
                ], 400);
            }
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to update datetime: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new session from calendar date click
     */
    #[Route('/create-from-date', name: 'calendar_create_from_date', methods: ['POST'])]
    public function createFromDate(Request $request): JsonResponse
    {
        $user = $this->getUser();
        
        // Get data from request
        $data = json_decode($request->getContent(), true);
        $dateStr = $data['date'] ?? null;
        
        if (!$dateStr) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Date is required.',
            ], 400);
        }
        
        try {
            $clickedDate = new \DateTimeImmutable($dateStr);
            
            // Create a new planning
            $planning = new Planning();
            $planning->setTitle('New Study Session');
            $planning->setScheduledDate($clickedDate);
            $planning->setScheduledTime($clickedDate);
            $planning->setPlannedDuration(60); // Default 60 minutes
            $planning->setStatus(Planning::STATUS_SCHEDULED);
            $planning->setReminder(false);
            
            // Note: You'll need to set the course - this is a placeholder
            // In a real implementation, you might want to show a form to select the course
            // For now, we'll return a URL to the create form with the date pre-filled
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Redirecting to create form...',
                'redirectUrl' => $this->generateUrl('study_session_new', [
                    'date' => $clickedDate->format('Y-m-d'),
                    'time' => $clickedDate->format('H:i'),
                ]),
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to create session: ' . $e->getMessage(),
            ], 500);
        }
    }
}
