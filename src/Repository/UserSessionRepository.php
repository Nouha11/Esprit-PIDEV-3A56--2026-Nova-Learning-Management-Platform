<?php

namespace App\Repository;

use App\Entity\users\UserSession;
use App\Entity\users\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSession>
 */
class UserSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSession::class);
    }

    /**
     * Get all active sessions for a user
     */
    public function findActiveSessions(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.user = :user')
            ->andWhere('s.isActive = :active')
            ->setParameter('user', $user)
            ->setParameter('active', true)
            ->orderBy('s.lastActivity', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find session by token
     */
    public function findByToken(string $token): ?UserSession
    {
        return $this->findOneBy(['sessionToken' => $token, 'isActive' => true]);
    }

    /**
     * Deactivate old sessions (inactive for more than 30 days)
     */
    public function deactivateOldSessions(): int
    {
        $date = new \DateTime('-30 days');
        
        return $this->createQueryBuilder('s')
            ->update()
            ->set('s.isActive', ':inactive')
            ->where('s.lastActivity < :date')
            ->andWhere('s.isActive = :active')
            ->setParameter('inactive', false)
            ->setParameter('date', $date)
            ->setParameter('active', true)
            ->getQuery()
            ->execute();
    }

    /**
     * Count active sessions for a user
     */
    public function countActiveSessions(User $user): int
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.user = :user')
            ->andWhere('s.isActive = :active')
            ->setParameter('user', $user)
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Delete inactive sessions
     */
    public function deleteInactiveSessions(): int
    {
        return $this->createQueryBuilder('s')
            ->delete()
            ->where('s.isActive = :inactive')
            ->setParameter('inactive', false)
            ->getQuery()
            ->execute();
    }
}
