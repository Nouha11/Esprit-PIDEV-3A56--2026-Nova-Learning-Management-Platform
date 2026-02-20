<?php

namespace App\Repository\Gamification;

use App\Entity\Gamification\Game;
use App\Entity\Gamification\GameRating;
use App\Entity\users\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameRating>
 */
class GameRatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameRating::class);
    }

    /**
     * Get average rating and count for a game
     */
    public function getGameRatingStats(Game $game): array
    {
        $result = $this->createQueryBuilder('gr')
            ->select('AVG(gr.rating) as averageRating', 'COUNT(gr.id) as totalRatings')
            ->where('gr.game = :game')
            ->setParameter('game', $game)
            ->getQuery()
            ->getSingleResult();

        return [
            'average' => $result['averageRating'] ? round((float)$result['averageRating'], 1) : 0,
            'count' => (int)$result['totalRatings']
        ];
    }

    /**
     * Get user's rating for a specific game
     */
    public function getUserRating(Game $game, User $user): ?GameRating
    {
        return $this->findOneBy([
            'game' => $game,
            'user' => $user
        ]);
    }

    /**
     * Get average ratings for multiple games (for game list)
     */
    public function getAverageRatingsForGames(array $gameIds): array
    {
        if (empty($gameIds)) {
            return [];
        }

        $results = $this->createQueryBuilder('gr')
            ->select('IDENTITY(gr.game) as gameId', 'AVG(gr.rating) as averageRating', 'COUNT(gr.id) as totalRatings')
            ->where('gr.game IN (:gameIds)')
            ->setParameter('gameIds', $gameIds)
            ->groupBy('gr.game')
            ->getQuery()
            ->getResult();

        $ratings = [];
        foreach ($results as $result) {
            $ratings[$result['gameId']] = [
                'average' => round((float)$result['averageRating'], 1),
                'count' => (int)$result['totalRatings']
            ];
        }

        return $ratings;
    }

    /**
     * Save a rating (create or update)
     */
    public function saveRating(GameRating $rating): void
    {
        $this->getEntityManager()->persist($rating);
        $this->getEntityManager()->flush();
    }
}
