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
     * Get current energy level
     */
    public function getCurrentEnergy(User $user): int
    {
        $studentProfile = $user->getStudentProfile();
        if (!$studentProfile) {
            return 100;
        }
        
        return $studentProfile->getEnergy() ?? 100;
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
