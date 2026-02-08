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
            'game_name' => $game->getName()
        ];

        if ($won) {
            // Award tokens and XP directly to student profile
            $tokens = $game->getRewardTokens();
            $xp = $game->getRewardXP();
            
            // Update student profile
            $student->addTokens($tokens);
            $student->addXP($xp);
            
            $rewards['tokens'] = $tokens;
            $rewards['xp'] = $xp;
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
}
