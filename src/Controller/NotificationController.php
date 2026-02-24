<?php

namespace App\Controller;

use App\Service\Library\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notifications')]
class NotificationController extends AbstractController
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    #[Route('/', name: 'notifications_index')]
    public function index(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $notifications = $this->notificationService->getAllNotifications($user, 50);

        return $this->render('notifications/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    #[Route('/{id}/mark-read', name: 'notifications_mark_read', methods: ['POST'])]
    public function markRead(int $id): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['success' => false], 401);
        }

        $notification = $this->notificationService->getNotificationById($id);
        
        if (!$notification || $notification->getUser() !== $user) {
            return $this->json(['success' => false], 404);
        }

        $this->notificationService->markAsRead($notification);

        return $this->json(['success' => true]);
    }

    #[Route('/mark-all-read', name: 'notifications_mark_all_read', methods: ['POST'])]
    public function markAllRead(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['success' => false], 401);
        }

        $this->notificationService->markAllAsRead($user);

        return $this->json(['success' => true]);
    }
}
