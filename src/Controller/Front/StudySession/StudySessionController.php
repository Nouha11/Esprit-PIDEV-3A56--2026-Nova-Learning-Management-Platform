<?php

namespace App\Controller\Front\StudySession;

use App\Entity\StudySession\Planning;
use App\Entity\StudySession\StudySession;
use App\Form\StudySession\StudySessionType;
use App\Repository\StudySession\StudySessionRepository;
use App\Service\StudySession\StudySessionService;
use App\Service\StudySession\PlanningService;
use App\Service\StudySession\StreakService;
use Doctrine\ORM\EntityManagerInterface;
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
        private CsrfTokenManagerInterface $csrfTokenManager,
        private StudySessionRepository $studySessionRepository,
        private EntityManagerInterface $entityManager,
        private StreakService $streakService
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
            $studySession->setStartedAt(new \DateTimeImmutable());
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

    #[Route('/', name: 'study_session_index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();
        
        // Get all study sessions for the current user
        $studySessions = $this->studySessionRepository->findByFilters(
            userId: $user->getId()
        );

        return $this->render('front/study_session/index.html.twig', [
            'study_sessions' => $studySessions,
        ]);
    }
    #[Route('/{id}', name: 'study_session_show', methods: ['GET'])]
    public function show(StudySession $studySession): Response
    {
        // Ensure user can only view their own sessions
        if ($studySession->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot view this study session.');
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
            try {
                $this->entityManager->persist($studySession);
                $this->entityManager->flush();

                $this->addFlash('success', 'Study session created successfully.');
                return $this->redirectToRoute('study_session_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to create study session: ' . $e->getMessage());
            }
        }

        return $this->render('front/study_session/form.html.twig', [
            'form' => $form->createView(),
            'study_session' => $studySession,
        ]);
    }

    #[Route('/{id}/edit', name: 'study_session_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StudySession $studySession): Response
    {
        // Ensure user can only edit their own sessions
        if ($studySession->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot edit this study session.');
        }

        $form = $this->createForm(StudySessionType::class, $studySession);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->flush();

                $this->addFlash('success', 'Study session updated successfully.');
                return $this->redirectToRoute('study_session_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to update study session: ' . $e->getMessage());
            }
        }

        return $this->render('front/study_session/form.html.twig', [
            'form' => $form->createView(),
            'study_session' => $studySession,
        ]);
    }

    #[Route('/{id}/delete', name: 'study_session_delete', methods: ['POST'])]
    public function delete(Request $request, StudySession $studySession): Response
    {
        // Ensure user can only delete their own sessions
        if ($studySession->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot delete this study session.');
        }

        // CSRF validation
        $token = $request->request->get('_token');
        if (!$this->csrfTokenManager->isTokenValid(
            new \Symfony\Component\Security\Csrf\CsrfToken('delete_study_session_' . $studySession->getId(), $token)
        )) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('study_session_index');
        }

        try {
            $this->entityManager->remove($studySession);
            $this->entityManager->flush();

            $this->addFlash('success', 'Study session deleted successfully.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to delete study session: ' . $e->getMessage());
        }

        return $this->redirectToRoute('study_session_index');
    }

    #[Route('/{id}/mark-complete', name: 'study_session_mark_complete', methods: ['POST'])]
    public function markComplete(Request $request, StudySession $studySession): Response
    {
        // Ensure user can only mark their own sessions as complete
        if ($studySession->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot mark this study session as complete.');
        }

        // CSRF validation
        $token = $request->request->get('_token');
        if (!$this->csrfTokenManager->isTokenValid(
            new \Symfony\Component\Security\Csrf\CsrfToken('mark_complete_' . $studySession->getId(), $token)
        )) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('study_session_index');
        }

        // Check if already completed
        if ($studySession->getCompletedAt() !== null) {
            $this->addFlash('warning', 'This session has already been marked as completed.');
            return $this->redirectToRoute('study_session_index');
        }

        try {
            // Set completion timestamp
            $completedAt = new \DateTimeImmutable();
            $studySession->setCompletedAt($completedAt);
            
            // Update associated planning status if exists
            if ($studySession->getPlanning()) {
                $studySession->getPlanning()->setStatus(Planning::STATUS_COMPLETED);
            }

            $this->entityManager->flush();

            // Update streak
            $this->streakService->updateStreak($this->getUser(), $completedAt);

            $this->addFlash('success', 'Study session marked as completed. Streak updated!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to mark session as complete: ' . $e->getMessage());
        }

        return $this->redirectToRoute('study_session_index');
    }

    #[Route('/{id}/mark-incomplete', name: 'study_session_mark_incomplete', methods: ['POST'])]
    public function markIncomplete(Request $request, StudySession $studySession): Response
    {
        // Ensure user can only mark their own sessions as incomplete
        if ($studySession->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot mark this study session as incomplete.');
        }

        // CSRF validation
        $token = $request->request->get('_token');
        if (!$this->csrfTokenManager->isTokenValid(
            new \Symfony\Component\Security\Csrf\CsrfToken('mark_incomplete_' . $studySession->getId(), $token)
        )) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('study_session_index');
        }

        // Check if already incomplete
        if ($studySession->getCompletedAt() === null) {
            $this->addFlash('warning', 'This session is already marked as incomplete.');
            return $this->redirectToRoute('study_session_index');
        }

        try {
            // Remove completion timestamp
            $studySession->setCompletedAt(null);
            
            // Update associated planning status if exists
            if ($studySession->getPlanning()) {
                $studySession->getPlanning()->setStatus(Planning::STATUS_SCHEDULED);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Study session marked as incomplete.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to mark session as incomplete: ' . $e->getMessage());
        }

        return $this->redirectToRoute('study_session_index');
    }
}
