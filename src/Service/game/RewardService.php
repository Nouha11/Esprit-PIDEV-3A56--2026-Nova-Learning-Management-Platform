<?php

namespace App\Service\game;

use App\Entity\Gamification\Reward;
use App\Entity\users\StudentProfile;
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
     * Award a reward to a student
     */
    public function awardRewardToStudent(Reward $reward, StudentProfile $student): bool
    {
        // Check if student already has this reward
        if ($student->hasEarnedReward($reward)) {
            return false; // Already earned
        }

        // Award based on reward type
        switch ($reward->getType()) {
            case 'BONUS_XP':
                $student->addXP($reward->getValue());
                break;

            case 'BONUS_TOKENS':
                $student->addTokens($reward->getValue());
                break;

            case 'BADGE':
            case 'ACHIEVEMENT':
                $student->addEarnedReward($reward);
                break;
        }

        $this->em->flush();
        return true;
    }

    /**
     * Get all rewards earned by a student
     */
    public function getStudentRewards(StudentProfile $student): array
    {
        return $student->getEarnedRewards()->toArray();
    }

    /**
     * Get rewards by category for a student
     */
    public function getStudentRewardsByType(StudentProfile $student, string $type): array
    {
        return array_filter(
            $student->getEarnedRewards()->toArray(),
            fn($reward) => $reward->getType() === $type
        );
    }

    /**
     * Check if student has earned a specific reward
     */
    public function hasStudentEarnedReward(StudentProfile $student, Reward $reward): bool
    {
        return $student->hasEarnedReward($reward);
    }

    /**
     * Get student's progress towards all rewards
     */
    public function getStudentRewardProgress(StudentProfile $student): array
    {
        $allRewards = $this->getActiveRewards();
        $earnedRewards = $student->getEarnedRewards()->toArray();
        
        $earnedIds = array_map(fn($r) => $r->getId(), $earnedRewards);

        return [
            'total_rewards' => count($allRewards),
            'earned_rewards' => count($earnedRewards),
            'progress_percentage' => count($allRewards) > 0 
                ? round((count($earnedRewards) / count($allRewards)) * 100, 2) 
                : 0,
            'rewards' => array_map(function($reward) use ($earnedIds) {
                return [
                    'id' => $reward->getId(),
                    'name' => $reward->getName(),
                    'type' => $reward->getType(),
                    'earned' => in_array($reward->getId(), $earnedIds),
                    'icon' => $reward->getIcon()
                ];
            }, $allRewards)
        ];
    }

    /**
     * Get games that offer a specific reward
     */
    public function getGamesOfferingReward(Reward $reward): array
    {
        return $reward->getGames()->filter(fn($game) => $game->isActive())->toArray();
    }

    /**
     * Check if user qualifies for a reward based on their stats
     */
    public function checkRewardEligibility(Reward $reward, StudentProfile $student): bool
    {
        // If no requirement specified, anyone can earn it through gameplay
        if (!$reward->getRequirement()) {
            return true;
        }

        // Parse requirement string and check against student stats
        // Example requirements: "level:5", "tokens:1000", "xp:500"
        $requirement = $reward->getRequirement();
        
        // Simple parsing - you can make this more sophisticated
        if (stripos($requirement, 'level') !== false) {
            preg_match('/level[:\s]+(\d+)/', $requirement, $matches);
            if (isset($matches[1])) {
                return $student->getLevel() >= (int)$matches[1];
            }
        }
        
        if (stripos($requirement, 'xp') !== false) {
            preg_match('/xp[:\s]+(\d+)/', $requirement, $matches);
            if (isset($matches[1])) {
                return $student->getTotalXP() >= (int)$matches[1];
            }
        }
        
        if (stripos($requirement, 'token') !== false) {
            preg_match('/token[s]?[:\s]+(\d+)/', $requirement, $matches);
            if (isset($matches[1])) {
                return $student->getTotalTokens() >= (int)$matches[1];
            }
        }

        // Default to true if we can't parse the requirement
        return true;
    }

    /**
     * Get statistics about a reward
     */
    public function getRewardStatistics(Reward $reward): array
    {
        return [
            'id' => $reward->getId(),
            'name' => $reward->getName(),
            'type' => $reward->getType(),
            'total_games' => $reward->getGames()->count(),
            'active_games' => $reward->getGames()->filter(fn($g) => $g->isActive())->count(),
            'students_earned' => $reward->getStudents()->count(),
            'is_active' => $reward->isActive(),
            'value' => $reward->getValue()
        ];
    }
}