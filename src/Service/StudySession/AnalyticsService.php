<?php

namespace App\Service\StudySession;

use App\Entity\users\User;
use App\Repository\StudySession\StudySessionRepository;
use App\Repository\StudySession\PlanningRepository;

class AnalyticsService
{
    public function __construct(
        private StudySessionRepository $studySessionRepository,
        private PlanningRepository $planningRepository
    ) {
    }

    /**
     * Calculate total study time for completed sessions in date range
     *
     * @param User $user
     * @param \DateTimeInterface $start
     * @param \DateTimeInterface $end
     * @return int Total study time in minutes
     */
    public function getTotalStudyTime(User $user, \DateTimeInterface $start, \DateTimeInterface $end): int
    {
        $qb = $this->studySessionRepository->createQueryBuilder('s');
        
        $result = $qb
            ->select('SUM(s.duration) as total')
            ->where('s.user = :user')
            ->andWhere('s.completedAt IS NOT NULL')
            ->andWhere('s.completedAt >= :start')
            ->andWhere('s.completedAt <= :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    /**
     * Calculate total XP earned for completed sessions in date range
     *
     * @param User $user
     * @param \DateTimeInterface $start
     * @param \DateTimeInterface $end
     * @return int Total XP earned
     */
    public function getTotalXP(User $user, \DateTimeInterface $start, \DateTimeInterface $end): int
    {
        $qb = $this->studySessionRepository->createQueryBuilder('s');
        
        $result = $qb
            ->select('SUM(s.xpEarned) as total')
            ->where('s.user = :user')
            ->andWhere('s.completedAt IS NOT NULL')
            ->andWhere('s.completedAt >= :start')
            ->andWhere('s.completedAt <= :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    /**
     * Calculate completion rate (completed / total * 100)
     *
     * @param User $user
     * @param \DateTimeInterface $start
     * @param \DateTimeInterface $end
     * @return float Completion rate as percentage
     */
    public function getCompletionRate(User $user, \DateTimeInterface $start, \DateTimeInterface $end): float
    {
        // Count total planned sessions in date range
        $qbTotal = $this->planningRepository->createQueryBuilder('p');
        $totalPlanned = $qbTotal
            ->select('COUNT(p.id)')
            ->leftJoin('p.course', 'c')
            ->where('c.createdBy = :user')
            ->andWhere('p.scheduledDate >= :start')
            ->andWhere('p.scheduledDate <= :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();

        if ($totalPlanned == 0) {
            return 0.0;
        }

        // Count completed sessions in date range
        $qbCompleted = $this->studySessionRepository->createQueryBuilder('s');
        $totalCompleted = $qbCompleted
            ->select('COUNT(s.id)')
            ->where('s.user = :user')
            ->andWhere('s.completedAt IS NOT NULL')
            ->andWhere('s.completedAt >= :start')
            ->andWhere('s.completedAt <= :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();

        return round(($totalCompleted / $totalPlanned) * 100, 2);
    }

    /**
     * Get study time grouped by course
     *
     * @param User $user
     * @param \DateTimeInterface $start
     * @param \DateTimeInterface $end
     * @return array Array of ['course_name' => string, 'total_duration' => int]
     */
    public function getStudyTimeByCourse(User $user, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $qb = $this->studySessionRepository->createQueryBuilder('s');
        
        $results = $qb
            ->select('c.courseName as course_name')
            ->addSelect('SUM(s.duration) as total_duration')
            ->leftJoin('s.planning', 'p')
            ->leftJoin('p.course', 'c')
            ->where('s.user = :user')
            ->andWhere('s.completedAt IS NOT NULL')
            ->andWhere('s.completedAt >= :start')
            ->andWhere('s.completedAt <= :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->groupBy('c.id')
            ->orderBy('total_duration', 'DESC')
            ->getQuery()
            ->getResult();

        // Convert to array with integer values
        return array_map(function($row) {
            return [
                'course_name' => $row['course_name'],
                'total_duration' => (int) $row['total_duration']
            ];
        }, $results);
    }

    /**
     * Get XP earned over time grouped by date
     *
     * @param User $user
     * @param \DateTimeInterface $start
     * @param \DateTimeInterface $end
     * @return array Array of ['date' => string, 'total_xp' => int]
     */
    public function getXPOverTime(User $user, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $qb = $this->studySessionRepository->createQueryBuilder('s');
        
        $sessions = $qb
            ->select('s.completedAt', 's.xpEarned')
            ->where('s.user = :user')
            ->andWhere('s.completedAt IS NOT NULL')
            ->andWhere('s.completedAt >= :start')
            ->andWhere('s.completedAt <= :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('s.completedAt', 'ASC')
            ->getQuery()
            ->getResult();

        // Group by date in PHP
        $xpByDate = [];
        foreach ($sessions as $session) {
            $completedAt = $session['completedAt'];
            if ($completedAt instanceof \DateTimeInterface) {
                $dateKey = $completedAt->format('Y-m-d');
                if (!isset($xpByDate[$dateKey])) {
                    $xpByDate[$dateKey] = 0;
                }
                $xpByDate[$dateKey] += (int) $session['xpEarned'];
            }
        }

        // Convert to array format
        $result = [];
        foreach ($xpByDate as $date => $totalXp) {
            $result[] = [
                'date' => $date,
                'total_xp' => $totalXp
            ];
        }

        return $result;
    }

    /**
     * Analyze energy levels by time of day
     *
     * @param User $user
     * @return array Array of ['hour' => int, 'avg_energy' => string, 'session_count' => int]
     */
    public function getEnergyPatterns(User $user): array
    {
        $qb = $this->studySessionRepository->createQueryBuilder('s');
        
        $sessions = $qb
            ->select('s.startedAt', 's.energyLevel')
            ->where('s.user = :user')
            ->andWhere('s.energyLevel IS NOT NULL')
            ->setParameter('user', $user)
            ->orderBy('s.startedAt', 'ASC')
            ->getQuery()
            ->getResult();

        // Group by hour in PHP
        $patterns = [];
        foreach ($sessions as $session) {
            $startedAt = $session['startedAt'];
            if ($startedAt instanceof \DateTimeInterface) {
                $hour = (int) $startedAt->format('H');
                $energyLevel = $session['energyLevel'];
                
                if (!isset($patterns[$hour])) {
                    $patterns[$hour] = [
                        'hour' => $hour,
                        'energy_levels' => [],
                        'total_sessions' => 0
                    ];
                }
                
                if (!isset($patterns[$hour]['energy_levels'][$energyLevel])) {
                    $patterns[$hour]['energy_levels'][$energyLevel] = 0;
                }
                
                $patterns[$hour]['energy_levels'][$energyLevel]++;
                $patterns[$hour]['total_sessions']++;
            }
        }

        // Determine most common energy level for each hour
        $result = [];
        foreach ($patterns as $hour => $data) {
            arsort($data['energy_levels']);
            $mostCommonEnergy = array_key_first($data['energy_levels']);
            
            $result[] = [
                'hour' => $hour,
                'avg_energy' => $mostCommonEnergy,
                'session_count' => $data['total_sessions']
            ];
        }

        return $result;
    }
}
