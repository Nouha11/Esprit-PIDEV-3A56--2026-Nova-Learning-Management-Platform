<?php

namespace App\Service\StudySession;

use App\Entity\StudySession\Planning;
use App\Repository\StudySession\PlanningRepository;
use Doctrine\ORM\EntityManagerInterface;

class PlanningService
{
    private const ALLOWED_STATUSES = [
        Planning::STATUS_SCHEDULED,
        Planning::STATUS_COMPLETED,
        Planning::STATUS_MISSED,
        Planning::STATUS_CANCELLED
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private PlanningRepository $planningRepository
    ) {
    }

    /**
     * Update planning status with validation
     */
    public function updateStatus(Planning $planning, string $newStatus): Planning
    {
        if (!in_array($newStatus, self::ALLOWED_STATUSES, true)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid status "%s". Allowed values: %s', 
                    $newStatus, 
                    implode(', ', self::ALLOWED_STATUSES)
                )
            );
        }

        $planning->setStatus($newStatus);
        $this->em->flush();

        return $planning;
    }

    /**
     * Cancel a planning session
     */
    public function cancelPlanning(Planning $planning): Planning
    {
        $planning->setStatus(Planning::STATUS_CANCELLED);
        $this->em->flush();

        return $planning;
    }

    /**
     * Find planning sessions by filters
     */
    public function findByFilters(?string $status, ?\DateTimeImmutable $dateFrom, ?\DateTimeImmutable $dateTo): array
    {
        return $this->planningRepository->findByFilters($status, $dateFrom, $dateTo);
    }
}
