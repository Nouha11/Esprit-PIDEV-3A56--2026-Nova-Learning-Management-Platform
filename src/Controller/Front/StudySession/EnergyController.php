<?php

namespace App\Controller\Front\StudySession;

use App\Service\StudySession\AnalyticsService;
use App\Repository\StudySession\StudySessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/study-session/energy')]
#[IsGranted('ROLE_STUDENT')]
class EnergyController extends AbstractController
{
    public function __construct(
        private AnalyticsService $analyticsService,
        private StudySessionRepository $studySessionRepository
    ) {}

    /**
     * Display energy patterns analytics
     */
    #[Route('/analytics', name: 'energy_analytics', methods: ['GET'])]
    public function analytics(): Response
    {
        $user = $this->getUser();
        
        // Check if user has at least 5 sessions with energy data
        $sessionsWithEnergy = $this->studySessionRepository->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.user = :user')
            ->andWhere('s.energyLevel IS NOT NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        $insufficientData = $sessionsWithEnergy < 5;
        
        $energyPatterns = [];
        if (!$insufficientData) {
            try {
                $energyPatterns = $this->analyticsService->getEnergyPatterns($user);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to load energy patterns: ' . $e->getMessage());
            }
        }

        return $this->render('front/study_session/energy_analytics.html.twig', [
            'energy_patterns' => $energyPatterns,
            'insufficient_data' => $insufficientData,
            'session_count' => $sessionsWithEnergy,
        ]);
    }

    /**
     * Display optimal study time recommendations based on energy patterns
     */
    #[Route('/recommendations', name: 'energy_recommendations', methods: ['GET'])]
    public function recommendations(): Response
    {
        $user = $this->getUser();
        
        // Check if user has at least 5 sessions with energy data
        $sessionsWithEnergy = $this->studySessionRepository->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.user = :user')
            ->andWhere('s.energyLevel IS NOT NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        $insufficientData = $sessionsWithEnergy < 5;
        
        $recommendations = [];
        $highEnergyPeriods = [];
        $lowEnergyPeriods = [];
        
        if (!$insufficientData) {
            try {
                $energyPatterns = $this->analyticsService->getEnergyPatterns($user);
                
                // Analyze patterns to generate recommendations
                foreach ($energyPatterns as $pattern) {
                    $hour = $pattern['hour'];
                    $energyLevel = $pattern['avg_energy'];
                    
                    if ($energyLevel === 'high') {
                        $highEnergyPeriods[] = $hour;
                    } elseif ($energyLevel === 'low') {
                        $lowEnergyPeriods[] = $hour;
                    }
                }
                
                // Generate recommendations based on patterns
                if (!empty($highEnergyPeriods)) {
                    $recommendations[] = [
                        'type' => 'optimal_time',
                        'message' => $this->formatHighEnergyRecommendation($highEnergyPeriods),
                        'icon' => 'success',
                    ];
                }
                
                if (!empty($lowEnergyPeriods)) {
                    $recommendations[] = [
                        'type' => 'avoid_time',
                        'message' => $this->formatLowEnergyRecommendation($lowEnergyPeriods),
                        'icon' => 'warning',
                    ];
                }
                
                // Add general recommendations
                if (empty($recommendations)) {
                    $recommendations[] = [
                        'type' => 'general',
                        'message' => 'Your energy levels are fairly consistent throughout the day. Continue tracking to identify more specific patterns.',
                        'icon' => 'info',
                    ];
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to generate recommendations: ' . $e->getMessage());
            }
        }

        return $this->render('front/study_session/energy_recommendations.html.twig', [
            'recommendations' => $recommendations,
            'high_energy_periods' => $highEnergyPeriods,
            'low_energy_periods' => $lowEnergyPeriods,
            'insufficient_data' => $insufficientData,
            'session_count' => $sessionsWithEnergy,
        ]);
    }

    /**
     * Format high energy period recommendation message
     */
    private function formatHighEnergyRecommendation(array $hours): string
    {
        sort($hours);
        $timeRanges = $this->formatHourRanges($hours);
        
        if (count($timeRanges) === 1) {
            return sprintf(
                'Your energy levels are typically highest between %s. Schedule your most challenging study sessions during this time.',
                $timeRanges[0]
            );
        }
        
        $lastRange = array_pop($timeRanges);
        return sprintf(
            'Your energy levels are typically highest between %s and %s. Schedule your most challenging study sessions during these times.',
            implode(', ', $timeRanges),
            $lastRange
        );
    }

    /**
     * Format low energy period recommendation message
     */
    private function formatLowEnergyRecommendation(array $hours): string
    {
        sort($hours);
        $timeRanges = $this->formatHourRanges($hours);
        
        if (count($timeRanges) === 1) {
            return sprintf(
                'Your energy levels tend to be lower between %s. Consider taking breaks or scheduling lighter tasks during this time.',
                $timeRanges[0]
            );
        }
        
        $lastRange = array_pop($timeRanges);
        return sprintf(
            'Your energy levels tend to be lower between %s and %s. Consider taking breaks or scheduling lighter tasks during these times.',
            implode(', ', $timeRanges),
            $lastRange
        );
    }

    /**
     * Format hours into readable time ranges
     */
    private function formatHourRanges(array $hours): array
    {
        if (empty($hours)) {
            return [];
        }
        
        sort($hours);
        $ranges = [];
        $rangeStart = $hours[0];
        $rangeEnd = $hours[0];
        
        for ($i = 1; $i < count($hours); $i++) {
            if ($hours[$i] === $rangeEnd + 1) {
                // Continue the range
                $rangeEnd = $hours[$i];
            } else {
                // End current range and start new one
                $ranges[] = $this->formatTimeRange($rangeStart, $rangeEnd);
                $rangeStart = $hours[$i];
                $rangeEnd = $hours[$i];
            }
        }
        
        // Add the last range
        $ranges[] = $this->formatTimeRange($rangeStart, $rangeEnd);
        
        return $ranges;
    }

    /**
     * Format a time range from start hour to end hour
     */
    private function formatTimeRange(int $startHour, int $endHour): string
    {
        $formatHour = function(int $hour): string {
            if ($hour === 0) {
                return '12:00 AM';
            } elseif ($hour < 12) {
                return sprintf('%d:00 AM', $hour);
            } elseif ($hour === 12) {
                return '12:00 PM';
            } else {
                return sprintf('%d:00 PM', $hour - 12);
            }
        };
        
        if ($startHour === $endHour) {
            return $formatHour($startHour);
        }
        
        return sprintf('%s - %s', $formatHour($startHour), $formatHour($endHour + 1));
    }
}
