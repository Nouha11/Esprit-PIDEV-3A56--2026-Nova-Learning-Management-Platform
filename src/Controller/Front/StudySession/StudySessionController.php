<?php

namespace App\Controller\Front\StudySession;

use App\Entity\StudySession\Planning;
use App\Entity\StudySession\StudySession;
use App\Form\StudySession\StudySessionType;
use App\Repository\StudySession\StudySessionRepository;
use App\Service\StudySession\PlanningService;
use App\Service\StudySession\StudySessionService;
use App\Service\StudySession\StreakService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/study-session')]
#[IsGranted('ROLE_STUDENT')]
class StudySessionController extends AbstractController
{
    public function __construct(
        private StudySessionService $studySessionService,
        private PlanningService $planningService,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private StudySessionRepository $studySessionRepository,
        private EntityManagerInterface $entityManager,
        private StreakService $streakService
    ) {}

    /**
     * ✅ INITIAL ROUTE
     * Redirects to planned study sessions (/planning)
     */
    #[Route('/', name: 'study_session_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->redirectToRoute('planning_index');
    }

    #[Route('/complete/{planning}', name: 'study_complete', methods: ['POST'])]
    public function complete(Planning $planning, Request $request): Response
    {
        $token = $request->request->get('_token');

        if (
            !$this->csrfTokenManager->isTokenValid(
                new CsrfToken('complete_session_' . $planning->getId(), $token)
            )
        ) {
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
            $energyUsed = intdiv($duration, 10);
            $xpEarned = $duration * 2;

            $burnoutRisk = match (true) {
                $energyUsed > 80 => 'HIGH',
                $energyUsed > 40 => 'MODERATE',
                default => 'LOW'
            };

            $studySession = new StudySession();
            $studySession->setUser($this->getUser());
            $studySession->setPlanning($planning);
            $studySession->setDuration($duration); // Set duration field (required)
            $studySession->setActualDuration($duration);
            $studySession->setXpEarned($xpEarned);
            $studySession->setBurnoutRisk($burnoutRisk);
            $studySession->setStartedAt(new \DateTimeImmutable());
            $studySession->setCompletedAt(new \DateTimeImmutable());

            $this->studySessionService->create($studySession);

            $planning->setStatus(Planning::STATUS_COMPLETED);
            $this->planningService->update($planning);

            $this->streakService->updateStreak(
                $this->getUser(),
                $studySession->getCompletedAt()
            );

            $this->addFlash(
                'success',
                "Session completed! XP earned: $xpEarned | Burnout risk: $burnoutRisk"
            );
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to complete session: ' . $e->getMessage());
        }

        return $this->redirectToRoute('planning_index');
    }

    #[Route('/{id}', name: 'study_session_show', methods: ['GET'])]
    public function show(StudySession $studySession): Response
    {
        if ($studySession->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('front/study_session/show.html.twig', [
            'study_session' => $studySession,
        ]);
    }

    #[Route('/new', name: 'study_session_new', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $studySession = new StudySession();
        $studySession->setUser($this->getUser());

        $form = $this->createForm(StudySessionType::class, $studySession);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($studySession);
            $this->entityManager->flush();

            $this->addFlash('success', 'Study session created successfully.');
            return $this->redirectToRoute('planning_index');
        }

        return $this->render('front/study_session/form.html.twig', [
            'form' => $form->createView(),
            'study_session' => $studySession,
        ]);
    }

    #[Route('/{id}/edit', name: 'study_session_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StudySession $studySession): Response
    {
        if ($studySession->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(StudySessionType::class, $studySession);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Study session updated successfully.');
            return $this->redirectToRoute('planning_index');
        }

        return $this->render('front/study_session/form.html.twig', [
            'form' => $form->createView(),
            'study_session' => $studySession,
        ]);
    }

    #[Route('/{id}/delete', name: 'study_session_delete', methods: ['POST'])]
    public function delete(Request $request, StudySession $studySession): Response
    {
        if ($studySession->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $token = $request->request->get('_token');
        if (
            !$this->csrfTokenManager->isTokenValid(
                new CsrfToken('delete_study_session_' . $studySession->getId(), $token)
            )
        ) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('planning_index');
        }

        $this->entityManager->remove($studySession);
        $this->entityManager->flush();

        $this->addFlash('success', 'Study session deleted successfully.');
        return $this->redirectToRoute('planning_index');
    }
}