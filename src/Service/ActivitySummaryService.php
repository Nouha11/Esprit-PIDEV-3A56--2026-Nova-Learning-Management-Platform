<?php

namespace App\Service;

use App\Entity\users\User;
use App\Repository\UserActivityRepository;

class ActivitySummaryService
{
    public function __construct(
        private UserActivityRepository $activityRepository
    ) {}

    /**
     * Generate smart activity summary for a user
     */
    public function generateSummary(User $user): array
    {
        // Get activities from last 7 days
        $startDate = new \DateTime('-7 days');
        $endDate = new \DateTime();
        $activities = $this->activityRepository->findByUserAndDateRange($user, $startDate, $endDate, 100);

        if (empty($activities)) {
            return [
                'summary' => 'No recent activity in the last 7 days.',
                'insights' => [],
                'recommendations' => ['Start exploring the platform to track your progress!'],
                'stats' => [
                    'total_activities' => 0,
                    'most_active_day' => null,
                    'streak' => 0,
                ],
                'highlights' => [],
            ];
        }

        // Analyze activities
        $activityTypes = $this->groupByType($activities);
        $dailyActivity = $this->groupByDay($activities);
        $insights = $this->generateInsights($activityTypes, $dailyActivity);
        $recommendations = $this->generateRecommendations($activityTypes, $user);
        $highlights = $this->extractHighlights($activities);
        $streak = $this->calculateStreak($dailyActivity);

        // Generate natural language summary
        $summary = $this->generateNaturalLanguageSummary($activityTypes, $dailyActivity, $streak);

        return [
            'summary' => $summary,
            'insights' => $insights,
            'recommendations' => $recommendations,
            'stats' => [
                'total_activities' => count($activities),
                'most_active_day' => $this->getMostActiveDay($dailyActivity),
                'streak' => $streak,
            ],
            'highlights' => $highlights,
        ];
    }

    /**
     * Group activities by type
     */
    private function groupByType(array $activities): array
    {
        $grouped = [];
        foreach ($activities as $activity) {
            $type = $activity->getActivityType();
            if (!isset($grouped[$type])) {
                $grouped[$type] = 0;
            }
            $grouped[$type]++;
        }
        arsort($grouped);
        return $grouped;
    }

    /**
     * Group activities by day
     */
    private function groupByDay(array $activities): array
    {
        $grouped = [];
        foreach ($activities as $activity) {
            $day = $activity->getCreatedAt()->format('Y-m-d');
            if (!isset($grouped[$day])) {
                $grouped[$day] = 0;
            }
            $grouped[$day]++;
        }
        ksort($grouped);
        return $grouped;
    }

    /**
     * Generate insights from activity patterns
     */
    private function generateInsights(array $activityTypes, array $dailyActivity): array
    {
        $insights = [];

        // Most common activity
        if (!empty($activityTypes)) {
            $topActivity = array_key_first($activityTypes);
            $count = $activityTypes[$topActivity];
            $insights[] = [
                'type' => 'top_activity',
                'icon' => 'bi-star',
                'color' => 'primary',
                'text' => "Most frequent activity: " . $this->formatActivityType($topActivity) . " ({$count} times)",
            ];
        }

        // Activity diversity
        $uniqueTypes = count($activityTypes);
        if ($uniqueTypes >= 5) {
            $insights[] = [
                'type' => 'diversity',
                'icon' => 'bi-grid',
                'color' => 'success',
                'text' => "Great variety! You've engaged in {$uniqueTypes} different types of activities",
            ];
        }

        // Consistency check
        $activeDays = count($dailyActivity);
        if ($activeDays >= 5) {
            $insights[] = [
                'type' => 'consistency',
                'icon' => 'bi-calendar-check',
                'color' => 'info',
                'text' => "Excellent consistency! Active on {$activeDays} out of 7 days",
            ];
        } elseif ($activeDays <= 2) {
            $insights[] = [
                'type' => 'consistency',
                'icon' => 'bi-calendar-x',
                'color' => 'warning',
                'text' => "Only active on {$activeDays} days this week. Try to be more consistent!",
            ];
        }

        // Learning pattern detection
        if (isset($activityTypes['quiz_completed']) && $activityTypes['quiz_completed'] >= 3) {
            $insights[] = [
                'type' => 'learning',
                'icon' => 'bi-lightning-charge',
                'color' => 'purple',
                'text' => "Quiz enthusiast! Completed {$activityTypes['quiz_completed']} quizzes this week",
            ];
        }

        if (isset($activityTypes['game_played']) && $activityTypes['game_played'] >= 5) {
            $insights[] = [
                'type' => 'gaming',
                'icon' => 'bi-controller',
                'color' => 'primary',
                'text' => "Gaming champion! Played {$activityTypes['game_played']} games this week",
            ];
        }

        return $insights;
    }

    /**
     * Generate personalized recommendations
     */
    private function generateRecommendations(array $activityTypes, User $user): array
    {
        $recommendations = [];

        // Recommend quizzes if not taken
        if (!isset($activityTypes['quiz_completed']) || $activityTypes['quiz_completed'] < 2) {
            $recommendations[] = [
                'icon' => 'bi-lightning-charge',
                'color' => 'purple',
                'text' => 'Try taking more quizzes to test your knowledge',
                'action' => 'Take a Quiz',
                'link' => '/game/quiz',
            ];
        }

        // Recommend games if not played
        if (!isset($activityTypes['game_played']) || $activityTypes['game_played'] < 3) {
            $recommendations[] = [
                'icon' => 'bi-controller',
                'color' => 'primary',
                'text' => 'Play educational games to make learning fun',
                'action' => 'Play Games',
                'link' => '/games',
            ];
        }

        // Recommend rewards if not claimed
        if (!isset($activityTypes['reward_claimed'])) {
            $recommendations[] = [
                'icon' => 'bi-gift',
                'color' => 'warning',
                'text' => 'Check out available rewards you can claim',
                'action' => 'Browse Rewards',
                'link' => '/rewards',
            ];
        }

        // Recommend profile completion
        if (!isset($activityTypes['profile_updated'])) {
            $recommendations[] = [
                'icon' => 'bi-person-check',
                'color' => 'info',
                'text' => 'Complete your profile to unlock more features',
                'action' => 'Edit Profile',
                'link' => '/profile/edit',
            ];
        }

        // Recommend 2FA if not enabled
        if (!isset($activityTypes['2fa_enabled']) && !$user->isTotpEnabled()) {
            $recommendations[] = [
                'icon' => 'bi-shield-check',
                'color' => 'success',
                'text' => 'Enable Two-Factor Authentication for better security',
                'action' => 'Enable 2FA',
                'link' => '/2fa/setup',
            ];
        }

        return array_slice($recommendations, 0, 3); // Return top 3 recommendations
    }

    /**
     * Extract activity highlights
     */
    private function extractHighlights(array $activities): array
    {
        $highlights = [];

        foreach ($activities as $activity) {
            $metadata = $activity->getMetadata();
            
            // Level up highlight
            if ($activity->getActivityType() === 'level_up' && isset($metadata['level'])) {
                $highlights[] = [
                    'icon' => 'bi-arrow-up-circle',
                    'color' => 'success',
                    'text' => "Leveled up to Level {$metadata['level']}!",
                    'date' => $activity->getCreatedAt(),
                ];
            }

            // Badge earned highlight
            if ($activity->getActivityType() === 'badge_earned' && isset($metadata['badge_name'])) {
                $highlights[] = [
                    'icon' => 'bi-award',
                    'color' => 'warning',
                    'text' => "Earned '{$metadata['badge_name']}' badge",
                    'date' => $activity->getCreatedAt(),
                ];
            }

            // High XP gain
            if (isset($metadata['xp']) && $metadata['xp'] >= 100) {
                $highlights[] = [
                    'icon' => 'bi-star-fill',
                    'color' => 'success',
                    'text' => "Earned {$metadata['xp']} XP in one activity!",
                    'date' => $activity->getCreatedAt(),
                ];
            }
        }

        return array_slice($highlights, 0, 3); // Return top 3 highlights
    }

    /**
     * Calculate activity streak
     */
    private function calculateStreak(array $dailyActivity): int
    {
        if (empty($dailyActivity)) {
            return 0;
        }

        $streak = 0;
        $currentDate = new \DateTime();
        
        for ($i = 0; $i < 7; $i++) {
            $dateStr = $currentDate->format('Y-m-d');
            if (isset($dailyActivity[$dateStr])) {
                $streak++;
            } else {
                break;
            }
            $currentDate->modify('-1 day');
        }

        return $streak;
    }

    /**
     * Get most active day
     */
    private function getMostActiveDay(array $dailyActivity): ?array
    {
        if (empty($dailyActivity)) {
            return null;
        }

        $maxDay = array_keys($dailyActivity, max($dailyActivity))[0];
        return [
            'date' => $maxDay,
            'count' => $dailyActivity[$maxDay],
        ];
    }

    /**
     * Generate natural language summary
     */
    private function generateNaturalLanguageSummary(array $activityTypes, array $dailyActivity, int $streak): string
    {
        $totalActivities = array_sum($activityTypes);
        $activeDays = count($dailyActivity);
        
        $summary = "In the past 7 days, you've completed {$totalActivities} activities across {$activeDays} days. ";

        if ($streak > 0) {
            $summary .= "You're on a {$streak}-day streak! ";
        }

        if (!empty($activityTypes)) {
            $topActivity = array_key_first($activityTypes);
            $summary .= "Your most frequent activity was " . $this->formatActivityType($topActivity) . ". ";
        }

        if ($activeDays >= 5) {
            $summary .= "Great consistency this week!";
        } elseif ($activeDays >= 3) {
            $summary .= "Good progress, keep it up!";
        } else {
            $summary .= "Try to be more active to maximize your learning!";
        }

        return $summary;
    }

    /**
     * Format activity type for display
     */
    private function formatActivityType(string $type): string
    {
        return ucwords(str_replace('_', ' ', $type));
    }
}
