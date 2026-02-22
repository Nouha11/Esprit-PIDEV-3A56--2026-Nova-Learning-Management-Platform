<?php

namespace App\Controller;

use App\Service\SessionManagementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/sessions')]
#[IsGranted('ROLE_USER')]
class SessionManagementController extends AbstractController
{
    public function __construct(
        private SessionManagementService $sessionManagementService
    ) {}

    #[Route('', name: 'app_sessions', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();
        $activeSessions = $this->sessionManagementService->getActiveSessions($user);

        return $this->render('security/sessions.html.twig', [
            'activeSessions' => $activeSessions,
        ]);
    }

    #[Route('/terminate/{id}', name: 'app_session_terminate', methods: ['POST'])]
    public function terminate(int $id): JsonResponse
    {
        $user = $this->getUser();
        $success = $this->sessionManagementService->terminateSession($id, $user);

        if ($success) {
            return new JsonResponse([
                'success' => true,
                'message' => 'Session terminated successfully'
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Failed to terminate session'
        ], 400);
    }

    #[Route('/terminate-all', name: 'app_session_terminate_all', methods: ['POST'])]
    public function terminateAll(): JsonResponse
    {
        $user = $this->getUser();
        $count = $this->sessionManagementService->terminateAllOtherSessions($user);

        return new JsonResponse([
            'success' => true,
            'message' => "Terminated {$count} session(s) successfully",
            'count' => $count
        ]);
    }
}
