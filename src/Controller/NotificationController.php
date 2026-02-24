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

<?php

namespace App\Controller;

use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/notifications')]
#[IsGranted('ROLE_USER')]
class NotificationController extends AbstractController
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    #[Route('', name: 'app_notifications', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();
        $notifications = $this->notificationService->getAllNotifications($user, 50);

        return $this->render('notifications/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    #[Route('/unread', name: 'app_notifications_unread', methods: ['GET'])]
    public function unread(): JsonResponse
    {
        $user = $this->getUser();
        $notifications = $this->notificationService->getUnreadNotifications($user, 10);
        $count = $this->notificationService->countUnread($user);

        return new JsonResponse([
            'count' => $count,
            'notifications' => array_map(function($notification) {
                return [
                    'id' => $notification->getId(),
                    'type' => $notification->getType(),
                    'title' => $notification->getTitle(),
                    'message' => $notification->getMessage(),
                    'icon' => $notification->getIcon(),
                    'color' => $notification->getColor(),
                    'actionUrl' => $notification->getActionUrl(),
                    'createdAt' => $notification->getCreatedAt()->format('Y-m-d H:i:s'),
                    'isRead' => $notification->isRead(),
                ];
            }, $notifications)
        ]);
    }

    #[Route('/{id}/read', name: 'app_notification_mark_read', methods: ['POST'])]
    public function markAsRead(int $id): JsonResponse
    {
        $user = $this->getUser();
        $notification = $this->notificationService->getAllNotifications($user, 1000);
        
        $targetNotification = null;
        foreach ($notification as $notif) {
            if ($notif->getId() === $id) {
                $targetNotification = $notif;
                break;
            }
        }

        if (!$targetNotification) {
            return new JsonResponse(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $this->notificationService->markAsRead($targetNotification);

        return new JsonResponse(['success' => true]);
    }

    #[Route('/mark-all-read', name: 'app_notifications_mark_all_read', methods: ['POST'])]
    public function markAllAsRead(): JsonResponse
    {
        $user = $this->getUser();
        $count = $this->notificationService->markAllAsRead($user);

        return new JsonResponse([
            'success' => true,
            'count' => $count,
            'message' => "Marked {$count} notification(s) as read"
        ]);
    }
}
