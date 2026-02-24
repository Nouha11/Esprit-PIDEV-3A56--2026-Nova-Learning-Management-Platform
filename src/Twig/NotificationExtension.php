<?php

namespace App\Twig;

use App\Service\Library\NotificationService;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class NotificationExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private NotificationService $notificationService,
        private Security $security
    ) {}

    public function getGlobals(): array
    {
        $user = $this->security->getUser();
        
        if (!$user) {
            return [
                'unread_notifications' => [],
                'unread_notifications_count' => 0,
            ];
        }

        $unreadNotifications = $this->notificationService->getUnreadNotifications($user);

        return [
            'unread_notifications' => $unreadNotifications,
            'unread_notifications_count' => count($unreadNotifications),
        ];
    }
}
