<?php

namespace App\Controller\Front\StudySession;

use App\Entity\StudySession\StudySession;
use App\Service\StudySession\PomodoroService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/study-session/{id}/pomodoro')]
#[IsGranted('ROLE_STUDENT')]
class PomodoroController extends AbstractController
{
    public function __construct(
        private PomodoroService $pomodoroService
    ) {}

    /**
     * Display the Pomodoro timer page for a study session
     */
    #[Route('', name: 'pomodoro_timer', methods: ['GET'])]
    public function timer(StudySession $studySession): Response
    {
        // Ensure user can only access their own sessions
        if ($studySession->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot access this study session.');
        }

        return $this->render('front/study_session/pomodoro.html.twig', [
            'study_session' => $studySession,
        ]);
    }

    /**
     * Handle Pomodoro completion and update count
     */
    #[Route('/complete', name: 'pomodoro_complete', methods: ['POST'])]
    public function complete(Request $request, StudySession $studySession): JsonResponse
    {
        // Ensure user can only update their own sessions
        if ($studySession->getUser() !== $this->getUser()) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Access denied'
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            // Complete the pomodoro (increments count)
            $this->pomodoroService->completePomodoro($studySession);

            return new JsonResponse([
                'success' => true,
                'pomodoroCount' => $studySession->getPomodoroCount(),
                'message' => 'Pomodoro completed successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to update pomodoro count: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
