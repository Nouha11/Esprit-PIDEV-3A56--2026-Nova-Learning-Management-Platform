<?php

namespace App\Controller\Admin;

use App\Repository\Gamification\GameRepository;
use App\Repository\Gamification\RewardRepository;
use App\Repository\StudentProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/statistics')]
#[IsGranted('ROLE_ADMIN')]
class AdminStatisticsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private StudentProfileRepository $studentRepository,
        private GameRepository $gameRepository,
        private RewardRepository $rewardRepository
    ) {
    }

    /**
     * Display statistics dashboard
     */
    #[Route('', name: 'admin_statistics_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('admin/statistics/dashboard.html.twig');
    }

    /**
     * Get top users by XP (JSON endpoint)
     */
    #[Route('/api/top-users-xp', name: 'admin_statistics_top_users_xp', methods: ['GET'])]
    public function getTopUsersByXP(): JsonResponse
    {
        try {
            $topUsers = $this->studentRepository->createQueryBuilder('s')
                ->select('s.firstName', 's.lastName', 's.totalXP')
                ->orderBy('s.totalXP', 'DESC')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();

            $data = [
                ['User', 'Total XP']
            ];

            if (empty($topUsers)) {
                // Return sample data if no users exist
                $data[] = ['No Data', 0];
            } else {
                foreach ($topUsers as $user) {
                    $fullName = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
                    if (empty($fullName)) {
                        $fullName = 'Unknown User';
                    }
                    $data[] = [
                        $fullName,
                        (int) ($user['totalXP'] ?? 0)
                    ];
                }
            }

            return new JsonResponse($data);
        } catch (\Exception $e) {
            // Return error data for debugging
            return new JsonResponse([
                ['User', 'Total XP'],
                ['Error: ' . $e->getMessage(), 0]
            ]);
        }
    }

    /**
     * Get game type distribution (JSON endpoint)
     */
    #[Route('/api/game-types', name: 'admin_statistics_game_types', methods: ['GET'])]
    public function getGameTypeDistribution(): JsonResponse
    {
        try {
            $gameTypes = $this->gameRepository->createQueryBuilder('g')
                ->select('g.type', 'COUNT(g.id) as count')
                ->where('g.isActive = :active')
                ->setParameter('active', true)
                ->groupBy('g.type')
                ->getQuery()
                ->getResult();

            $data = [
                ['Game Type', 'Count']
            ];

            if (empty($gameTypes)) {
                $data[] = ['No Games', 1];
            } else {
                foreach ($gameTypes as $type) {
                    $data[] = [
                        $type['type'],
                        (int) $type['count']
                    ];
                }
            }

            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse([
                ['Game Type', 'Count'],
                ['Error', 1]
            ]);
        }
    }

    /**
     * Get rewards by type (JSON endpoint)
     */
    #[Route('/api/rewards-by-type', name: 'admin_statistics_rewards_by_type', methods: ['GET'])]
    public function getRewardsByType(): JsonResponse
    {
        try {
            $rewardTypes = $this->rewardRepository->createQueryBuilder('r')
                ->select('r.type', 'COUNT(r.id) as count')
                ->where('r.isActive = :active')
                ->setParameter('active', true)
                ->groupBy('r.type')
                ->getQuery()
                ->getResult();

            $data = [
                ['Reward Type', 'Count']
            ];

            if (empty($rewardTypes)) {
                $data[] = ['No Rewards', 1];
            } else {
                foreach ($rewardTypes as $type) {
                    $typeName = str_replace('_', ' ', $type['type']);
                    $data[] = [
                        $typeName,
                        (int) $type['count']
                    ];
                }
            }

            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse([
                ['Reward Type', 'Count'],
                ['Error', 1]
            ]);
        }
    }

    /**
     * Get overall statistics summary (JSON endpoint)
     */
    #[Route('/api/summary', name: 'admin_statistics_summary', methods: ['GET'])]
    public function getSummary(): JsonResponse
    {
        try {
            $totalStudents = $this->studentRepository->count([]);
            $totalGames = $this->gameRepository->count(['isActive' => true]);
            $totalRewards = $this->rewardRepository->count(['isActive' => true]);
            
            // Total XP across all students
            $totalXP = $this->studentRepository->createQueryBuilder('s')
                ->select('SUM(s.totalXP)')
                ->getQuery()
                ->getSingleScalarResult() ?? 0;

            // Total tokens across all students
            $totalTokens = $this->studentRepository->createQueryBuilder('s')
                ->select('SUM(s.totalTokens)')
                ->getQuery()
                ->getSingleScalarResult() ?? 0;

            return new JsonResponse([
                'totalStudents' => $totalStudents,
                'totalGames' => $totalGames,
                'totalRewards' => $totalRewards,
                'totalXP' => (int) $totalXP,
                'totalTokens' => (int) $totalTokens,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'totalStudents' => 0,
                'totalGames' => 0,
                'totalRewards' => 0,
                'totalXP' => 0,
                'totalTokens' => 0,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get most favorite games (JSON endpoint)
     */
    #[Route('/api/most-favorite-games', name: 'admin_statistics_most_favorite_games', methods: ['GET'])]
    public function getMostFavoriteGames(): JsonResponse
    {
        try {
            // Query to get games with their favorite count
            $connection = $this->em->getConnection();
            $sql = "
                SELECT g.name, COUNT(ufg.user_id) as favorites_count
                FROM game g
                LEFT JOIN user_favorite_games ufg ON g.id = ufg.game_id
                WHERE g.is_active = 1
                GROUP BY g.id, g.name
                HAVING favorites_count > 0
                ORDER BY favorites_count DESC
                LIMIT 10
            ";
            
            $result = $connection->executeQuery($sql)->fetchAllAssociative();

            $data = [
                ['Game', 'Favorites']
            ];

            if (empty($result)) {
                $data[] = ['No Favorites Yet', 0];
            } else {
                foreach ($result as $row) {
                    $data[] = [
                        $row['name'],
                        (int) $row['favorites_count']
                    ];
                }
            }

            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse([
                ['Game', 'Favorites'],
                ['Error: ' . $e->getMessage(), 0]
            ]);
        }
    }
}
