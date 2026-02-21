<?php

namespace App\Controller\Admin\Game;

use App\Repository\Gamification\GameRepository;
use App\Repository\Gamification\RewardRepository;
use App\Repository\StudentProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/games/statistics')]
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
     * Display interactive games dashboard with filters
     */
    #[Route('/games-dashboard', name: 'admin_statistics_games_dashboard_page', methods: ['GET'])]
    public function gamesDashboard(): Response
    {
        return $this->render('admin/statistics/games_dashboard.html.twig');
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

            // Total ratings
            $connection = $this->em->getConnection();
            $totalRatings = $connection->executeQuery('SELECT COUNT(*) FROM game_rating')->fetchOne();

            // Average rating
            $avgRating = $connection->executeQuery('SELECT AVG(rating) FROM game_rating')->fetchOne();

            return new JsonResponse([
                'totalStudents' => $totalStudents,
                'totalGames' => $totalGames,
                'totalRewards' => $totalRewards,
                'totalXP' => (int) $totalXP,
                'totalTokens' => (int) $totalTokens,
                'totalRatings' => (int) $totalRatings,
                'avgRating' => $avgRating ? round((float) $avgRating, 1) : 0,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'totalStudents' => 0,
                'totalGames' => 0,
                'totalRewards' => 0,
                'totalXP' => 0,
                'totalTokens' => 0,
                'totalRatings' => 0,
                'avgRating' => 0,
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

    /**
     * Get top rated games (JSON endpoint)
     */
    #[Route('/api/top-rated-games', name: 'admin_statistics_top_rated_games', methods: ['GET'])]
    public function getTopRatedGames(): JsonResponse
    {
        try {
            $connection = $this->em->getConnection();
            $sql = "
                SELECT g.name, 
                        ROUND(AVG(gr.rating), 1) as avg_rating,
                        COUNT(gr.id) as rating_count
                FROM game g
                INNER JOIN game_rating gr ON g.id = gr.game_id
                WHERE g.is_active = 1
                GROUP BY g.id, g.name
                HAVING rating_count >= 1
                ORDER BY avg_rating DESC, rating_count DESC
                LIMIT 10
            ";
            
            $result = $connection->executeQuery($sql)->fetchAllAssociative();

            $data = [
                ['Game', 'Average Rating']
            ];

            if (empty($result)) {
                $data[] = ['No Ratings Yet', 0];
            } else {
                foreach ($result as $row) {
                    $data[] = [
                        $row['name'] . ' (' . $row['rating_count'] . ' ratings)',
                        (float) $row['avg_rating']
                    ];
                }
            }

            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse([
                ['Game', 'Average Rating'],
                ['Error: ' . $e->getMessage(), 0]
            ]);
        }
    }

    /**
     * Get rating distribution (JSON endpoint)
     */
    #[Route('/api/rating-distribution', name: 'admin_statistics_rating_distribution', methods: ['GET'])]
    public function getRatingDistribution(): JsonResponse
    {
        try {
            $connection = $this->em->getConnection();
            $sql = "
                SELECT rating, COUNT(*) as count
                FROM game_rating
                GROUP BY rating
                ORDER BY rating ASC
            ";
            
            $result = $connection->executeQuery($sql)->fetchAllAssociative();

            $data = [
                ['Rating', 'Count']
            ];

            if (empty($result)) {
                // Show empty distribution
                for ($i = 1; $i <= 5; $i++) {
                    $data[] = [$i . ' Star' . ($i > 1 ? 's' : ''), 0];
                }
            } else {
                // Create array with all ratings (1-5)
                $distribution = array_fill(1, 5, 0);
                
                foreach ($result as $row) {
                    $distribution[(int)$row['rating']] = (int)$row['count'];
                }
                
                foreach ($distribution as $rating => $count) {
                    $data[] = [
                        $rating . ' Star' . ($rating > 1 ? 's' : ''),
                        $count
                    ];
                }
            }

            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse([
                ['Rating', 'Count'],
                ['Error', 0]
            ]);
        }
    }

    /**
     * Get games data for dashboard with filters (JSON endpoint)
     */
    #[Route('/api/games-dashboard-data', name: 'admin_statistics_games_dashboard', methods: ['GET'])]
    public function getGamesDashboardData(): JsonResponse
    {
        try {
            $games = $this->gameRepository->createQueryBuilder('g')
                ->select('g.id', 'g.name', 'g.type', 'g.difficulty', 'g.category', 'g.tokenCost', 'g.rewardTokens', 'g.rewardXP')
                ->where('g.isActive = :active')
                ->setParameter('active', true)
                ->getQuery()
                ->getResult();

            // Get favorites count for each game
            $connection = $this->em->getConnection();
            $favoritesData = [];
            $ratingsData = [];
            
            foreach ($games as $game) {
                // Get favorites count
                $favSql = "SELECT COUNT(*) as count FROM user_favorite_games WHERE game_id = :gameId";
                $favCount = $connection->executeQuery($favSql, ['gameId' => $game['id']])->fetchOne();
                $favoritesData[$game['id']] = (int)$favCount;
                
                // Get average rating
                $ratingSql = "SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM game_rating WHERE game_id = :gameId";
                $ratingResult = $connection->executeQuery($ratingSql, ['gameId' => $game['id']])->fetchAssociative();
                $ratingsData[$game['id']] = [
                    'avg' => $ratingResult['avg_rating'] ? round((float)$ratingResult['avg_rating'], 2) : 0,
                    'count' => (int)$ratingResult['count']
                ];
            }

            // Format data for Google Charts DataTable
            $data = [
                ['Game Name', 'Type', 'Difficulty', 'Category', 'Cost', 'Reward Tokens', 'Reward XP', 'Favorites', 'Avg Rating', 'Rating Count']
            ];

            foreach ($games as $game) {
                $data[] = [
                    $game['name'],
                    $game['type'],
                    $game['difficulty'],
                    $game['category'],
                    (int)$game['tokenCost'],
                    (int)$game['rewardTokens'],
                    (int)$game['rewardXP'],
                    $favoritesData[$game['id']],
                    $ratingsData[$game['id']]['avg'],
                    $ratingsData[$game['id']]['count']
                ];
            }

            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse([
                ['Game Name', 'Type', 'Difficulty', 'Category', 'Cost', 'Reward Tokens', 'Reward XP', 'Favorites', 'Avg Rating', 'Rating Count'],
                ['Error loading data', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0]
            ]);
        }
    }
}
