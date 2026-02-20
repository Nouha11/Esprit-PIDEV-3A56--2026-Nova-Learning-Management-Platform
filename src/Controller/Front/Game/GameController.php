<?php
namespace App\Controller\Front\Game;

use App\Entity\Gamification\Game;
use App\Repository\Gamification\GameRepository as GamificationGameRepository;
use App\Repository\Gamification\GameRatingRepository;
use App\Service\game\GameService;
use App\Service\game\TokenService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/games')]
class GameController extends AbstractController
{
    public function __construct(
        private GameService $gameService,
        private TokenService $tokenService,
        private GamificationGameRepository $gameRepository,
        private GameRatingRepository $ratingRepository,
        private PaginatorInterface $paginator,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
    * Browse all available games with pagination and Ajax filters
    */
    #[Route('', name: 'front_game_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $type = $request->query->get('type', '');
        $difficulty = $request->query->get('difficulty', '');
        $freeOnly = $request->query->get('free_only', false);
        $isAjax = $request->isXmlHttpRequest();

        $queryBuilder = $this->gameRepository->createQueryBuilder('g')
            ->where('g.isActive = :active')
            ->setParameter('active', true);

        // Apply search filter
        if (!empty($search)) {
            $queryBuilder
                ->andWhere('g.name LIKE :search OR g.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Apply type filter
        if (!empty($type)) {
            $queryBuilder
                ->andWhere('g.type = :type')
                ->setParameter('type', $type);
        }

        // Apply difficulty filter
        if (!empty($difficulty)) {
            $queryBuilder
                ->andWhere('g.difficulty = :difficulty')
                ->setParameter('difficulty', $difficulty);
        }

        // Apply free only filter
        if ($freeOnly === 'true' || $freeOnly === '1') {
            $queryBuilder->andWhere('g.tokenCost = 0');
        }

        $queryBuilder->orderBy('g.createdAt', 'DESC');

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            6 // 6 games per page
        );

        // Get rating stats for all games in current page
        $gameIds = array_map(fn($game) => $game->getId(), iterator_to_array($pagination));
        $gameRatings = $this->ratingRepository->getAverageRatingsForGames($gameIds);

        // If Ajax request, return only the games partial
        if ($isAjax) {
            return $this->render('front/game/_games_list.html.twig', [
                'games' => $pagination,
                'gameRatings' => $gameRatings,
            ]);
        }

        return $this->render('front/game/index.html.twig', [
            'games' => $pagination,
            'gameRatings' => $gameRatings,
            'search' => $search,
            'type' => $type,
            'difficulty' => $difficulty,
            'freeOnly' => $freeOnly,
        ]);
    }

    /**
    * Show game details
    */
    #[Route('/{id}', name: 'front_game_show', methods: ['GET'])]
    public function show(Game $game, Request $request): Response
    {
        // Check if game is active
        if (!$game->isActive()) {
            $this->addFlash('info', '<i class="bi bi-info-circle me-2"></i>This game has been deactivated by the admin and is currently unavailable.');
            return $this->redirectToRoute('front_game_index');
        }

        $student = null;
        if ($this->getUser() && $this->getUser()->getStudentProfile()) {
            $student = $this->getUser()->getStudentProfile();
            
            // Only show token cost warning if not coming from game completion
            $session = $request->getSession();
            $flashBag = $session->getFlashBag();
            $hasSuccessMessage = $flashBag->has('success');
            
            if (!$hasSuccessMessage) {
                // Show token cost warning if game has a cost
                if ($game->getTokenCost() > 0) {
                    $canAfford = $student->getTotalTokens() >= $game->getTokenCost();
                    
                    if ($canAfford) {
                        $this->addFlash('warning', sprintf(
                            '<i class="bi bi-exclamation-triangle me-2"></i><strong>Note:</strong> Playing this game will cost <strong>%d token%s</strong>. You currently have <strong>%d tokens</strong>.',
                            $game->getTokenCost(),
                            $game->getTokenCost() > 1 ? 's' : '',
                            $student->getTotalTokens()
                        ));
                    } else {
                        $this->addFlash('danger', sprintf(
                            '<i class="bi bi-x-circle me-2"></i><strong>Insufficient tokens!</strong> This game costs <strong>%d token%s</strong> but you only have <strong>%d tokens</strong>.',
                            $game->getTokenCost(),
                            $game->getTokenCost() > 1 ? 's' : '',
                            $student->getTotalTokens()
                        ));
                    }
                } else {
                    $this->addFlash('info', '<i class="bi bi-gift me-2"></i><strong>Free game!</strong> No tokens required to play.');
                }
            }
        }

        // Get rating stats
        $ratingStats = $this->ratingRepository->getGameRatingStats($game);
        $userRating = null;
        
        if ($this->getUser()) {
            $rating = $this->ratingRepository->getUserRating($game, $this->getUser());
            $userRating = $rating ? $rating->getRating() : 0;
        }

        return $this->render('front/game/show.html.twig', [
            'game' => $game,
            'student' => $student,
            'rewards' => $game->getRewards(),
            'averageRating' => $ratingStats['average'],
            'totalRatings' => $ratingStats['count'],
            'userRating' => $userRating,
        ]);
    }

    /**
    * Check if student can afford a game (Ajax endpoint)
    */
    #[Route('/{id}/check-tokens', name: 'front_game_check_tokens', methods: ['POST'])]
    #[IsGranted('ROLE_STUDENT')]
    public function checkTokens(Game $game): JsonResponse
    {
        $user = $this->getUser();
        $student = $user->getStudentProfile();

        if (!$student) {
            return $this->json([
                'success' => false,
                'message' => 'Student profile not found'
            ], 404);
        }

        $validation = $this->tokenService->validateTransaction($student, $game);

        return $this->json([
            'success' => $validation['valid'],
            'canAfford' => $validation['valid'],
            'message' => $validation['message'],
            'missing' => $validation['missing'],
            'currentBalance' => $student->getTotalTokens(),
            'gameCost' => $game->getTokenCost(),
            'gameIsFree' => $this->tokenService->isFreeGame($game),
        ]);
    }

    /**
    * Play game interface
    */
    #[Route('/{id}/play', name: 'front_game_play', methods: ['GET'])]
    #[IsGranted('ROLE_STUDENT')]
    public function play(Game $game, Request $request): Response
    {
        // Check if game is active
        if (!$game->isActive()) {
            $this->addFlash('info', '<i class="bi bi-info-circle me-2"></i>This game has been deactivated by the admin and is currently unavailable.');
            return $this->redirectToRoute('front_game_index');
        }

        $user = $this->getUser();
        $student = $user->getStudentProfile();

        if (!$student) {
            $this->addFlash('error', '<i class="bi bi-exclamation-triangle me-2"></i>Student profile not found. Please contact support.');
            return $this->redirectToRoute('front_game_index');
        }

        // Check if user has enough tokens using TokenService
        if (!$this->tokenService->hasEnoughTokens($student, $game)) {
            $missing = $this->tokenService->getMissingTokens($student, $game);
            $this->addFlash('warning', sprintf(
                '<i class="bi bi-coin me-2"></i><strong>Not enough tokens!</strong> You need <strong>%d more token%s</strong> to play this game. Current balance: <strong>%d tokens</strong>.',
                $missing,
                $missing > 1 ? 's' : '',
                $student->getTotalTokens()
            ));
            return $this->redirectToRoute('front_game_show', ['id' => $game->getId()]);
        }

        // Store balance before deduction
        $balanceBeforePlay = $student->getTotalTokens();

        // Deduct token cost using TokenService
        if ($game->getTokenCost() > 0) {
            $this->tokenService->deductTokens($student, $game, 'Game play: ' . $game->getName());
            
            // Store game cost in session for completion message
            $session = $request->getSession();
            $session->set('game_' . $game->getId() . '_cost', $game->getTokenCost());
            $session->set('game_' . $game->getId() . '_balance_before', $balanceBeforePlay);
        }

        return $this->render('front/game/play.html.twig', [
            'game' => $game,
            'student' => $student,
        ]);
    }

    /**
     * Complete game and earn rewards
     */
    #[Route('/{id}/complete', name: 'front_game_complete', methods: ['POST'])]
    #[IsGranted('ROLE_STUDENT')]
    public function complete(Game $game, Request $request): Response
    {
        $user = $this->getUser();
        $student = $user->getStudentProfile();

        if (!$student) {
            $this->addFlash('error', '<i class="bi bi-exclamation-triangle me-2"></i>Student profile not found. Please contact support.');
            return $this->redirectToRoute('front_game_index');
        }

        // Clear any existing flash messages to avoid clutter
        $session = $request->getSession();
        $session->getFlashBag()->clear();

        // Get session data about the game cost
        $gameCost = $session->get('game_' . $game->getId() . '_cost', 0);
        $balanceBeforePlay = $session->get('game_' . $game->getId() . '_balance_before', null);
        
        // Clear session data
        $session->remove('game_' . $game->getId() . '_cost');
        $session->remove('game_' . $game->getId() . '_balance_before');

        // Get current values before update
        $tokensBefore = $student->getTotalTokens();
        $xpBefore = $student->getTotalXP();
        
        // Get reward values
        $rewardTokens = $game->getRewardTokens();
        $rewardXP = $game->getRewardXP();

        // DIRECT UPDATE: Bypass service and update directly
        $student->setTotalTokens($tokensBefore + $rewardTokens);
        $student->setTotalXP($xpBefore + $rewardXP);
        
        // Ensure entity is managed
        if (!$this->entityManager->contains($student)) {
            $student = $this->entityManager->merge($student);
        }
        
        // Flush to database
        $this->entityManager->flush();
        
        // Refresh to verify
        $this->entityManager->refresh($student);
        
        $tokensAfter = $student->getTotalTokens();
        $xpAfter = $student->getTotalXP();

        // Build comprehensive completion message
        if ($gameCost > 0 && $balanceBeforePlay !== null) {
            $netGain = $tokensAfter - $balanceBeforePlay;
            
            $message = sprintf(
                '<i class="bi bi-trophy-fill me-2"></i><strong>Game Complete!</strong><br>' .
                '<div class="mt-2">' .
                '<i class="bi bi-wallet2 me-1"></i>Cost: <strong>%d tokens</strong> | ' .
                '<i class="bi bi-coin me-1"></i>Earned: <strong>%d tokens</strong> + ' .
                '<i class="bi bi-star-fill me-1"></i><strong>%d XP</strong><br>' .
                '<i class="bi bi-calculator me-1"></i>Net result: <strong class="%s">%+d tokens</strong> (from %d to %d)' .
                '</div>',
                $gameCost,
                $rewardTokens,
                $rewardXP,
                $netGain >= 0 ? 'text-success' : 'text-danger',
                $netGain,
                $balanceBeforePlay,
                $tokensAfter
            );
        } else {
            // Fallback if session data not available
            $message = sprintf(
                '<i class="bi bi-trophy-fill me-2"></i><strong>Game Complete!</strong><br>' .
                'Tokens: %d → %d (earned: +%d) <i class="bi bi-coin"></i><br>' .
                'XP: %d → %d (earned: +%d) <i class="bi bi-star-fill"></i>',
                $tokensBefore,
                $tokensAfter,
                $rewardTokens,
                $xpBefore,
                $xpAfter,
                $rewardXP
            );
        }

        $this->addFlash('success', $message);

        return $this->redirectToRoute('front_game_show', ['id' => $game->getId()]);
    }

    /**
     * Toggle favorite status for a game (Ajax endpoint)
     */
    #[Route('/{id}/toggle-favorite', name: 'front_game_toggle_favorite', methods: ['POST'])]
    public function toggleFavorite(Game $game): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return $this->json([
                    'success' => false,
                    'message' => 'You must be logged in to favorite games'
                ], 401);
            }

            $isFavorited = $user->hasFavoriteGame($game);

            if ($isFavorited) {
                // Remove from favorites
                $user->removeFavoriteGame($game);
                $message = 'Removed from favorites';
                $action = 'removed';
            } else {
                // Add to favorites (enforce uniqueness - already handled by Collection)
                $user->addFavoriteGame($game);
                $message = 'Added to favorites';
                $action = 'added';
            }

            // Persist changes
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => $message,
                'action' => $action,
                'isFavorited' => !$isFavorited, // New state
                'favoritesCount' => $game->getFavoritedBy()->count()
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            error_log('Favorite toggle error: ' . $e->getMessage());
            
            return $this->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show user's favorite games
     */
    #[Route('/favorites/my-favorites', name: 'front_game_favorites', methods: ['GET'])]
    public function favorites(Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to view favorites');
            return $this->redirectToRoute('app_login');
        }
        
        $favoriteGames = $user->getFavoriteGames();

        // Filter only active games
        $activeGames = $favoriteGames->filter(function($game) {
            return $game->isActive();
        });

        // Paginate favorites
        $pagination = $this->paginator->paginate(
            $activeGames,
            $request->query->getInt('page', 1),
            6 // 6 games per page
        );

        return $this->render('front/game/favorites.html.twig', [
            'games' => $pagination,
        ]);
    }

    /**
    * Filter games by type with pagination
    */
    #[Route('/type/{type}', name: 'front_game_by_type', methods: ['GET'])]
    public function byType(string $type, Request $request): Response
    {
        $validTypes = ['PUZZLE', 'MEMORY', 'TRIVIA', 'ARCADE'];
        if (!in_array($type, $validTypes)) {
            throw $this->createNotFoundException('Invalid game type');
        }

        $queryBuilder = $this->gameRepository->createQueryBuilder('g')
            ->where('g.isActive = :active')
            ->andWhere('g.type = :type')
            ->setParameter('active', true)
            ->setParameter('type', $type)
            ->orderBy('g.createdAt', 'DESC');

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            6 // 6 games per page
        );

        return $this->render('front/game/index.html.twig', [
            'games' => $pagination,
            'filter_type' => $type,
        ]);
    }
}