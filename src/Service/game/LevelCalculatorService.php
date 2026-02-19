<?php

namespace App\Service\game;

class LevelCalculatorService
{
    // Level thresholds: [min XP, max XP, level name]
    private const LEVEL_THRESHOLDS = [
        1 => ['min' => 0, 'max' => 99, 'name' => 'Novice'],
        2 => ['min' => 100, 'max' => 249, 'name' => 'Apprentice'],
        3 => ['min' => 250, 'max' => 499, 'name' => 'Adept'],
        4 => ['min' => 500, 'max' => 999, 'name' => 'Expert'],
        5 => ['min' => 1000, 'max' => PHP_INT_MAX, 'name' => 'Master'],
    ];

    /**
     * Calculate level information based on total XP
     *
     * @param int $xp Total experience points
     * @return array ['level' => int, 'name' => string, 'progress' => float, 'currentLevelMin' => int, 'nextLevelMin' => int]
     */
    public function calculateLevel(int $xp): array
    {
        $level = 1;
        $levelName = 'Novice';
        $currentLevelMin = 0;
        $nextLevelMin = 100;

        // Find the current level
        foreach (self::LEVEL_THRESHOLDS as $lvl => $threshold) {
            if ($xp >= $threshold['min'] && $xp <= $threshold['max']) {
                $level = $lvl;
                $levelName = $threshold['name'];
                $currentLevelMin = $threshold['min'];
                
                // Get next level threshold (if not max level)
                if ($lvl < count(self::LEVEL_THRESHOLDS)) {
                    $nextLevelMin = self::LEVEL_THRESHOLDS[$lvl + 1]['min'];
                } else {
                    // Max level reached
                    $nextLevelMin = $threshold['max'];
                }
                break;
            }
        }

        // Calculate progress percentage to next level
        $progress = $this->calculateProgress($xp, $currentLevelMin, $nextLevelMin, $level);

        return [
            'level' => $level,
            'name' => $levelName,
            'progress' => $progress,
            'currentLevelMin' => $currentLevelMin,
            'nextLevelMin' => $nextLevelMin,
            'xpInCurrentLevel' => $xp - $currentLevelMin,
            'xpNeededForNextLevel' => max(0, $nextLevelMin - $xp),
        ];
    }

    /**
     * Calculate progress percentage to next level
     *
     * @param int $currentXp Current XP
     * @param int $currentLevelMin Minimum XP for current level
     * @param int $nextLevelMin Minimum XP for next level
     * @param int $level Current level
     * @return float Progress percentage (0-100)
     */
    private function calculateProgress(int $currentXp, int $currentLevelMin, int $nextLevelMin, int $level): float
    {
        // If max level, return 100%
        if ($level >= count(self::LEVEL_THRESHOLDS)) {
            return 100.0;
        }

        $xpInCurrentLevel = $currentXp - $currentLevelMin;
        $xpRequiredForNextLevel = $nextLevelMin - $currentLevelMin;

        if ($xpRequiredForNextLevel <= 0) {
            return 100.0;
        }

        $progress = ($xpInCurrentLevel / $xpRequiredForNextLevel) * 100;
        
        return round(min(100.0, max(0.0, $progress)), 2);
    }

    /**
     * Get level name by level number
     *
     * @param int $level Level number
     * @return string Level name
     */
    public function getLevelName(int $level): string
    {
        return self::LEVEL_THRESHOLDS[$level]['name'] ?? 'Unknown';
    }

    /**
     * Get all level thresholds
     *
     * @return array Level thresholds
     */
    public function getLevelThresholds(): array
    {
        return self::LEVEL_THRESHOLDS;
    }

    /**
     * Get XP required for a specific level
     *
     * @param int $level Target level
     * @return int Minimum XP required
     */
    public function getXpForLevel(int $level): int
    {
        return self::LEVEL_THRESHOLDS[$level]['min'] ?? 0;
    }

    /**
     * Get level badge color based on level
     *
     * @param int $level Level number
     * @return string Bootstrap color class
     */
    public function getLevelBadgeColor(int $level): string
    {
        return match($level) {
            1 => 'secondary',
            2 => 'info',
            3 => 'primary',
            4 => 'warning',
            5 => 'success',
            default => 'secondary',
        };
    }

    /**
     * Get level icon based on level
     *
     * @param int $level Level number
     * @return string Bootstrap icon class
     */
    public function getLevelIcon(int $level): string
    {
        return match($level) {
            1 => 'bi-star',
            2 => 'bi-star-fill',
            3 => 'bi-trophy',
            4 => 'bi-trophy-fill',
            5 => 'bi-gem',
            default => 'bi-star',
        };
    }
}
