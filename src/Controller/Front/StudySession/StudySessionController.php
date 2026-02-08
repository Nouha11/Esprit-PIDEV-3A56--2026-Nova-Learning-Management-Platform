<?php

namespace App\Controller\Front\StudySession;

use App\Entity\StudySession\Planning;
use App\Entity\StudySession\StudySession;
use App\Service\StudySession\StudySessionService;
use App\Service\StudySession\PlanningService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/study-session')]
#[IsGranted('ROLE_STUDENT')]
class StudySessionController extends AbstractController
{
    public function __construct(
        private StudySessionService $studySessionService,
        private PlanningService $planningService,
        private CsrfTokenManagerInterface $csrfTokenManager
    ) {}

    #[Route('/complete/{planning}', name: 'study_complete', methods: ['POST'])]
    public function complete(Planning $planning, Request $request): Response 
    {
        // CSRF validation
        $token = $request->request->get('_token');
        if (!$this->csrfTokenManager->isTokenValid(new \Symfony\Component\Security\Csrf\CsrfToken('complete_session_' . $planning->getId(), $token))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('planning_index');
        }

        if ($planning->getStatus() === Planning::STATUS_COMPLETED) {
            $this->addFlash('warning', 'This session has already been completed.');
            return $this->redirectToRoute('planning_index');
        }

        $duration = $planning->getPlannedDuration() ?? 0;
        if ($duration <= 0) {
            $this->addFlash('error', 'Invalid planned duration.');
            return $this->redirectToRoute('planning_index');
        }

        try {
            // Calculate metrics
            $energyUsed = intdiv($duration, 10);
            $xpEarned = $duration * 2;

            $burnoutRisk = match (true) {
                $energyUsed > 80 => 'HIGH',
                $energyUsed > 40 => 'MODERATE',
                default => 'LOW'
            };

            // Create study session record
            $studySession = new StudySession();
            $studySession->setPlanning($planning);
            $studySession->setActualDuration($duration);
            $studySession->setXpEarned($xpEarned);
            $studySession->setBurnoutRisk($burnoutRisk);
            $studySession->setCompletedAt(new \DateTimeImmutable());

            $this->studySessionService->create($studySession);

            // Update planning status
            $planning->setStatus(Planning::STATUS_COMPLETED);
            $this->planningService->update($planning);

            $this->addFlash(
                'success',
                "Session completed! XP earned: $xpEarned | Burnout risk: $burnoutRisk"
            );
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to complete session: ' . $e->getMessage());
        }

        return $this->redirectToRoute('planning_index');
    }
}
