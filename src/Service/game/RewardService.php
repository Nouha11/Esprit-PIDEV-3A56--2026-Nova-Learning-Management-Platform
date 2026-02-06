<?php

namespace App\Service\game;

use App\Entity\Gamification\Reward;
use App\Repository\Gamification\RewardRepository;
use Doctrine\ORM\EntityManagerInterface;
class RewardService
{
    public function __construct(
        private EntityManagerInterface $em,
        private RewardRepository $rewardRepository
    ) {
    }
    
    /**
    * Create a new reward
    */
    public function createReward(Reward $reward): Reward
    {
        $this->em->persist($reward);
        $this->em->flush();
        return $reward;
    }

    /**
    * Update reward
    */
    public function updateReward(Reward $reward): Reward
    {
        $this->em->flush();
        return $reward;
    }
    
    /**
    * Delete reward (soft delete)
    */
    public function deleteReward(Reward $reward): void
    {
        $reward->setIsActive(false);
        $this->em->flush();
    }

    /**
    * Get all active rewards
    */
    public function getActiveRewards(): array
    {
        return $this->rewardRepository->findAllActive();
    }

    /**
    * Get rewards by type
    */
    public function getRewardsByType(string $type): array
    {
        return $this->rewardRepository->findByType($type);
    }

    /**
    * Check if user qualifies for a reward
    */
    public function checkRewardEligibility(Reward $reward, array $userStats): bool
    {
        // TODO: Implement logic based on reward requirements
        // For now, return true
        return true;
    }
}