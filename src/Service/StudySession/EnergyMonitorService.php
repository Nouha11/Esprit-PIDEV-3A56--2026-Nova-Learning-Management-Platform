<?php

namespace App\Service\StudySession;

use App\Entity\users\User;
use Doctrine\ORM\EntityManagerInterface;

class EnergyMonitorService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Get current energy level with auto-refill
     */
    public function getCurrentEnergy(User $user): int
    {
        $studentProfile = $user->getStudentProfile();
        if (!$studentProfile) {
            return 100;
        }
        
        // Apply auto-refill before returning current energy
        $this->applyAutoRefill($studentProfile);
        
        return $studentProfile->getEnergy() ?? 100;
    }

    /**
     * Apply auto-refill: 1 energy point every 5 minutes
     */
    private function applyAutoRefill($studentProfile): void
    {
        $currentEnergy = $studentProfile->getEnergy() ?? 100;
        
        // If already at max, no need to refill
        if ($currentEnergy >= 100) {
            return;
        }
        
        $lastUpdate = $studentProfile->getLastEnergyUpdate();
        if (!$lastUpdate) {
            // Initialize last update time
            $studentProfile->setLastEnergyUpdate(new \DateTime());
            $this->entityManager->flush();
            return;
        }
        
        $now = new \DateTime();
        $minutesPassed = ($now->getTimestamp() - $lastUpdate->getTimestamp()) / 60;
        
        // Calculate energy to refill (1 point per 5 minutes)
        $energyToRefill = floor($minutesPassed / 5);
        
        if ($energyToRefill > 0) {
            $newEnergy = min(100, $currentEnergy + $energyToRefill);
            $studentProfile->setEnergy($newEnergy);
            $studentProfile->setLastEnergyUpdate($now);
            $this->entityManager->flush();
        }
    }
    
    /**
     * Get time until next energy refill in seconds
     */
    public function getTimeUntilNextRefill($user): int
    {
        $studentProfile = $user->getStudentProfile();
        if (!$studentProfile) {
            return 0;
        }
        
        $lastUpdate = $studentProfile->getLastEnergyUpdate();
        if (!$lastUpdate) {
            return 0;
        }
        
        $now = new \DateTime();
        $secondsSinceLastUpdate = $now->getTimestamp() - $lastUpdate->getTimestamp();
        $secondsUntilNextRefill = (5 * 60) - ($secondsSinceLastUpdate % (5 * 60));
        
        return max(0, $secondsUntilNextRefill);
    }

    /**
     * Check if energy is depleted
     */
    public function isEnergyDepleted(User $user): bool
    {
        return $this->getCurrentEnergy($user) <= 0;
    }

    /**
     * Deplete energy (for future use)
     */
    public function depleteEnergy(User $user, int $amount): void
    {
        $studentProfile = $user->getStudentProfile();
        if (!$studentProfile) {
            return;
        }

        $currentEnergy = $studentProfile->getEnergy() ?? 100;
        $newEnergy = max(0, $currentEnergy - $amount);
        $studentProfile->setEnergy($newEnergy);
        
        $this->entityManager->persist($studentProfile);
        $this->entityManager->flush();
    }

    /**
     * Restore energy (called after mini-game)
     */
    public function restoreEnergy(User $user, int $amount): void
    {
        $studentProfile = $user->getStudentProfile();
        if (!$studentProfile) {
            return;
        }

        $currentEnergy = $studentProfile->getEnergy() ?? 100;
        $newEnergy = min(100, $currentEnergy + $amount);
        $studentProfile->setEnergy($newEnergy);
        
        $this->entityManager->persist($studentProfile);
        $this->entityManager->flush();
    }
}
