<?php

namespace App\Service\StudySession;

use App\Entity\StudySession\StudyStreak;
use App\Entity\users\User;
use App\Repository\StudySession\StudyStreakRepository;
use Doctrine\ORM\EntityManagerInterface;

class StreakService
{
    public function __construct(
        private StudyStreakRepository $streakRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Update streak when a study session is completed
     * Increments streak if session is on a new day, updates lastStudyDate
     *
     * @param User $user
     * @param \DateTimeInterface $sessionDate
     * @return void
     */
    public function updateStreak(User $user, \DateTimeInterface $sessionDate): void
    {
        $streak = $this->streakRepository->findOrCreateForUser($user);
        
        // Convert to DateTimeImmutable for consistency
        $sessionDateImmutable = $sessionDate instanceof \DateTimeImmutable 
            ? $sessionDate 
            : \DateTimeImmutable::createFromMutable($sessionDate);
        
        $sessionDateOnly = $sessionDateImmutable->setTime(0, 0, 0);
        
        // If no last study date, this is the first session
        if ($streak->getLastStudyDate() === null) {
            $streak->setCurrentStreak(1);
            $streak->setLongestStreak(1);
            $streak->setLastStudyDate($sessionDateOnly);
            $this->entityManager->flush();
            return;
        }
        
        $lastStudyDateOnly = $streak->getLastStudyDate()->setTime(0, 0, 0);
        
        // Check if session is on the same day
        if ($sessionDateOnly == $lastStudyDateOnly) {
            // Same day, no streak change
            return;
        }
        
        // Check if session is on the next day (consecutive)
        $nextDay = $lastStudyDateOnly->modify('+1 day');
        if ($sessionDateOnly == $nextDay) {
            // Consecutive day, increment streak
            $newStreak = $streak->getCurrentStreak() + 1;
            $streak->setCurrentStreak($newStreak);
            
            // Update longest streak if necessary
            if ($newStreak > $streak->getLongestStreak()) {
                $streak->setLongestStreak($newStreak);
            }
            
            $streak->setLastStudyDate($sessionDateOnly);
            $this->entityManager->flush();
            return;
        }
        
        // Gap detected, reset streak to 1
        $streak->setCurrentStreak(1);
        $streak->setLastStudyDate($sessionDateOnly);
        $this->entityManager->flush();
    }

    /**
     * Get current streak count for user
     *
     * @param User $user
     * @return int
     */
    public function getCurrentStreak(User $user): int
    {
        $streak = $this->streakRepository->findOrCreateForUser($user);
        return $streak->getCurrentStreak();
    }

    /**
     * Get longest streak count for user
     *
     * @param User $user
     * @return int
     */
    public function getLongestStreak(User $user): int
    {
        $streak = $this->streakRepository->findOrCreateForUser($user);
        return $streak->getLongestStreak();
    }

    /**
     * Check if streak should be reset due to >24 hours gap
     * Resets streak to 0 if more than 24 hours have passed since last study
     *
     * @param User $user
     * @return void
     */
    public function checkAndResetStreak(User $user): void
    {
        $streak = $this->streakRepository->findOrCreateForUser($user);
        
        // If no last study date or streak is already 0, nothing to reset
        if ($streak->getLastStudyDate() === null || $streak->getCurrentStreak() === 0) {
            return;
        }
        
        $now = new \DateTimeImmutable();
        $lastStudyDate = $streak->getLastStudyDate()->setTime(0, 0, 0);
        $today = $now->setTime(0, 0, 0);
        
        // Calculate the difference in days
        $daysDifference = $today->diff($lastStudyDate)->days;
        
        // If more than 1 day has passed (gap of more than 24 hours), reset streak
        if ($daysDifference > 1) {
            $streak->setCurrentStreak(0);
            $this->entityManager->flush();
        }
    }
}
