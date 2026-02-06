<?php
namespace App\Controller\Front\Game;

use App\Entity\Gamification\Game;
use App\Repository\Gamification\GameRepository as GamificationGameRepository;
use App\Service\game\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/games')]
class GameController extends AbstractController
{
    public function __construct(
        private GameService $gameService,
        private GamificationGameRepository $gameRepository
    ) {
    }

    /**
    * Browse all available games
    */
    #[Route('', name: 'front_game_index', methods: ['GET'])]
    public function index(): Response
    {
        $games = $this->gameService->getActiveGames();
        return $this->render('front/game/index.html.twig', [
        'games' => $games,
        ]);
    }

    /**
    * Show game details
    */
    #[Route('/{id}', name: 'front_game_show', methods: ['GET'])]
    public function show(Game $game): Response
    {
        // Check if game is active
        if (!$game->isActive()) {
        throw $this->createNotFoundException('This game is not available');
        }
        return $this->render('front/game/show.html.twig', [
        'game' => $game,
        ]);
    }

    /**
    * Play game interface
    */
    #[Route('/{id}/play', name: 'front_game_play', methods: ['GET'])]
    //#[IsGranted('ROLE_STUDENT')]
    public function play(Game $game): Response
    {
        if (!$game->isActive()) {
            throw $this->createNotFoundException('This game is not available');
        }
        // TODO: Check if user has enough tokens
        // $user = $this->getUser();
        // if (!$this->gameService->canUserPlayGame($user->getTokens(), $game)) {
        // $this->addFlash('error', 'Not enough tokens to play this game');
        // return $this->redirectToRoute('front_game_show', ['id' => $game->getId()]);
        // }
        return $this->render('front/game/play.html.twig', [
            'game' => $game,
        ]);
    }

    /**
    * Complete game and earn rewards
    */
    #[Route('/{id}/complete', name: 'front_game_complete', methods: ['POST'])]
    //#[IsGranted('ROLE_STUDENT')]
    public function complete(Game $game): Response
    {
        // TODO: Get actual user ID from security
        $userId = 1; // Placeholder
        $rewards = $this->gameService->processGameCompletion($game, $userId);
        $this->addFlash('success', sprintf(
            'Congratulations! You earned %d tokens and %d XP!',
            $rewards['tokens'],
            $rewards['xp']
        ));
        return $this->redirectToRoute('front_game_index');
    }

    /**
    * Filter games by type
    */
    #[Route('/type/{type}', name: 'front_game_by_type', methods: ['GET'])]
    public function byType(string $type): Response
    {
        $validTypes = ['PUZZLE', 'MEMORY', 'TRIVIA', 'ARCADE'];
        if (!in_array($type, $validTypes)) {
            throw $this->createNotFoundException('Invalid game type');
        }
        $games = $this->gameRepository->findByType($type);
        return $this->render('front/game/index.html.twig', [
            'games' => $games,
            'filter_type' => $type,
        ]);
    }
}