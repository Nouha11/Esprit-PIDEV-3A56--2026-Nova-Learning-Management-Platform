<?php

namespace App\Controller\Admin\StudySession;

use App\Entity\StudySession\StudySession;
use App\Repository\StudySession\StudySessionRepository;
use App\Service\StudySession\StudySessionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/study-sessions')]
//#[IsGranted('ROLE_ADMIN')]
class AdminStudySessionController extends AbstractController
{
    public function __construct(
        private StudySessionService $studySessionService,
        private StudySessionRepository $studySessionRepository
    ) {
    }

    /**
     * List all study sessions with optional filters
     */
    #[Route('', name: 'admin_study_session_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $userId = $request->query->get('userId') ? (int)$request->query->get('userId') : null;
        $burnoutRisk = $request->query->get('burnoutRisk');
        $dateFrom = $request->query->get('dateFrom') 
            ? new \DateTimeImmutable($request->query->get('dateFrom')) 
            : null;
        $dateTo = $request->query->get('dateTo') 
            ? new \DateTimeImmutable($request->query->get('dateTo')) 
            : null;

        $studySessions = $this->studySessionService->findByFilters($userId, $burnoutRisk, $dateFrom, $dateTo);

        return $this->render('admin/study_session/index.html.twig', [
            'study_sessions' => $studySessions,
            'current_user_id' => $userId,
            'current_burnout_risk' => $burnoutRisk,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
    }

    /**
     * Show study session details
     */
    #[Route('/{id}', name: 'admin_study_session_show', methods: ['GET'])]
    public function show(StudySession $studySession): Response
    {
        return $this->render('admin/study_session/show.html.twig', [
            'study_session' => $studySession,
        ]);
    }

    /**
     * Display analytics dashboard
     */
    #[Route('/analytics/dashboard', name: 'admin_study_session_analytics', methods: ['GET'])]
    public function analytics(Request $request): Response
    {
        $dateFrom = $request->query->get('dateFrom') 
            ? new \DateTimeImmutable($request->query->get('dateFrom')) 
            : null;
        $dateTo = $request->query->get('dateTo') 
            ? new \DateTimeImmutable($request->query->get('dateTo')) 
            : null;
        $groupBy = $request->query->get('groupBy');

        $analytics = $this->studySessionService->getAnalytics($dateFrom, $dateTo, $groupBy);

        return $this->render('admin/study_session/analytics.html.twig', [
            'analytics' => $analytics,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'group_by' => $groupBy,
        ]);
    }
}
