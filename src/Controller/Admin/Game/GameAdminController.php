<?php

namespace App\Controller\Admin\Game;

use App\Entity\Gamification\Game;
use App\Form\Admin\GameFormType;
use App\Repository\Gamification\GameRepository;
use App\Service\game\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/games')]
//#[IsGranted('ROLE_ADMIN')]
class GameAdminController extends AbstractController
{
    public function __construct(
        private GameService $gameService,
        private GameRepository $gameRepository
    ) {
    }

    /**
    * List all games
    */
    #[Route('', name: 'admin_game_index', methods: ['GET'])]
    public function index(): Response
    {
        $games = $this->gameRepository->findAll();
        return $this->render('admin/game/index.html.twig', [
        'games' => $games,
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
    * Delete game
    */
    #[Route('/{id}/delete', name: 'admin_game_delete', methods: ['POST'])]
    public function delete(Request $request, Game $game): Response
    {
        if ($this->isCsrfTokenValid('delete'.$game->getId(), $request->request->get('_token'))) {
            $this->gameService->deleteGame($game);
            $this->addFlash('success', 'Game deleted successfully!');
        }
        return $this->redirectToRoute('admin_game_index');
    }
}