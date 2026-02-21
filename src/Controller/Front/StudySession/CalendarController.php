<?php

namespace App\Controller\Front\StudySession;

use App\Repository\StudySession\PlanningRepository;
use App\Entity\StudySession\Planning;
use App\Repository\StudySession\StudySessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/study-session/calendar')]
#[IsGranted('ROLE_STUDENT')]
class CalendarController extends AbstractController
{
    public function __construct(
        private StudySessionRepository $studySessionRepository,
        private PlanningRepository $planningRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * ✅ Calendar entry point (NO redirect)
     */
    #[Route('/', name: 'calendar_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('front/study_session/calendar.html.twig');
    }

    /**
     * Calendar events (plannings + sessions)
     */
    #[Route('/events', name: 'calendar_events', methods: ['GET'])]
public function events(Request $request): JsonResponse
{
    $user = $this->getUser();

    $start = $request->query->get('start');
    $end = $request->query->get('end');

    $dateFrom = $start ? new \DateTimeImmutable($start) : null;
    $dateTo = $end ? new \DateTimeImmutable($end) : null;

    $plannings = $this->planningRepository->findAll();

foreach ($plannings as $planning) {
    if (!$planning->getScheduledDate() || !$planning->getScheduledTime()) {
        continue;
    }

    $start = \DateTimeImmutable::createFromFormat(
        'Y-m-d H:i:s',
        $planning->getScheduledDate()->format('Y-m-d') . ' ' .
        $planning->getScheduledTime()->format('H:i:s')
    );

    $end = $start->modify('+' . ($planning->getPlannedDuration() ?? 60) . ' minutes');

    $events[] = [
        'id' => 'planning_' . $planning->getId(),
        'title' => $planning->getTitle(),
        'start' => $start->format(DATE_ATOM),
        'end' => $end->format(DATE_ATOM),
        'backgroundColor' =>
            $planning->getStatus() === Planning::STATUS_COMPLETED
                ? '#28a745'
                : '#007bff',
        'extendedProps' => [
            'planningId' => $planning->getId(),
            'status' => $planning->getStatus(),
        ],
    ];
    }

    return new JsonResponse($events);
}

    /**
     * ✅ REQUIRED by Twig
     * Drag & drop update
     */
    #[Route('/update-datetime', name: 'calendar_update_datetime', methods: ['POST'])]
    public function updateDateTime(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        $eventId = $data['id'] ?? null;
        $start = $data['start'] ?? null;
        $end = $data['end'] ?? null;

        if (!$eventId || !$start) {
            return new JsonResponse(['success' => false], 400);
        }

        $startDateTime = new \DateTimeImmutable($start);

        try {
            if (str_starts_with($eventId, 'session_')) {
                $session = $this->studySessionRepository->find((int) str_replace('session_', '', $eventId));
                if (!$session || $session->getUser() !== $user) {
                    return new JsonResponse(['success' => false], 403);
                }

                $planning = $session->getPlanning();
                if (!$planning) {
                    return new JsonResponse(['success' => false], 400);
                }

                $planning->setScheduledDate($startDateTime);
                $planning->setScheduledTime($startDateTime);

                if ($end) {
                    $endDateTime = new \DateTimeImmutable($end);
                    $duration = (int)(($endDateTime->getTimestamp() - $startDateTime->getTimestamp()) / 60);
                    $planning->setPlannedDuration($duration);
                    $session->setDuration($duration);
                }
            }

            if (str_starts_with($eventId, 'planning_')) {
                $planning = $this->planningRepository->find((int) str_replace('planning_', '', $eventId));
                if (!$planning || $planning->getCourse()?->getCreatedBy() !== $user) {
                    return new JsonResponse(['success' => false], 403);
                }

                $planning->setScheduledDate($startDateTime);
                $planning->setScheduledTime($startDateTime);

                if ($end) {
                    $endDateTime = new \DateTimeImmutable($end);
                    $duration = (int)(($endDateTime->getTimestamp() - $startDateTime->getTimestamp()) / 60);
                    $planning->setPlannedDuration($duration);
                }
            }

            $this->entityManager->flush();
            return new JsonResponse(['success' => true]);

        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}