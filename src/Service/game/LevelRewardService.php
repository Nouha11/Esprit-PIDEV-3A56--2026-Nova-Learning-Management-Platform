<?php

namespace App\Service\game;

use App\Entity\users\StudentProfile;
use App\Repository\Gamification\RewardRepository;
use Doctrine\ORM\EntityManagerInterface;

class LevelRewardService
{
    public function __construct(
        private EntityManagerInterface $em,
        private RewardRepository $rewardRepository
    ) {
    }

    /**
     * Check and award level-based achievements when a student levels up
     * Awards ALL milestones between previous level and current level (including surpassed ones)
     */
    public function checkAndAwardLevelRewards(StudentProfile $student, int $previousLevel): array
    {
        $awardedRewards = [];
        $currentLevel = $student->getLevel();

        // Get ALL milestones between previous level and current level
        // This ensures we award milestones even if user surpasses them (e.g., goes from level 3 to 11)
        $levelRewards = $this->rewardRepository->createQueryBuilder('r')
            ->where('r.type = :type')
            ->andWhere('r.isActive = :active')
            ->andWhere('r.requiredLevel > :previousLevel')
            ->andWhere('r.requiredLevel <= :currentLevel')
            ->setParameter('type', 'LEVEL_MILESTONE')
            ->setParameter('active', true)
            ->setParameter('previousLevel', $previousLevel)
            ->setParameter('currentLevel', $currentLevel)
            ->orderBy('r.requiredLevel', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($levelRewards as $reward) {
            // Check if student already has this reward
            if (!$student->hasEarnedReward($reward)) {
                // Award the reward
                $student->addEarnedReward($reward);
                
                // Award tokens (value represents token amount)
                $student->addTokens($reward->getValue());
                
                $awardedRewards[] = [
                    'name' => $reward->getName(),
                    'description' => $reward->getDescription(),
                    'tokens' => $reward->getValue(),
                    'level' => $reward->getRequiredLevel(),
                    'icon' => $reward->getIcon()
                ];
            }
        }

        if (!empty($awardedRewards)) {
            $this->em->flush();
        }

        return $awardedRewards;
    }

    /**
     * Get all available level milestones
     */
    public function getAllLevelMilestones(): array
    {
        return $this->rewardRepository->createQueryBuilder('r')
            ->where('r.type = :type')
            ->andWhere('r.isActive = :active')
            ->setParameter('type', 'LEVEL_MILESTONE')
            ->setParameter('active', true)
            ->orderBy('r.requiredLevel', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get student's progress towards level milestones
     */
    public function getStudentMilestoneProgress(StudentProfile $student): array
    {
        $milestones = $this->getAllLevelMilestones();
        $currentLevel = $student->getLevel();
        $progress = [];

        foreach ($milestones as $milestone) {
            $progress[] = [
                'reward' => $milestone,
                'earned' => $student->hasEarnedReward($milestone),
                'canEarn' => $currentLevel >= $milestone->getRequiredLevel(),
                'levelsRemaining' => max(0, $milestone->getRequiredLevel() - $currentLevel)
            ];
        }

        return $progress;
    }
}
