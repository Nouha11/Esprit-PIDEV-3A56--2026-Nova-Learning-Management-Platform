<?php

namespace App\Service;

use App\Entity\users\User;
use App\Repository\UserActivityRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class AIActivitySummaryService
{
    private const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

    public function __construct(
        private UserActivityRepository $activityRepository,
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $geminiApiKey
    ) {}

    /**
     * Generate AI-powered activity summary
     */
    public function generateSummary(User $user): array
    {
        // Get activities from last 7 days
        $startDate = new \DateTime('-7 days');
        $endDate = new \DateTime();
        $activities = $this->activityRepository->findByUserAndDateRange($user, $startDate, $endDate, 100);

        if (empty($activities)) {
            return [
                'summary' => 'No recent activity in the last 7 days. Start exploring to track your progress!',
                'insights' => [],
                'recommendations' => ['Start exploring the platform to track your progress!'],
                'stats' => [
                    'total_activities' => 0,
                    'most_active_day' => null,
                    'streak' => 0,
                ],
                'highlights' => [],
                'charts' => [
                    'activity_types' => [],
                    'daily_activity' => [],
                ],
                'ai_generated' => false,
            ];
        }

        // Prepare activity data for AI
        $activityData = $this->prepareActivityData($activities);
        
        // Generate AI summary
        try {
            $aiSummary = $this->generateAISummary($activityData, $user);
        } catch (\Exception $e) {
            $this->logger->error('AI Summary generation failed: ' . $e->getMessage());
            // Fallback to algorithmic summary
            return $this->generateAlgorithmicSummary($activities, $user);
        }

        // Analyze activities for stats
        $activityTypes = $this->groupByType($activities);
        $dailyActivity = $this->groupByDay($activities);
        $highlights = $this->extractHighlights($activities);
        $streak = $this->calculateStreak($dailyActivity);

        return [
            'summary' => $aiSummary['summary'],
            'insights' => $aiSummary['insights'],
            'recommendations' => $aiSummary['recommendations'],
            'stats' => [
                'total_activities' => count($activities),
                'most_active_day' => $this->getMostActiveDay($dailyActivity),
                'streak' => $streak,
            ],
            'highlights' => $highlights,
            'charts' => [
                'activity_types' => $activityTypes,
                'daily_activity' => $dailyActivity,
            ],
            'ai_generated' => true,
        ];
    }

    /**
     * Prepare activity data for AI processing
     */
    private function prepareActivityData(array $activities): array
    {
        $data = [
            'total_count' => count($activities),
            'activities_by_type' => [],
            'activities_by_day' => [],
            'recent_activities' => [],
        ];

        foreach ($activities as $activity) {
            $type = $activity->getActivityType();
            $day = $activity->getCreatedAt()->format('l'); // Day name
            
            // Count by type
            if (!isset($data['activities_by_type'][$type])) {
                $data['activities_by_type'][$type] = 0;
            }
            $data['activities_by_type'][$type]++;
            
            // Count by day
            if (!isset($data['activities_by_day'][$day])) {
                $data['activities_by_day'][$day] = 0;
            }
            $data['activities_by_day'][$day]++;
        }

        // Get last 5 activities for context
        $recentActivities = array_slice($activities, 0, 5);
        foreach ($recentActivities as $activity) {
            $data['recent_activities'][] = [
                'type' => $activity->getActivityType(),
                'description' => $activity->getDescription(),
                'metadata' => $activity->getMetadata(),
            ];
        }

        return $data;
    }

    /**
     * Generate AI summary using Gemini API
     */
    private function generateAISummary(array $activityData, User $user): array
    {
        $prompt = $this->buildPrompt($activityData, $user);

        try {
            $response = $this->httpClient->request('POST', self::GEMINI_API_URL, [
                'query' => ['key' => $this->geminiApiKey],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 500,
                    ]
                ],
                'timeout' => 10,
            ]);

            $data = $response->toArray();
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $aiResponse = $data['candidates'][0]['content']['parts'][0]['text'];
                return $this->parseAIResponse($aiResponse);
            }
        } catch (\Exception $e) {
            $this->logger->error('Gemini API error: ' . $e->getMessage());
            throw $e;
        }

        throw new \Exception('Invalid AI response');
    }

    /**
     * Build prompt for AI
     */
    private function buildPrompt(array $activityData, User $user): string
    {
        $role = $user->getRole();
        $username = $user->getUsername();
        
        $activitiesByType = json_encode($activityData['activities_by_type'], JSON_PRETTY_PRINT);
        $activitiesByDay = json_encode($activityData['activities_by_day'], JSON_PRETTY_PRINT);
        
        return <<<PROMPT
You are an AI learning coach analyzing a student's activity on an educational platform.

User: {$username} (Role: {$role})
Time Period: Last 7 days
Total Activities: {$activityData['total_count']}

Activities by Type:
{$activitiesByType}

Activities by Day:
{$activitiesByDay}

Generate a personalized activity summary in JSON format with the following structure:
{
    "summary": "A friendly, encouraging 2-3 sentence summary of their week",
    "insights": [
        {"icon": "bi-icon-name", "color": "primary|success|warning|info|danger", "text": "Insight text"},
        // 2-4 insights about patterns, achievements, or areas of focus
    ],
    "recommendations": [
        {"icon": "bi-icon-name", "color": "color", "text": "Recommendation text", "action": "Action button text", "link": "/path"},
        // 2-3 personalized recommendations
    ]
}

Guidelines:
- Be encouraging and positive
- Identify patterns and trends
- Provide actionable recommendations
- Use appropriate Bootstrap icons (bi-star, bi-trophy, bi-lightning-charge, bi-controller, bi-book, etc.)
- Keep insights concise and meaningful
- Recommend activities they haven't tried or should do more of

Return ONLY valid JSON, no additional text.
PROMPT;
    }

    /**
     * Parse AI response
     */
    private function parseAIResponse(string $response): array
    {
        // Extract JSON from response (AI might add markdown code blocks)
        $response = trim($response);
        $response = preg_replace('/```json\s*/', '', $response);
        $response = preg_replace('/```\s*$/', '', $response);
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse AI response JSON');
        }

        return [
            'summary' => $data['summary'] ?? 'Great progress this week!',
            'insights' => $data['insights'] ?? [],
            'recommendations' => $data['recommendations'] ?? [],
        ];
    }

    /**
     * Fallback algorithmic summary (when AI fails)
     */
    private function generateAlgorithmicSummary(array $activities, User $user): array
    {
        $activityTypes = $this->groupByType($activities);
        $dailyActivity = $this->groupByDay($activities);
        $streak = $this->calculateStreak($dailyActivity);
        
        $totalActivities = count($activities);
        $activeDays = count($dailyActivity);
        
        $summary = "In the past 7 days, you've completed {$totalActivities} activities across {$activeDays} days. ";
        if ($streak > 0) {
            $summary .= "You're on a {$streak}-day streak! Keep up the great work!";
        }

        $insights = [];
        if (!empty($activityTypes)) {
            $topActivity = array_key_first($activityTypes);
            $insights[] = [
                'icon' => 'bi-star',
                'color' => 'primary',
                'text' => "Most frequent: " . ucwords(str_replace('_', ' ', $topActivity)),
            ];
        }

        $recommendations = [
            ['icon' => 'bi-lightning-charge', 'color' => 'purple', 'text' => 'Try taking more quizzes', 'action' => 'Take Quiz', 'link' => '/game/quiz'],
            ['icon' => 'bi-controller', 'color' => 'primary', 'text' => 'Play educational games', 'action' => 'Play Games', 'link' => '/games'],
        ];

        return [
            'summary' => $summary,
            'insights' => $insights,
            'recommendations' => $recommendations,
            'stats' => [
                'total_activities' => $totalActivities,
                'most_active_day' => $this->getMostActiveDay($dailyActivity),
                'streak' => $streak,
            ],
            'highlights' => $this->extractHighlights($activities),
            'charts' => [
                'activity_types' => $activityTypes,
                'daily_activity' => $dailyActivity,
            ],
            'ai_generated' => false,
        ];
    }

    // Helper methods (same as ActivitySummaryService)
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

    private function extractHighlights(array $activities): array
    {
        $highlights = [];

        foreach ($activities as $activity) {
            $metadata = $activity->getMetadata();
            
            if ($activity->getActivityType() === 'level_up' && isset($metadata['level'])) {
                $highlights[] = [
                    'icon' => 'bi-arrow-up-circle',
                    'color' => 'success',
                    'text' => "Leveled up to Level {$metadata['level']}!",
                    'date' => $activity->getCreatedAt(),
                ];
            }

            if ($activity->getActivityType() === 'badge_earned' && isset($metadata['badge_name'])) {
                $highlights[] = [
                    'icon' => 'bi-award',
                    'color' => 'warning',
                    'text' => "Earned '{$metadata['badge_name']}' badge",
                    'date' => $activity->getCreatedAt(),
                ];
            }

            if (isset($metadata['xp']) && $metadata['xp'] >= 100) {
                $highlights[] = [
                    'icon' => 'bi-star-fill',
                    'color' => 'success',
                    'text' => "Earned {$metadata['xp']} XP in one activity!",
                    'date' => $activity->getCreatedAt(),
                ];
            }
        }

        return array_slice($highlights, 0, 3);
    }
}
