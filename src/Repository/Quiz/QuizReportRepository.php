<?php

namespace App\Repository\Quiz;

use App\Entity\Quiz\QuizReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class QuizReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizReport::class);
    }

    public function findPendingReports(): array
    {
        return $this->createQueryBuilder('qr')
            ->where('qr.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('qr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findResolvedReports(): array
    {
        return $this->createQueryBuilder('qr')
            ->where('qr.status IN (:statuses)')
            ->setParameter('statuses', ['resolved', 'dismissed'])
            ->orderBy('qr.resolvedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countPendingReports(): int
    {
        return $this->createQueryBuilder('qr')
            ->select('COUNT(qr.id)')
            ->where('qr.status = :status')
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
