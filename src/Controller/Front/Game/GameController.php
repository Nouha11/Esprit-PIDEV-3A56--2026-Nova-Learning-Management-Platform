<?php
namespace App\Controller\Front\Game;

use App\Entity\Gamification\Game;
use App\Repository\Gamification\GameRepository as GamificationGameRepository;
use App\Service\game\GameService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/games')]
class GameController extends AbstractController
{
    public function __construct(
        private GameService $gameService,
        private GamificationGameRepository $gameRepository,
        private PaginatorInterface $paginator
    ) {
    }

    /**
    * Browse all available games with pagination
    */
    #[Route('', name: 'front_game_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $query = $this->gameRepository->createQueryBuilder('g')
            ->where('g.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery();

        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6 // 6 games per page
        );

        return $this->render('front/game/index.html.twig', [
            'games' => $pagination,
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

        // Check if user has enough tokens
        if (!$this->gameService->canUserPlayGame($student->getTotalTokens(), $game)) {
            $this->addFlash('error', sprintf(
                'Not enough tokens to play this game. You need %d tokens but only have %d.',
                $game->getTokenCost(),
                $student->getTotalTokens()
            ));
            return $this->redirectToRoute('front_game_show', ['id' => $game->getId()]);
        }

        // Deduct token cost
        if ($game->getTokenCost() > 0) {
            $this->gameService->deductGameCost($student, $game);
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
    * Filter games by type with pagination
    */
    #[Route('/type/{type}', name: 'front_game_by_type', methods: ['GET'])]
    public function byType(string $type, Request $request): Response
    {
        $validTypes = ['PUZZLE', 'MEMORY', 'TRIVIA', 'ARCADE'];
        if (!in_array($type, $validTypes)) {
            throw $this->createNotFoundException('Invalid game type');
        }

        $query = $this->gameRepository->createQueryBuilder('g')
            ->where('g.isActive = :active')
            ->andWhere('g.type = :type')
            ->setParameter('active', true)
            ->setParameter('type', $type)
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery();

        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6 // 6 games per page
        );

        return $this->render('front/game/index.html.twig', [
            'games' => $pagination,
            'filter_type' => $type,
        ]);
    }
}