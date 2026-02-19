<?php

namespace App\Service\game;

use App\Entity\Gamification\Game;
use App\Entity\users\StudentProfile;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class TokenService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Check if student has enough tokens to play a game
     *
     * @param StudentProfile $student
     * @param Game $game
     * @return bool
     */
    public function hasEnoughTokens(StudentProfile $student, Game $game): bool
    {
        return $student->getTotalTokens() >= $game->getTokenCost();
    }

    /**
     * Get the number of tokens the student is missing
     *
     * @param StudentProfile $student
     * @param Game $game
     * @return int
     */
    public function getMissingTokens(StudentProfile $student, Game $game): int
    {
        $missing = $game->getTokenCost() - $student->getTotalTokens();
        return max(0, $missing);
    }

    /**
     * Deduct tokens from student's balance
     *
     * @param StudentProfile $student
     * @param Game $game
     * @param string $reason
     * @return bool Success status
     */
    public function deductTokens(StudentProfile $student, Game $game, string $reason = 'Game play'): bool
    {
        if (!$this->hasEnoughTokens($student, $game)) {
            $this->logger->warning('Insufficient tokens', [
                'student_id' => $student->getId(),
                'game_id' => $game->getId(),
                'required' => $game->getTokenCost(),
                'available' => $student->getTotalTokens(),
            ]);
            return false;
        }

        $previousBalance = $student->getTotalTokens();
        $student->deductTokens($game->getTokenCost());
        
        $this->entityManager->persist($student);
        $this->entityManager->flush();

        // Log the transaction
        $this->logTransaction(
            $student,
            $game,
            -$game->getTokenCost(),
            $previousBalance,
            $student->getTotalTokens(),
            $reason
        );

        return true;
    }

    /**
     * Add tokens to student's balance
     *
     * @param StudentProfile $student
     * @param int $amount
     * @param string $reason
     * @return void
     */
    public function addTokens(StudentProfile $student, int $amount, string $reason = 'Reward'): void
    {
        $previousBalance = $student->getTotalTokens();
        $student->addTokens($amount);
        
        $this->entityManager->persist($student);
        $this->entityManager->flush();

        // Log the transaction
        $this->logTransaction(
            $student,
            null,
            $amount,
            $previousBalance,
            $student->getTotalTokens(),
            $reason
        );
    }

    /**
     * Log token transaction
     *
     * @param StudentProfile $student
     * @param Game|null $game
     * @param int $amount (positive for credit, negative for debit)
     * @param int $previousBalance
     * @param int $newBalance
     * @param string $reason
     * @return void
     */
    private function logTransaction(
        StudentProfile $student,
        ?Game $game,
        int $amount,
        int $previousBalance,
        int $newBalance,
        string $reason
    ): void {
        $this->logger->info('Token transaction', [
            'student_id' => $student->getId(),
            'student_name' => $student->getFirstName() . ' ' . $student->getLastName(),
            'game_id' => $game?->getId(),
            'game_name' => $game?->getName(),
            'amount' => $amount,
            'previous_balance' => $previousBalance,
            'new_balance' => $newBalance,
            'reason' => $reason,
            'timestamp' => new \DateTime(),
        ]);
    }

    /**
     * Get token balance for a student
     *
     * @param StudentProfile $student
     * @return int
     */
    public function getBalance(StudentProfile $student): int
    {
        return $student->getTotalTokens();
    }

    /**
     * Check if a game is free (no token cost)
     *
     * @param Game $game
     * @return bool
     */
    public function isFreeGame(Game $game): bool
    {
        return $game->getTokenCost() === 0;
    }

    /**
     * Get token cost for a game
     *
     * @param Game $game
     * @return int
     */
    public function getGameCost(Game $game): int
    {
        return $game->getTokenCost();
    }

    /**
     * Validate token transaction
     *
     * @param StudentProfile $student
     * @param Game $game
     * @return array ['valid' => bool, 'message' => string, 'missing' => int]
     */
    public function validateTransaction(StudentProfile $student, Game $game): array
    {
        if ($this->isFreeGame($game)) {
            return [
                'valid' => true,
                'message' => 'This game is free to play',
                'missing' => 0,
            ];
        }

        if ($this->hasEnoughTokens($student, $game)) {
            return [
                'valid' => true,
                'message' => 'Sufficient tokens available',
                'missing' => 0,
            ];
        }

        $missing = $this->getMissingTokens($student, $game);
        return [
            'valid' => false,
            'message' => sprintf(
                'You need %d more token%s to play this game',
                $missing,
                $missing > 1 ? 's' : ''
            ),
            'missing' => $missing,
        ];
    }
}
