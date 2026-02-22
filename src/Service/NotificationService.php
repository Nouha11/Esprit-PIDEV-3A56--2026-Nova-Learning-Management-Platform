<?php

namespace App\Service;

use App\Entity\users\Notification;
use App\Entity\users\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private NotificationRepository $notificationRepository,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private string $fromEmail = 'noreply@nova-platform.com'
    ) {}

    /**
     * Create an in-app notification
     */
    public function createNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        ?array $metadata = null,
        ?string $actionUrl = null,
        ?string $icon = null,
        ?string $color = null
    ): Notification {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType($type);
        $notification->setTitle($title);
        $notification->setMessage($message);
        $notification->setMetadata($metadata);
        $notification->setActionUrl($actionUrl);
        $notification->setIcon($icon ?? 'bi-bell');
        $notification->setColor($color ?? 'primary');

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }

    /**
     * Send email notification
     */
    public function sendEmail(
        User $user,
        string $subject,
        string $template,
        array $context = []
    ): void {
        try {
            $email = (new TemplatedEmail())
                ->from(new Address($this->fromEmail, 'NOVA Platform'))
                ->to(new Address($user->getEmail(), $user->getUsername()))
                ->subject($subject)
                ->htmlTemplate($template)
                ->context($context);

            $this->mailer->send($email);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send email notification: ' . $e->getMessage());
        }
    }

    /**
     * Notify user about new device login (both in-app and email)
     */
    public function notifyNewDeviceLogin(
        User $user,
        string $browser,
        string $platform,
        string $device,
        ?string $location,
        ?string $ipAddress,
        \DateTimeInterface $loginTime
    ): void {
        $locationStr = $location ?? $ipAddress ?? 'Unknown location';
        
        // Create in-app notification
        $this->createNotification(
            user: $user,
            type: 'new_device_login',
            title: 'New Device Login Detected',
            message: "A new login was detected from {$browser} on {$platform} ({$device}) at {$locationStr}.",
            metadata: [
                'browser' => $browser,
                'platform' => $platform,
                'device' => $device,
                'location' => $locationStr,
                'ip_address' => $ipAddress,
                'login_time' => $loginTime->format('Y-m-d H:i:s'),
            ],
            actionUrl: '/sessions',
            icon: 'bi-shield-exclamation',
            color: 'warning'
        );

        // Send email notification
        $this->sendEmail(
            user: $user,
            subject: 'New Device Login to Your NOVA Account',
            template: 'emails/new_device_login.html.twig',
            context: [
                'user' => $user,
                'browser' => $browser,
                'platform' => $platform,
                'device' => $device,
                'location' => $locationStr,
                'ip_address' => $ipAddress,
                'login_time' => $loginTime,
            ]
        );
    }

    /**
     * Get unread notifications for a user
     */
    public function getUnreadNotifications(User $user, int $limit = 10): array
    {
        return $this->notificationRepository->findUnreadByUser($user, $limit);
    }

    /**
     * Get all notifications for a user
     */
    public function getAllNotifications(User $user, int $limit = 50): array
    {
        return $this->notificationRepository->findByUser($user, $limit);
    }

    /**
     * Count unread notifications
     */
    public function countUnread(User $user): int
    {
        return $this->notificationRepository->countUnreadByUser($user);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
        $this->entityManager->flush();
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(User $user): int
    {
        return $this->notificationRepository->markAllAsReadForUser($user);
    }

    /**
     * Delete old read notifications
     */
    public function cleanupOldNotifications(): int
    {
        return $this->notificationRepository->deleteOldReadNotifications();
    }
}
