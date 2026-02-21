<?php

namespace App\Service\game;

class LevelCalculatorService
{
    // Level thresholds with progressive XP requirements
    // Formula: Each level requires approximately 100 XP more than previous
    private const LEVEL_THRESHOLDS = [
        // Beginner Tier (Levels 1-5)
        1 => ['min' => 0, 'max' => 99, 'name' => 'Novice'],
        2 => ['min' => 100, 'max' => 249, 'name' => 'Novice'],
        3 => ['min' => 250, 'max' => 449, 'name' => 'Novice'],
        4 => ['min' => 450, 'max' => 699, 'name' => 'Novice'],
        5 => ['min' => 700, 'max' => 999, 'name' => 'Apprentice'],
        
        // Intermediate Tier (Levels 6-10)
        6 => ['min' => 1000, 'max' => 1349, 'name' => 'Apprentice'],
        7 => ['min' => 1350, 'max' => 1749, 'name' => 'Apprentice'],
        8 => ['min' => 1750, 'max' => 2199, 'name' => 'Apprentice'],
        9 => ['min' => 2200, 'max' => 2699, 'name' => 'Apprentice'],
        10 => ['min' => 2700, 'max' => 3249, 'name' => 'Skilled'],
        
        // Advanced Tier (Levels 11-15)
        11 => ['min' => 3250, 'max' => 3849, 'name' => 'Skilled'],
        12 => ['min' => 3850, 'max' => 4499, 'name' => 'Skilled'],
        13 => ['min' => 4500, 'max' => 5199, 'name' => 'Skilled'],
        14 => ['min' => 5200, 'max' => 5949, 'name' => 'Skilled'],
        15 => ['min' => 5950, 'max' => 6749, 'name' => 'Adept'],
        
        // Expert Tier (Levels 16-20)
        16 => ['min' => 6750, 'max' => 7599, 'name' => 'Adept'],
        17 => ['min' => 7600, 'max' => 8499, 'name' => 'Adept'],
        18 => ['min' => 8500, 'max' => 9449, 'name' => 'Adept'],
        19 => ['min' => 9450, 'max' => 10449, 'name' => 'Adept'],
        20 => ['min' => 10450, 'max' => 11499, 'name' => 'Professional'],
        
        // Professional Tier (Levels 21-25)
        21 => ['min' => 11500, 'max' => 12599, 'name' => 'Professional'],
        22 => ['min' => 12600, 'max' => 13749, 'name' => 'Professional'],
        23 => ['min' => 13750, 'max' => 14949, 'name' => 'Professional'],
        24 => ['min' => 14950, 'max' => 16199, 'name' => 'Professional'],
        25 => ['min' => 16200, 'max' => 17499, 'name' => 'Expert'],
        
        // Expert Tier (Levels 26-30)
        26 => ['min' => 17500, 'max' => 18849, 'name' => 'Expert'],
        27 => ['min' => 18850, 'max' => 20249, 'name' => 'Expert'],
        28 => ['min' => 20250, 'max' => 21699, 'name' => 'Expert'],
        29 => ['min' => 21700, 'max' => 23199, 'name' => 'Expert'],
        30 => ['min' => 23200, 'max' => 24749, 'name' => 'Veteran'],
        
        // Veteran Tier (Levels 31-35)
        31 => ['min' => 24750, 'max' => 26349, 'name' => 'Veteran'],
        32 => ['min' => 26350, 'max' => 27999, 'name' => 'Veteran'],
        33 => ['min' => 28000, 'max' => 29699, 'name' => 'Veteran'],
        34 => ['min' => 29700, 'max' => 31449, 'name' => 'Veteran'],
        35 => ['min' => 31450, 'max' => 33249, 'name' => 'Elite'],
        
        // Elite Tier (Levels 36-40)
        36 => ['min' => 33250, 'max' => 35099, 'name' => 'Elite'],
        37 => ['min' => 35100, 'max' => 36999, 'name' => 'Elite'],
        38 => ['min' => 37000, 'max' => 38949, 'name' => 'Elite'],
        39 => ['min' => 38950, 'max' => 40949, 'name' => 'Elite'],
        40 => ['min' => 40950, 'max' => 42999, 'name' => 'Master'],
        
        // Master Tier (Levels 41-45)
        41 => ['min' => 43000, 'max' => 45099, 'name' => 'Master'],
        42 => ['min' => 45100, 'max' => 47249, 'name' => 'Master'],
        43 => ['min' => 47250, 'max' => 49449, 'name' => 'Master'],
        44 => ['min' => 49450, 'max' => 51699, 'name' => 'Master'],
        45 => ['min' => 51700, 'max' => 53999, 'name' => 'Grandmaster'],
        
        // Grandmaster Tier (Levels 46-50)
        46 => ['min' => 54000, 'max' => 56349, 'name' => 'Grandmaster'],
        47 => ['min' => 56350, 'max' => 58749, 'name' => 'Grandmaster'],
        48 => ['min' => 58750, 'max' => 61199, 'name' => 'Grandmaster'],
        49 => ['min' => 61200, 'max' => 63699, 'name' => 'Grandmaster'],
        50 => ['min' => 63700, 'max' => 66249, 'name' => 'Champion'],
        
        // Champion Tier (Levels 51-55)
        51 => ['min' => 66250, 'max' => 68849, 'name' => 'Champion'],
        52 => ['min' => 68850, 'max' => 71499, 'name' => 'Champion'],
        53 => ['min' => 71500, 'max' => 74199, 'name' => 'Champion'],
        54 => ['min' => 74200, 'max' => 76949, 'name' => 'Champion'],
        55 => ['min' => 76950, 'max' => 79749, 'name' => 'Legend'],
        
        // Legend Tier (Levels 56-60)
        56 => ['min' => 79750, 'max' => 82599, 'name' => 'Legend'],
        57 => ['min' => 82600, 'max' => 85499, 'name' => 'Legend'],
        58 => ['min' => 85500, 'max' => 88449, 'name' => 'Legend'],
        59 => ['min' => 88450, 'max' => 91449, 'name' => 'Legend'],
        60 => ['min' => 91450, 'max' => PHP_INT_MAX, 'name' => 'Mythic'],
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
        return match(true) {
            $level >= 56 => 'danger',      // Legend/Mythic (56-60)
            $level >= 51 => 'warning',     // Champion (51-55)
            $level >= 46 => 'info',        // Grandmaster (46-50)
            $level >= 41 => 'success',     // Master (41-45)
            $level >= 36 => 'primary',     // Elite (36-40)
            $level >= 31 => 'dark',        // Veteran (31-35)
            $level >= 26 => 'secondary',   // Expert (26-30)
            $level >= 21 => 'info',        // Professional (21-25)
            $level >= 16 => 'primary',     // Adept (16-20)
            $level >= 11 => 'success',     // Skilled (11-15)
            $level >= 6 => 'warning',      // Apprentice (6-10)
            default => 'secondary',        // Novice (1-5)
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
        return match(true) {
            $level >= 56 => 'bi-stars',           // Legend/Mythic
            $level >= 51 => 'bi-trophy-fill',     // Champion
            $level >= 46 => 'bi-gem',             // Grandmaster
            $level >= 41 => 'bi-award-fill',      // Master
            $level >= 36 => 'bi-shield-fill',     // Elite
            $level >= 31 => 'bi-star-fill',       // Veteran
            $level >= 26 => 'bi-trophy',          // Expert
            $level >= 21 => 'bi-patch-check-fill',// Professional
            $level >= 16 => 'bi-bookmark-star',   // Adept
            $level >= 11 => 'bi-bookmark-fill',   // Skilled
            $level >= 6 => 'bi-star-half',        // Apprentice
            default => 'bi-star',                 // Novice
        };
    }
}
