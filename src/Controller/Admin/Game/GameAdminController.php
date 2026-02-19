<?php

namespace App\Controller\Admin\Game;

use App\Entity\Gamification\Game;
use App\Form\Admin\GameFormType;
use App\Repository\Gamification\GameRepository;
use App\Service\game\GameService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/games')]
#[IsGranted('ROLE_ADMIN')]
class GameAdminController extends AbstractController
{
    public function __construct(
        private GameService $gameService,
        private GameRepository $gameRepository,
        private PaginatorInterface $paginator
    ) {
    }

    /**
    * List all games with Ajax filters
    */
    #[Route('', name: 'admin_game_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $type = $request->query->get('type', '');
        $difficulty = $request->query->get('difficulty', '');
        $status = $request->query->get('status', ''); // active/inactive
        $isAjax = $request->isXmlHttpRequest();

        $queryBuilder = $this->gameRepository->createQueryBuilder('g');

        // Apply search filter
        if (!empty($search)) {
            $queryBuilder
                ->where('g.name LIKE :search OR g.description LIKE :search')
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

        // Apply status filter
        if ($status === 'active') {
            $queryBuilder->andWhere('g.isActive = true');
        } elseif ($status === 'inactive') {
            $queryBuilder->andWhere('g.isActive = false');
        }

        $queryBuilder->orderBy('g.createdAt', 'DESC');

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10 // 10 games per page
        );

        // If Ajax request, return only the table partial
        if ($isAjax) {
            return $this->render('admin/game/_games_table.html.twig', [
                'games' => $pagination,
            ]);
        }

        return $this->render('admin/game/index.html.twig', [
            'games' => $pagination,
            'search' => $search,
            'type' => $type,
            'difficulty' => $difficulty,
            'status' => $status,
        ]);
    }

    /**
    * Create new game
    */
    #[Route('/new', name: 'admin_game_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $game = new Game();
        $form = $this->createForm(GameFormType::class, $game);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->gameService->createGame($game);
            $this->addFlash('success', 'Game created successfully!');
            return $this->redirectToRoute('admin_game_index');
        }
        return $this->render('admin/game/new.html.twig', [
        'form' => $form,
        ]);
    }

    /**
    * Show game details
    */
    #[Route('/{id}', name: 'admin_game_show', methods: ['GET'])]
    public function show(Game $game): Response
    {
        return $this->render('admin/game/show.html.twig', [
            'game' => $game,
            'rewards' => $game->getRewards(), // Show associated rewards
        ]);
    }

    /**
    * Edit game
    */
    #[Route('/{id}/edit', name: 'admin_game_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Game $game): Response
    {
        $form = $this->createForm(GameFormType::class, $game);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->gameService->updateGame($game);
            $this->addFlash('success', 'Game updated successfully!');
            return $this->redirectToRoute('admin_game_index');
        }
        return $this->render('admin/game/edit.html.twig', [
        'form' => $form,
        'game' => $game,
        ]);
    }
    
    /**
     * Toggle game active status (Ajax)
     */
    #[Route('/{id}/toggle-active', name: 'admin_game_toggle_active', methods: ['POST'])]
    public function toggleActive(Request $request, Game $game, EntityManagerInterface $entityManager): Response
    {
        // Verify CSRF token
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('toggle_active_' . $game->getId(), $token)) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid security token.'
            ], 400);
        }

        try {
            // Toggle the active status
            $newStatus = !$game->isActive();
            $game->setIsActive($newStatus);
            $entityManager->flush();

            $statusText = $newStatus ? 'activated' : 'deactivated';
            $message = sprintf('Game "%s" has been %s successfully!', $game->getName(), $statusText);

            return $this->json([
                'success' => true,
                'message' => $message,
                'isActive' => $newStatus,
                'gameId' => $game->getId(),
                'gameName' => $game->getName()
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error toggling game status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete game
     */
    #[Route('/{id}/delete', name: 'admin_game_delete', methods: ['POST'])]
    public function delete(Request $request, Game $game, EntityManagerInterface $entityManager): Response 
    {
        if ($this->isCsrfTokenValid('delete'.$game->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($game);
                $entityManager->flush();
                
                $this->addFlash('success', 'Game deleted successfully!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error deleting game: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Invalid security token.');
        }
        
        return $this->redirectToRoute('admin_game_index');
    }
}