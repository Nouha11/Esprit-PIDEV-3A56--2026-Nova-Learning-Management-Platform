<?php

namespace App\Service\game;

use App\Entity\Gamification\Game;
use App\Entity\users\StudentProfile;
use App\Repository\Gamification\GameRepository;
use Doctrine\ORM\EntityManagerInterface;

class GameService
{
    public function __construct(
        private EntityManagerInterface $em,
        private GameRepository $gameRepository
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
        // Doctrine automatically handles the rewards relationship
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
        $rewards = [
            'tokens' => 0,
            'xp' => 0,
            'special_rewards' => [],
            'game_name' => $game->getName()
        ];

        if ($won) {
            // Award base tokens and XP
            $tokens = $game->getRewardTokens();
            $xp = $game->getRewardXP();
            
            $student->addTokens($tokens);
            $student->addXP($xp);
            
            $rewards['tokens'] = $tokens;
            $rewards['xp'] = $xp;

            // Process special rewards associated with this game
            foreach ($game->getRewards() as $reward) {
                if ($reward->isActive()) {
                    $specialReward = $this->processSpecialReward($reward, $student);
                    if ($specialReward) {
                        $rewards['special_rewards'][] = $specialReward;
                    }
                }
            }

            $this->em->flush();
        }

        return $rewards;
    }

    /**
     * Process a special reward for a student
     */
    private function processSpecialReward(\App\Entity\Gamification\Reward $reward, StudentProfile $student): ?array
    {
        $rewardData = [
            'name' => $reward->getName(),
            'type' => $reward->getType(),
            'value' => $reward->getValue(),
            'description' => $reward->getDescription()
        ];

        // Handle different reward types
        switch ($reward->getType()) {
            case 'BONUS_XP':
                $student->addXP($reward->getValue());
                $rewardData['awarded'] = '+' . $reward->getValue() . ' XP';
                break;

            case 'BONUS_TOKENS':
                $student->addTokens($reward->getValue());
                $rewardData['awarded'] = '+' . $reward->getValue() . ' Tokens';
                break;

            case 'BADGE':
            case 'ACHIEVEMENT':
                // Check if student already has this reward
                if (!$student->hasEarnedReward($reward)) {
                    $student->addEarnedReward($reward);
                    $rewardData['awarded'] = 'Unlocked!';
                } else {
                    // Student already has this reward, don't award again
                    return null;
                }
                break;

            default:
                $rewardData['awarded'] = 'Unknown reward type';
                break;
        }

        return $rewardData;
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
     * Get total potential rewards for a game (base + special)
     */
    public function calculateTotalPotentialRewards(Game $game): array
    {
        $totalTokens = $game->getRewardTokens();
        $totalXP = $game->getRewardXP();
        $badges = [];
        $achievements = [];

        foreach ($game->getRewards() as $reward) {
            if (!$reward->isActive()) {
                continue;
            }

            switch ($reward->getType()) {
                case 'BONUS_XP':
                    $totalXP += $reward->getValue();
                    break;
                case 'BONUS_TOKENS':
                    $totalTokens += $reward->getValue();
                    break;
                case 'BADGE':
                    $badges[] = $reward->getName();
                    break;
                case 'ACHIEVEMENT':
                    $achievements[] = $reward->getName();
                    break;
            }
        }

        return [
            'total_tokens' => $totalTokens,
            'total_xp' => $totalXP,
            'badges' => $badges,
            'achievements' => $achievements
        ];
    }

    /**
     * Get games offering a specific reward
     */
    public function getGamesOfferingReward(\App\Entity\Gamification\Reward $reward): array
    {
        return $this->gameRepository->createQueryBuilder('g')
            ->innerJoin('g.rewards', 'r')
            ->where('r.id = :rewardId')
            ->andWhere('g.isActive = :active')
            ->setParameter('rewardId', $reward->getId())
            ->setParameter('active', true)
            ->orderBy('g.difficulty', 'ASC')
            ->addOrderBy('g.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}