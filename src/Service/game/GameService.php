<?php

namespace App\Service\game;

use App\Entity\Gamification\Game;
use App\Entity\Gamification\StudentGameProgress;
use App\Entity\Gamification\StudentReward;
use App\Entity\Gamification\Reward;
use App\Entity\users\StudentProfile;
use App\Repository\Gamification\GameRepository;
use App\Repository\Gamification\StudentGameProgressRepository;
use App\Repository\Gamification\RewardRepository;
use Doctrine\ORM\EntityManagerInterface;

class GameService
{
    public function __construct(
        private EntityManagerInterface $em,
        private GameRepository $gameRepository,
        private StudentGameProgressRepository $progressRepository,
        private RewardRepository $rewardRepository
    ) {
    }
    
    /**
    * Create a new game
    */
    public function createGame(Game $game): Game
    {
        $this->em->persist($game);
        $this->em->flush();
        return $game;
    }

    /**
    * Update an existing game
    */
    public function updateGame(Game $game): Game
    {
        $this->em->flush();
        return $game;
    }

    /**
    * Delete a game (soft delete - set isActive to false)
    */
    public function deleteGame(Game $game): void
    {
        $game->setIsActive(false);
        $this->em->flush();
    }

    /**
    * Get all active games
    */
    public function getActiveGames(): array
    {
        return $this->gameRepository->findAllActive();
    }

    /**
    * Get games by difficulty level
    */
    public function getGamesByDifficulty(string $difficulty): array
    {
        return $this->gameRepository->findByDifficulty($difficulty);
    }
    
    /**
    * Process game completion and award rewards
    */
    public function processGameCompletion(Game $game, StudentProfile $student, bool $won = true): array
    {
        // Get or create progress record
        $progress = $this->progressRepository->findByStudentAndGame($student, $game);
        
        if (!$progress) {
            $progress = new StudentGameProgress();
            $progress->setStudent($student);
            $progress->setGame($game);
            $this->em->persist($progress);
        }

        // Update progress
        $progress->incrementTimesPlayed();
        $progress->setLastPlayedAt(new \DateTime());

        $rewards = [
            'tokens' => 0,
            'xp' => 0,
            'game_name' => $game->getName(),
            'badges' => []
        ];

        if ($won) {
            $progress->incrementTimesWon();
            
            // Award tokens and XP
            $tokens = $game->getRewardTokens();
            $xp = $game->getRewardXP();
            
            $progress->addTokens($tokens);
            $progress->addXP($xp);
            
            // Update student profile
            $student->addTokens($tokens);
            $student->addXP($xp);
            
            $rewards['tokens'] = $tokens;
            $rewards['xp'] = $xp;

            // Check for milestone rewards
            $milestoneRewards = $this->checkMilestones($student, $progress);
            $rewards['badges'] = $milestoneRewards;
        }

        $this->em->flush();

        return $rewards;
    }

    /**
    * Check if user can afford to play game
    */
    public function canUserPlayGame(int $userTokenBalance, Game $game): bool
    {
        return $userTokenBalance >= $game->getTokenCost();
    }

    /**
    * Deduct tokens for playing a game
    */
    public function deductGameCost(StudentProfile $student, Game $game): bool
    {
        if (!$this->canUserPlayGame($student->getTotalTokens(), $game)) {
            return false;
        }

        $student->deductTokens($game->getTokenCost());
        $this->em->flush();
        
        return true;
    }

    /**
    * Check for milestone achievements and award badges
    */
    private function checkMilestones(StudentProfile $student, StudentGameProgress $progress): array
    {
        $badges = [];

        // First win badge
        if ($progress->getTimesWon() === 1) {
            $badge = $this->awardBadge($student, 'First Victory', $progress->getGame());
            if ($badge) {
                $badges[] = $badge;
            }
        }

        // 10 wins badge
        if ($progress->getTimesWon() === 10) {
            $badge = $this->awardBadge($student, 'Game Master', $progress->getGame());
            if ($badge) {
                $badges[] = $badge;
            }
        }

        // Perfect win rate (10+ games)
        if ($progress->getTimesPlayed() >= 10 && $progress->getWinRate() === 100.0) {
            $badge = $this->awardBadge($student, 'Perfect Score', $progress->getGame());
            if ($badge) {
                $badges[] = $badge;
            }
        }

        return $badges;
    }

    /**
    * Award a badge to a student
    */
    private function awardBadge(StudentProfile $student, string $badgeName, ?Game $game = null): ?Reward
    {
        // Find the badge reward
        $badge = $this->rewardRepository->findOneBy([
            'name' => $badgeName,
            'type' => 'BADGE',
            'isActive' => true
        ]);

        if (!$badge) {
            return null;
        }

        // Check if student already has this badge
        $existingReward = $this->em->getRepository(StudentReward::class)
            ->findOneBy([
                'student' => $student,
                'reward' => $badge
            ]);

        if ($existingReward) {
            return null; // Already has this badge
        }

        // Award the badge
        $studentReward = new StudentReward();
        $studentReward->setStudent($student);
        $studentReward->setReward($badge);
        $studentReward->setEarnedFromGame($game);
        
        $this->em->persist($studentReward);

        return $badge;
    }

    /**
    * Get student's game statistics
    */
    public function getStudentStats(StudentProfile $student): array
    {
        return $this->progressRepository->getStudentStats($student);
    }

    /**
    * Get student's progress for a specific game
    */
    public function getStudentGameProgress(StudentProfile $student, Game $game): ?StudentGameProgress
    {
        return $this->progressRepository->findByStudentAndGame($student, $game);
    }
}