<?php

namespace App\Repository;

use App\Entity\users\Notification;
use App\Entity\users\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * Get unread notifications for a user
     */
    public function findUnreadByUser(User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.user = :user')
            ->andWhere('n.isRead = :read')
            ->setParameter('user', $user)
            ->setParameter('read', false)
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all notifications for a user
     */
    public function findByUser(User $user, int $limit = 50): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.user = :user')
            ->setParameter('user', $user)
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count unread notifications for a user
     */
    public function countUnreadByUser(User $user): int
    {
        return $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.user = :user')
            ->andWhere('n.isRead = :read')
            ->setParameter('user', $user)
            ->setParameter('read', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsReadForUser(User $user): int
    {
        return $this->createQueryBuilder('n')
            ->update()
            ->set('n.isRead', ':read')
            ->set('n.readAt', ':readAt')
            ->where('n.user = :user')
            ->andWhere('n.isRead = :unread')
            ->setParameter('read', true)
            ->setParameter('readAt', new \DateTime())
            ->setParameter('user', $user)
            ->setParameter('unread', false)
            ->getQuery()
            ->execute();
    }

    /**
     * Delete old read notifications (older than 30 days)
     */
    public function deleteOldReadNotifications(): int
    {
        $date = new \DateTime('-30 days');
        
        return $this->createQueryBuilder('n')
            ->delete()
            ->where('n.isRead = :read')
            ->andWhere('n.createdAt < :date')
            ->setParameter('read', true)
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }
}
