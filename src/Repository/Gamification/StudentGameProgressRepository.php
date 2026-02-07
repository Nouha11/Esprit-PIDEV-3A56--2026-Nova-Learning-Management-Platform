<?php

namespace App\Repository\Gamification;

use App\Entity\Gamification\StudentGameProgress;
use App\Entity\users\StudentProfile;
use App\Entity\Gamification\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StudentGameProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentGameProgress::class);
    }

    public function findByStudent(StudentProfile $student): array
    {
        return $this->createQueryBuilder('sgp')
            ->andWhere('sgp.student = :student')
            ->setParameter('student', $student)
            ->orderBy('sgp.lastPlayedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByStudentAndGame(StudentProfile $student, Game $game): ?StudentGameProgress
    {
        return $this->createQueryBuilder('sgp')
            ->andWhere('sgp.student = :student')
            ->andWhere('sgp.game = :game')
            ->setParameter('student', $student)
            ->setParameter('game', $game)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getStudentStats(StudentProfile $student): array
    {
        $qb = $this->createQueryBuilder('sgp')
            ->select('SUM(sgp.timesPlayed) as totalGamesPlayed')
            ->addSelect('SUM(sgp.timesWon) as totalGamesWon')
            ->addSelect('SUM(sgp.totalXPEarned) as totalXP')
            ->addSelect('SUM(sgp.totalTokensEarned) as totalTokens')
            ->andWhere('sgp.student = :student')
            ->setParameter('student', $student)
            ->getQuery()
            ->getOneOrNullResult();

        return [
            'totalGamesPlayed' => $qb['totalGamesPlayed'] ?? 0,
            'totalGamesWon' => $qb['totalGamesWon'] ?? 0,
            'totalXP' => $qb['totalXP'] ?? 0,
            'totalTokens' => $qb['totalTokens'] ?? 0,
        ];
    }

    public function getTopStudents(int $limit = 10): array
    {
        return $this->createQueryBuilder('sgp')
            ->select('IDENTITY(sgp.student) as studentId')
            ->addSelect('SUM(sgp.totalXPEarned) as totalXP')
            ->addSelect('SUM(sgp.timesWon) as totalWins')
            ->groupBy('sgp.student')
            ->orderBy('totalXP', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
