<?php

namespace App\Controller\Admin\Game;

use App\Entity\Gamification\Game;
use App\Form\Admin\GameFormType;
use App\Repository\Gamification\GameRepository;
use App\Service\game\GameService;
use App\Service\game\GameTemplateService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/games')]
#[IsGranted('ROLE_ADMIN')]
class GameAdminController extends AbstractController
{
    public function __construct(
        private GameService $gameService,
        private GameRepository $gameRepository,
        private PaginatorInterface $paginator,
        private GameTemplateService $templateService
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
    #[Route('/{id}', name: 'admin_game_show', requirements: ['id' => '\d+'], methods: ['GET'])]
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
    #[Route('/{id}/edit', name: 'admin_game_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
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
    #[Route('/{id}/toggle-active', name: 'admin_game_toggle_active', requirements: ['id' => '\d+'], methods: ['POST'])]
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
    #[Route('/{id}/delete', name: 'admin_game_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
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

    /**
     * Show game template generator
     */
    #[Route('/templates', name: 'admin_game_templates', methods: ['GET'])]
    public function templates(): Response
    {
        $templates = $this->templateService->getTemplates();
        
        return $this->render('admin/game/templates.html.twig', [
            'templates' => $templates,
        ]);
    }

    /**
     * Get template configuration (Ajax)
     */
    #[Route('/templates/config', name: 'admin_game_template_config', methods: ['POST'])]
    public function getTemplateConfig(Request $request): JsonResponse
    {
        $category = $request->request->get('category');
        $key = $request->request->get('key');
        $difficulty = $request->request->get('difficulty', 'MEDIUM');

        $config = $this->templateService->getTemplateConfig($category, $key, $difficulty);

        if (empty($config)) {
            return $this->json(['error' => 'Template not found'], 404);
        }

        return $this->json($config);
    }

    /**
     * Create game from template
     */
    #[Route('/templates/create', name: 'admin_game_create_from_template', methods: ['POST'])]
    public function createFromTemplate(Request $request, EntityManagerInterface $em): Response
    {
        $category = $request->request->get('category');
        $key = $request->request->get('key');
        $difficulty = $request->request->get('difficulty', 'MEDIUM');
        $customName = $request->request->get('custom_name');

        $config = $this->templateService->getTemplateConfig($category, $key, $difficulty);

        if (empty($config)) {
            $this->addFlash('error', 'Template not found');
            return $this->redirectToRoute('admin_game_templates');
        }

        // Create game entity
        $game = new Game();
        $game->setName($customName ?: $config['name']);
        $game->setDescription($config['description']);
        $game->setType($config['type']);
        $game->setCategory($config['category']);
        $game->setTokenCost($config['tokenCost']);
        $game->setIsActive(true);

        if ($config['category'] === 'FULL_GAME') {
            $game->setDifficulty($config['difficulty']);
            $game->setRewardTokens($config['rewardTokens']);
            $game->setRewardXP($config['rewardXP']);
        } else {
            $game->setDifficulty('EASY');
            $game->setEnergyPoints($config['energyPoints']);
            $game->setRewardTokens(0);
            $game->setRewardXP(0);
        }

        // Store engine and settings as JSON in description or create a new field
        $gameData = [
            'engine' => $config['engine'],
            'settings' => $config['settings'] ?? [],
        ];
        
        // You might want to add a 'gameData' JSON field to the Game entity
        // For now, we'll append it to description
        $game->setDescription($config['description'] . ' [Engine: ' . $config['engine'] . ']');

        $em->persist($game);
        $em->flush();

        $this->addFlash('success', 'Game created successfully from template!');
        return $this->redirectToRoute('admin_game_edit', ['id' => $game->getId()]);
    }
}
