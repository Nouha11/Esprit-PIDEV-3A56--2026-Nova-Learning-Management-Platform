<?php

namespace App\Repository\StudySession;

use App\Entity\StudySession\StudyStreak;
use App\Entity\users\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudyStreak>
 */
class StudyStreakRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudyStreak::class);
    }

    /**
     * Find or create streak for user
     *
     * @param User $user
     * @return StudyStreak
     */
    public function findOrCreateForUser(User $user): StudyStreak
    {
        $streak = $this->findOneBy(['user' => $user]);

        if (!$streak) {
            $streak = new StudyStreak();
            $streak->setUser($user);
            $this->getEntityManager()->persist($streak);
            $this->getEntityManager()->flush();
        }

        return $streak;
    }
}
