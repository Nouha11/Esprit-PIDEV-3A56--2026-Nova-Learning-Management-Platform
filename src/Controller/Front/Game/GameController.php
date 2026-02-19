<?php
namespace App\Controller\Front\Game;

use App\Entity\Gamification\Game;
use App\Repository\Gamification\GameRepository as GamificationGameRepository;
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

        // If Ajax request, return only the games partial
        if ($isAjax) {
            return $this->render('front/game/_games_list.html.twig', [
                'games' => $pagination,
            ]);
        }

        return $this->render('front/game/index.html.twig', [
            'games' => $pagination,
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
    public function show(Game $game): Response
    {
        if (!$game->isActive()) {
            throw $this->createNotFoundException('This game is not available');
        }

        $student = null;
        if ($this->getUser() && $this->getUser()->getStudentProfile()) {
            $student = $this->getUser()->getStudentProfile();
        }

        return $this->render('front/game/show.html.twig', [
            'game' => $game,
            'student' => $student,
            'rewards' => $game->getRewards(), // Add this line
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
    public function play(Game $game): Response
    {
        
        if (!$game->isActive()) {
            throw $this->createNotFoundException('This game is not available');
        }

        $user = $this->getUser();
        $student = $user->getStudentProfile();

        if (!$student) {
            $this->addFlash('error', 'Student profile not found');
            return $this->redirectToRoute('front_game_index');
        }

        // Check if user has enough tokens using TokenService
        if (!$this->tokenService->hasEnoughTokens($student, $game)) {
            $missing = $this->tokenService->getMissingTokens($student, $game);
            $this->addFlash('error', sprintf(
                'Not enough tokens to play this game. You need %d more token%s.',
                $missing,
                $missing > 1 ? 's' : ''
            ));
            return $this->redirectToRoute('front_game_show', ['id' => $game->getId()]);
        }

        // Deduct token cost using TokenService
        if ($game->getTokenCost() > 0) {
            $this->tokenService->deductTokens($student, $game, 'Game play: ' . $game->getName());
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
    public function complete(Game $game): Response
    {
        $user = $this->getUser();
        $student = $user->getStudentProfile();

        if (!$student) {
            $this->addFlash('error', 'Student profile not found');
            return $this->redirectToRoute('front_game_index');
        }

        // Process game completion (assuming they won)
        $rewards = $this->gameService->processGameCompletion($game, $student, true);

        // Build success message
        $message = sprintf(
            'Congratulations! You earned %d tokens and %d XP!',
            $rewards['tokens'],
            $rewards['xp']
        );

        // Add special rewards to the message
        if (!empty($rewards['special_rewards'])) {
            $specialRewardsText = [];
            foreach ($rewards['special_rewards'] as $specialReward) {
                $specialRewardsText[] = $specialReward['name'] . ': ' . $specialReward['awarded'];
            }
            $message .= ' Special rewards: ' . implode(', ', $specialRewardsText);
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