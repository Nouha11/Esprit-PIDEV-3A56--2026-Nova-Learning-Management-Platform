<?php

namespace App\Repository\StudySession;

use App\Entity\StudySession\Planning;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Planning>
 */
class PlanningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Planning::class);
    }

    /**
     * Find planning sessions by status, date range filters
     *
     * @param string|null $status
     * @param \DateTimeImmutable|null $dateFrom
     * @param \DateTimeImmutable|null $dateTo
     * @return Planning[]
     */
    public function findByFilters(?string $status = null, ?\DateTimeImmutable $dateFrom = null, ?\DateTimeImmutable $dateTo = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.course', 'c')
            ->addSelect('c');

        if ($status) {
            $qb->andWhere('p.status = :status')
               ->setParameter('status', $status);
        }

        if ($dateFrom) {
            $qb->andWhere('p.scheduledDate >= :dateFrom')
               ->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo) {
            $qb->andWhere('p.scheduledDate <= :dateTo')
               ->setParameter('dateTo', $dateTo);
        }

        $qb->orderBy('p.scheduledDate', 'DESC')
           ->addOrderBy('p.scheduledTime', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
