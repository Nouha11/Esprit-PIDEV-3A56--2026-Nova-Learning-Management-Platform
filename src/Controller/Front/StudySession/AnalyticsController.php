<?php

namespace App\Controller\Front\StudySession;

use App\Service\StudySession\AnalyticsService;
use App\Service\StudySession\StreakService;
use App\Service\StudySession\CacheService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/analytics')]
#[IsGranted('ROLE_STUDENT')]
class AnalyticsController extends AbstractController
{
    public function __construct(
        private AnalyticsService $analyticsService,
        private StreakService $streakService,
        private CacheService $cacheService
    ) {}

    #[Route('/', name: 'analytics_dashboard', methods: ['GET'])]
    public function dashboard(Request $request): Response
    {
        $user = $this->getUser();
        
        // Get time range filter from query parameter (default: week)
        $timeRange = $request->query->get('range', 'week');
        
        // Calculate date range based on filter
        $end = new \DateTimeImmutable('now');
        $start = match($timeRange) {
            'month' => $end->modify('-1 month'),
            'year' => $end->modify('-1 year'),
            default => $end->modify('-1 week'), // week is default
        };

        // Cache key for analytics data
        $cacheKey = sprintf('analytics_%s_%s_%s', $user->getId(), $timeRange, $end->format('Y-m-d'));

        // Get analytics data with caching
        $analyticsData = $this->cacheService->cacheAnalytics($cacheKey, function() use ($user, $start, $end) {
            return [
                'total_study_time' => $this->analyticsService->getTotalStudyTime($user, $start, $end),
                'total_xp' => $this->analyticsService->getTotalXP($user, $start, $end),
                'completion_rate' => $this->analyticsService->getCompletionRate($user, $start, $end),
                'study_time_by_course' => $this->analyticsService->getStudyTimeByCourse($user, $start, $end),
                'xp_over_time' => $this->analyticsService->getXPOverTime($user, $start, $end),
            ];
        });

        // Get current streak (not cached as it changes frequently)
        $currentStreak = $this->streakService->getCurrentStreak($user);
        $longestStreak = $this->streakService->getLongestStreak($user);

        // Prepare Chart.js data for study time by course
        $courseLabels = [];
        $courseDurations = [];
        foreach ($analyticsData['study_time_by_course'] as $courseData) {
            $courseLabels[] = $courseData['course_name'] ?? 'Unknown';
            $courseDurations[] = $courseData['total_duration'];
        }

        // Prepare Chart.js data for XP over time
        $xpDates = [];
        $xpValues = [];
        foreach ($analyticsData['xp_over_time'] as $xpData) {
            $xpDates[] = $xpData['date'];
            $xpValues[] = $xpData['total_xp'];
        }

        // Calculate session count for average calculation
        $sessionCount = count($analyticsData['xp_over_time']);
        $hasCourseData = count($courseLabels) > 0;
        $hasXpData = count($xpDates) > 0;

        return $this->render('front/study_session/analytics.html.twig', [
            'total_study_time' => $analyticsData['total_study_time'],
            'total_xp' => $analyticsData['total_xp'],
            'completion_rate' => $analyticsData['completion_rate'],
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
            'time_range' => $timeRange,
            'start_date' => $start,
            'end_date' => $end,
            'session_count' => $sessionCount,
            'has_course_data' => $hasCourseData,
            'has_xp_data' => $hasXpData,
            // Chart.js data
            'course_labels' => json_encode($courseLabels),
            'course_durations' => json_encode($courseDurations),
            'xp_dates' => json_encode($xpDates),
            'xp_values' => json_encode($xpValues),
        ]);
    }
}
