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
    ) {}


    public function findAll(): array
    {
        return $this->planningRepository->findAll();
    }

    public function create(Planning $planning): void
    {
        $this->em->persist($planning);
        $this->em->flush();
    }

    public function update(Planning $planning): void
    {
        $this->em->flush();
    }

    /**
     * Controller-friendly wrapper
     */
    public function findByFilters(array $filters): array
    {
        return $this->planningRepository->findByFilters(
            $filters['status'] ?? null,
            $filters['startDate'] ?? null,
            $filters['endDate'] ?? null
        );
    }

    public function updateStatus(Planning $planning, string $newStatus): Planning
    {
        if (!in_array($newStatus, self::ALLOWED_STATUSES, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid status "%s". Allowed values: %s',
                    $newStatus,
                    implode(', ', self::ALLOWED_STATUSES)
                )
            );
        }

        $planning->setStatus($newStatus);
        $this->em->flush();

        return $planning;
    }

    public function cancelPlanning(Planning $planning): Planning
    {
        $planning->setStatus(Planning::STATUS_CANCELLED);
        $this->em->flush();

        return $planning;
    }
}
