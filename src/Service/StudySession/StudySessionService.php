<?php

namespace App\Service\StudySession;

use App\Entity\StudySession\StudySession;
use App\Repository\StudySession\StudySessionRepository;
use Doctrine\ORM\EntityManagerInterface;

class StudySessionService
{
    public function __construct(
        private EntityManagerInterface $em,
        private StudySessionRepository $studySessionRepository
    ) {}

    /* ==========================
       BASIC CREATE (controller needs this)
       ========================== */

    public function create(StudySession $studySession): void
    {
        $this->em->persist($studySession);
        $this->em->flush();
    }

    /* ==========================
       FILTERING
       ========================== */

    public function findByFilters(
        ?int $userId,
        ?string $burnoutRisk,
        ?\DateTimeImmutable $dateFrom,
        ?\DateTimeImmutable $dateTo
    ): array {
        return $this->studySessionRepository
            ->findByFilters($userId, $burnoutRisk, $dateFrom, $dateTo);
    }

    /* ==========================
       ANALYTICS
       ========================== */

    public function getAnalytics(
        ?\DateTimeImmutable $dateFrom,
        ?\DateTimeImmutable $dateTo,
        ?string $groupBy
    ): array {
        $sessions = $this->studySessionRepository
            ->findByFilters(null, null, $dateFrom, $dateTo);

        $analytics = [
            'total_sessions' => count($sessions),
            'average_duration' => 0,
            'total_xp' => 0,
            'burnout_distribution' => [
                'LOW' => 0,
                'MODERATE' => 0,
                'HIGH' => 0
            ]
        ];

        if (!$sessions) {
            return $analytics;
        }

        $totalDuration = 0;
        $totalXp = 0;

        foreach ($sessions as $session) {
            $totalDuration += $session->getActualDuration();
            $totalXp += $session->getXpEarned() ?? 0;

            $risk = $session->getBurnoutRisk();
            if (isset($analytics['burnout_distribution'][$risk])) {
                $analytics['burnout_distribution'][$risk]++;
            }
        }

        $analytics['average_duration'] = round($totalDuration / count($sessions), 2);
        $analytics['total_xp'] = $totalXp;

        if ($groupBy) {
            $analytics['grouped_data'] =
                $this->studySessionRepository->getGroupedStatistics(
                    $groupBy,
                    $dateFrom,
                    $dateTo
                );
        }

        return $analytics;
    }
}
