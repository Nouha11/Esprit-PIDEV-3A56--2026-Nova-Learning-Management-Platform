<?php

namespace App\Repository\Gamification;

use App\Entity\Gamification\StudentReward;
use App\Entity\users\StudentProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StudentRewardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentReward::class);
    }

    public function findByStudent(StudentProfile $student): array
    {
        return $this->createQueryBuilder('sr')
            ->andWhere('sr.student = :student')
            ->setParameter('student', $student)
            ->orderBy('sr.earnedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findUnviewedByStudent(StudentProfile $student): array
    {
        return $this->createQueryBuilder('sr')
            ->andWhere('sr.student = :student')
            ->andWhere('sr.isViewed = :viewed')
            ->setParameter('student', $student)
            ->setParameter('viewed', false)
            ->orderBy('sr.earnedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByStudent(StudentProfile $student): int
    {
        return $this->createQueryBuilder('sr')
            ->select('COUNT(sr.id)')
            ->andWhere('sr.student = :student')
            ->setParameter('student', $student)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
