<?php

namespace App\Service\Library;

use App\Entity\Library\Notification;
use App\Entity\users\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service pour gérer les notifications utilisateur de la bibliothèque
 */
class NotificationService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    /**
     * Créer une notification pour un utilisateur
     */
    public function createNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $link = null
    ): Notification {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType($type);
        $notification->setTitle($title);
        $notification->setMessage($message);
        $notification->setLink($link);

        $this->em->persist($notification);
        $this->em->flush();

        return $notification;
    }

    /**
     * Notification pour prêt approuvé
     */
    public function notifyLoanApproved($loan): void
    {
        $this->createNotification(
            $loan->getUser(),
            'loan_approved',
            'Loan Approved!',
            sprintf('Your loan request for "%s" has been approved. You can pick it up at %s.', 
                $loan->getBook()->getTitle(),
                $loan->getLibrary()->getName()
            ),
            '/my-library'
        );
    }

    /**
     * Notification pour prêt rejeté
     */
    public function notifyLoanRejected($loan, ?string $reason = null): void
    {
        $message = sprintf('Your loan request for "%s" has been rejected.', 
            $loan->getBook()->getTitle()
        );
        
        if ($reason) {
            $message .= ' Reason: ' . $reason;
        }

        $this->createNotification(
            $loan->getUser(),
            'loan_rejected',
            'Loan Rejected',
            $message,
            '/my-library'
        );
    }

    /**
     * Notification pour prêt activé (livre récupéré)
     */
    public function notifyLoanActive($loan): void
    {
        $this->createNotification(
            $loan->getUser(),
            'loan_active',
            'Book Picked Up',
            sprintf('You have picked up "%s". Please return it by %s.', 
                $loan->getBook()->getTitle(),
                $loan->getEndAt()->format('M d, Y')
            ),
            '/my-library'
        );
    }

    /**
     * Notification pour prêt retourné
     */
    public function notifyLoanReturned($loan): void
    {
        $this->createNotification(
            $loan->getUser(),
            'loan_returned',
            'Book Returned',
            sprintf('Thank you for returning "%s".', 
                $loan->getBook()->getTitle()
            ),
            '/my-library'
        );
    }

    /**
     * Notification pour paiement réussi
     */
    public function notifyPaymentSuccess($payment): void
    {
        $this->createNotification(
            $payment->getUser(),
            'payment_success',
            'Payment Successful',
            sprintf('Your payment of $%s for "%s" was successful.', 
                number_format($payment->getAmount(), 2),
                $payment->getBook()->getTitle()
            ),
            '/my-library'
        );
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->setIsRead(true);
        $this->em->flush();
    }

    /**
     * Marquer toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsRead(User $user): void
    {
        $notifications = $this->em->getRepository(Notification::class)
            ->findBy(['user' => $user, 'isRead' => false]);

        foreach ($notifications as $notification) {
            $notification->setIsRead(true);
        }

        $this->em->flush();
    }

    /**
     * Obtenir les notifications non lues d'un utilisateur
     */
    public function getUnreadNotifications(User $user): array
    {
        return $this->em->getRepository(Notification::class)
            ->findBy(
                ['user' => $user, 'isRead' => false],
                ['createdAt' => 'DESC']
            );
    }

    /**
     * Obtenir le nombre de notifications non lues
     */
    public function getUnreadCount(User $user): int
    {
        return $this->em->getRepository(Notification::class)
            ->count(['user' => $user, 'isRead' => false]);
    }

    /**
     * Obtenir toutes les notifications d'un utilisateur
     */
    public function getAllNotifications(User $user, int $limit = 20): array
    {
        return $this->em->getRepository(Notification::class)
            ->findBy(
                ['user' => $user],
                ['createdAt' => 'DESC'],
                $limit
            );
    }

    /**
     * Obtenir une notification par ID
     */
    public function getNotificationById(int $id): ?Notification
    {
        return $this->em->getRepository(Notification::class)->find($id);
    }
}
