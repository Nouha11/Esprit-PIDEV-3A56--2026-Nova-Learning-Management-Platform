<?php

namespace App\Controller\Front\Game;

use App\Entity\Gamification\Game;
use App\Entity\Gamification\GameRating;
use App\Repository\Gamification\GameRatingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/games')]
class GameRatingController extends AbstractController
{
    public function __construct(
        private GameRatingRepository $ratingRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Submit or update a game rating (Ajax endpoint)
     */
    #[Route('/{id}/rate', name: 'front_game_rate', methods: ['POST'])]
    public function rateGame(Game $game, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'You must be logged in to rate games'
            ], 401);
        }

        try {
            $data = json_decode($request->getContent(), true);
            $ratingValue = $data['rating'] ?? null;

            // Validate rating
            if ($ratingValue === null || $ratingValue < 1 || $ratingValue > 5) {
                return $this->json([
                    'success' => false,
                    'message' => 'Rating must be between 1 and 5 stars'
                ], 400);
            }

            // Check if user already rated this game
            $existingRating = $this->ratingRepository->getUserRating($game, $user);

            if ($existingRating) {
                // Update existing rating
                $existingRating->setRating($ratingValue);
                $message = 'Your rating has been updated!';
                $action = 'updated';
            } else {
                // Create new rating
                $existingRating = new GameRating();
                $existingRating->setGame($game);
                $existingRating->setUser($user);
                $existingRating->setRating($ratingValue);
                $message = 'Thank you for rating this game!';
                $action = 'created';
            }

            $this->ratingRepository->saveRating($existingRating);

            // Get updated stats
            $stats = $this->ratingRepository->getGameRatingStats($game);

            return $this->json([
                'success' => true,
                'message' => $message,
                'action' => $action,
                'userRating' => $ratingValue,
                'averageRating' => $stats['average'],
                'totalRatings' => $stats['count']
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while saving your rating: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rating stats for a game (Ajax endpoint)
     */
    #[Route('/{id}/rating-stats', name: 'front_game_rating_stats', methods: ['GET'])]
    public function getRatingStats(Game $game): JsonResponse
    {
        $user = $this->getUser();
        $stats = $this->ratingRepository->getGameRatingStats($game);
        
        $userRating = null;
        if ($user) {
            $rating = $this->ratingRepository->getUserRating($game, $user);
            $userRating = $rating ? $rating->getRating() : null;
        }

        return $this->json([
            'success' => true,
            'averageRating' => $stats['average'],
            'totalRatings' => $stats['count'],
            'userRating' => $userRating
        ]);
    }
}
