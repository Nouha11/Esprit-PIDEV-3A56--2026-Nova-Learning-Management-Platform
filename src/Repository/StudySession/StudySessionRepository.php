<?php

namespace App\Repository\StudySession;

use App\Entity\StudySession\StudySession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudySession>
 */
class StudySessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudySession::class);
    }

    /**
     * Find study sessions by user, burnout risk, and date range filters
     *
     * @param int|null $userId
     * @param string|null $burnoutRisk
     * @param \DateTimeImmutable|null $dateFrom
     * @param \DateTimeImmutable|null $dateTo
     * @return StudySession[]
     */
    public function findByFilters(?int $userId = null, ?string $burnoutRisk = null, ?\DateTimeImmutable $dateFrom = null, ?\DateTimeImmutable $dateTo = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.user', 'u')
            ->leftJoin('s.planning', 'p')
            ->addSelect('u')
            ->addSelect('p');

        if ($userId) {
            $qb->andWhere('u.id = :userId')
               ->setParameter('userId', $userId);
        }

        if ($burnoutRisk) {
            $qb->andWhere('s.burnoutRisk = :burnoutRisk')
               ->setParameter('burnoutRisk', $burnoutRisk);
        }

        if ($dateFrom) {
            $qb->andWhere('s.startedAt >= :dateFrom')
               ->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo) {
            $qb->andWhere('s.startedAt <= :dateTo')
               ->setParameter('dateTo', $dateTo);
        }

        $qb->orderBy('s.startedAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Get aggregate statistics for study sessions
     *
     * @param \DateTimeImmutable|null $dateFrom
     * @param \DateTimeImmutable|null $dateTo
     * @return array
     */
    public function getAggregateStatistics(?\DateTimeImmutable $dateFrom = null, ?\DateTimeImmutable $dateTo = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id) as total_sessions')
            ->addSelect('AVG(s.duration) as average_duration')
            ->addSelect('SUM(s.xpEarned) as total_xp')
            ->addSelect('SUM(s.energyUsed) as total_energy');

        if ($dateFrom) {
            $qb->andWhere('s.startedAt >= :dateFrom')
               ->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo) {
            $qb->andWhere('s.startedAt <= :dateTo')
               ->setParameter('dateTo', $dateTo);
        }

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Get grouped statistics by dimension (course, user, or date)
     *
     * @param string $groupBy
     * @param \DateTimeImmutable|null $dateFrom
     * @param \DateTimeImmutable|null $dateTo
     * @return array
     */
    public function getGroupedStatistics(string $groupBy, ?\DateTimeImmutable $dateFrom = null, ?\DateTimeImmutable $dateTo = null): array
    {
        $qb = $this->createQueryBuilder('s');

        switch ($groupBy) {
            case 'course':
                $qb->leftJoin('s.planning', 'p')
                   ->leftJoin('p.course', 'c')
                   ->select('c.id as course_id')
                   ->addSelect('c.courseName as course_name')
                   ->addSelect('COUNT(s.id) as session_count')
                   ->addSelect('AVG(s.duration) as avg_duration')
                   ->addSelect('SUM(s.xpEarned) as total_xp')
                   ->groupBy('c.id');
                break;

            case 'user':
                $qb->leftJoin('s.user', 'u')
                   ->select('u.id as user_id')
                   ->addSelect('COUNT(s.id) as session_count')
                   ->addSelect('AVG(s.duration) as avg_duration')
                   ->addSelect('SUM(s.xpEarned) as total_xp')
                   ->groupBy('u.id');
                break;

            case 'date':
                $qb->select('DATE(s.startedAt) as session_date')
                   ->addSelect('COUNT(s.id) as session_count')
                   ->addSelect('AVG(s.duration) as avg_duration')
                   ->addSelect('SUM(s.xpEarned) as total_xp')
                   ->groupBy('session_date')
                   ->orderBy('session_date', 'DESC');
                break;

            default:
                return [];
        }

        if ($dateFrom) {
            $qb->andWhere('s.startedAt >= :dateFrom')
               ->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo) {
            $qb->andWhere('s.startedAt <= :dateTo')
               ->setParameter('dateTo', $dateTo);
        }

        return $qb->getQuery()->getResult();
    }
}
