<?php

namespace App\Service\game;

use App\Entity\Gamification\Game;
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
    * Process game completion
    * Returns array with earned tokens and XP
    */
    public function processGameCompletion(Game $game, int $userId): array
    {
    // TODO: In future, integrate with User entity
    // For now, just return the rewards
        return [
        'tokens' => $game->getRewardTokens(),
        'xp' => $game->getRewardXP(),
        'game_name' => $game->getName()
        ];
    }

    /**
    * Check if user can afford to play game
    */
    public function canUserPlayGame(int $userTokenBalance, Game $game): bool
    {
        return $userTokenBalance >= $game->getTokenCost();
    }
}