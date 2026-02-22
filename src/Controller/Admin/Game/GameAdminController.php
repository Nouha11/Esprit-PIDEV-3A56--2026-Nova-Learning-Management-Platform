<?php

namespace App\Controller\Admin\Game;

use App\Entity\Gamification\Game;
use App\Entity\Gamification\GameContent;
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
        private GameTemplateService $templateService,
        private \App\Service\game\HuggingFaceService $huggingFaceService
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
        $category = $request->query->get('category', ''); // FULL_GAME or MINI_GAME
        $isAjax = $request->isXmlHttpRequest();

        $queryBuilder = $this->gameRepository->createQueryBuilder('g');

        // Apply category filter if provided (for Ajax requests)
        if (!empty($category)) {
            $queryBuilder
                ->where('g.category = :category')
                ->setParameter('category', $category);
        }

        // Apply search filter
        if (!empty($search)) {
            $andWhere = !empty($category) ? 'andWhere' : 'where';
            $queryBuilder
                ->$andWhere('g.name LIKE :search OR g.description LIKE :search')
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

        // For non-Ajax requests, get both full games and mini games
        $fullGamesQb = $this->gameRepository->createQueryBuilder('g')
            ->where('g.category = :category')
            ->setParameter('category', 'FULL_GAME')
            ->orderBy('g.createdAt', 'DESC');

        $fullGames = $this->paginator->paginate(
            $fullGamesQb,
            $request->query->getInt('page', 1),
            10
        );

        $miniGamesQb = $this->gameRepository->createQueryBuilder('g')
            ->where('g.category = :category')
            ->setParameter('category', 'MINI_GAME')
            ->orderBy('g.createdAt', 'DESC');

        $miniGames = $this->paginator->paginate(
            $miniGamesQb,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/game/index.html.twig', [
            'fullGames' => $fullGames,
            'miniGames' => $miniGames,
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
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $game = new Game();
        $form = $this->createForm(GameFormType::class, $game);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->gameService->createGame($game);
            
            // Handle custom content
            $this->saveGameContent($game, $request, $em);
            
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
    public function edit(Request $request, Game $game, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(GameFormType::class, $game);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->gameService->updateGame($game);
            
            // Handle custom content
            $this->saveGameContent($game, $request, $em);
            
            $this->addFlash('success', 'Game updated successfully!');
            return $this->redirectToRoute('admin_game_index');
        }
        
        // Load existing content for editing
        $existingContent = null;
        if ($game->getContent()) {
            $existingContent = $game->getContent()->getData();
        }
        
        return $this->render('admin/game/edit.html.twig', [
        'form' => $form,
        'game' => $game,
        'existingContent' => $existingContent,
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

        // Determine the game name
        $baseName = $customName ?: $config['name'];
        $gameName = $baseName;
        
        // Check if game with this name already exists
        $existingGame = $this->gameRepository->findOneBy(['name' => $gameName]);
        
        if ($existingGame) {
            // Make name unique by appending a number
            $counter = 1;
            do {
                $gameName = $baseName . ' (' . $counter . ')';
                $existingGame = $this->gameRepository->findOneBy(['name' => $gameName]);
                $counter++;
            } while ($existingGame && $counter < 100); // Safety limit
            
            if ($existingGame) {
                $this->addFlash('error', 'Unable to create unique game name. Please use a custom name.');
                return $this->redirectToRoute('admin_game_templates');
            }
        }

        // Create game entity
        $game = new Game();
        $game->setName($gameName);
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
        
        // Store engine in description so the play controller can extract it
        $game->setDescription($config['description'] . ' [Engine: ' . $config['engine'] . ']');

        $em->persist($game);
        $em->flush();
        
        // DO NOT create default content for template games
        // Let the game engine generate appropriate content based on difficulty
        // Only create content if admin explicitly adds it via edit page

        $this->addFlash('success', 'Game created successfully from template!');
        return $this->redirectToRoute('admin_game_edit', ['id' => $game->getId()]);
    }

    /**
     * Test AI connection
     */
/*     #[Route('/ai/test', name: 'admin_game_ai_test', methods: ['GET'])]
    public function testAI(): JsonResponse
    {
        try {
            $isConnected = $this->huggingFaceService->testConnection();
            
            if ($isConnected) {
                return $this->json([
                    'success' => true,
                    'message' => 'Hugging Face API is connected and working!'
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'Failed to connect to Hugging Face API. Check your API key.'
                ], 500);
            }
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    } */

    /**
     * Generate trivia questions using AI
     */
/*     #[Route('/ai/generate-questions', name: 'admin_game_ai_generate', methods: ['POST'])]
    public function generateQuestions(Request $request): JsonResponse
    {
        $topic = $request->request->get('topic');
        $count = $request->request->getInt('count', 5);
        $difficulty = $request->request->get('difficulty', 'MEDIUM');
        
        if (empty($topic)) {
            return $this->json([
                'success' => false,
                'message' => 'Topic is required'
            ], 400);
        }
        
        if ($count < 3 || $count > 10) {
            return $this->json([
                'success' => false,
                'message' => 'Question count must be between 3 and 10'
            ], 400);
        }
        
        try {
            $questions = $this->huggingFaceService->generateTriviaQuestions($topic, $count, $difficulty);
            
            if (empty($questions)) {
                return $this->json([
                    'success' => false,
                    'message' => 'No questions were generated. Please try again with a different topic.'
                ], 500);
            }
            
            return $this->json([
                'success' => true,
                'message' => "Successfully generated {$count} {$difficulty} questions about '{$topic}'",
                'questions' => $questions,
                'count' => count($questions)
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    } */

    /**
     * Save or update game content based on game type
     */
    private function saveGameContent(Game $game, Request $request, EntityManagerInterface $em): void
    {
        $gameType = $game->getType();
        
        // Get or create GameContent entity
        $content = $game->getContent();
        if (!$content) {
            $content = new GameContent();
            $content->setGame($game);
            $em->persist($content);
        }
        
        // Extract content based on game type
        $data = [];
        
        switch ($gameType) {
            case 'PUZZLE':
                $word = $request->request->get('content_word');
                $hint = $request->request->get('content_hint');
                if ($word) {
                    $data['word'] = $word;
                }
                if ($hint) {
                    $data['hint'] = $hint;
                }
                break;
                
            case 'MEMORY':
                $wordsText = $request->request->get('content_words');
                if ($wordsText) {
                    $words = array_filter(array_map('trim', explode("\n", $wordsText)));
                    if (count($words) === 6) {
                        $data['words'] = $words;
                    }
                }
                break;
                
            case 'TRIVIA':
                $topic = $request->request->get('content_topic');
                $questionsJson = $request->request->get('content_questions');
                
                if ($topic) {
                    $data['topic'] = $topic;
                }
                
                if ($questionsJson) {
                    try {
                        $questions = json_decode($questionsJson, true);
                        if (is_array($questions) && !empty($questions)) {
                            $data['questions'] = $questions;
                        }
                    } catch (\Exception $e) {
                        // Invalid JSON, skip
                    }
                }
                break;
                
            case 'ARCADE':
                $sentencesText = $request->request->get('content_sentences');
                if ($sentencesText) {
                    $sentences = array_filter(array_map('trim', explode("\n", $sentencesText)));
                    if (count($sentences) >= 3 && count($sentences) <= 5) {
                        $data['sentences'] = $sentences;
                    }
                }
                break;
        }
        
        // Save data if any content was provided
        if (!empty($data)) {
            $content->setData($data);
            $em->flush();
        }
    }
    
    /**
     * Create default game content for template-based games
     */
    private function createDefaultGameContent(Game $game, array $config, EntityManagerInterface $em): void
    {
        $content = new GameContent();
        $content->setGame($game);
        
        $data = [];
        
        switch ($game->getType()) {
            case 'PUZZLE':
                $data = [
                    'word' => 'EXAMPLE',
                    'hint' => 'A sample word to demonstrate the game'
                ];
                break;
                
            case 'MEMORY':
                $data = [
                    'words' => ['🎮', '🎯', '🎲', '🎪', '🎨', '🎭']
                ];
                break;
                
            case 'TRIVIA':
                $data = [
                    'topic' => 'General Knowledge',
                    'questions' => [
                        [
                            'question' => 'What is 2 + 2?',
                            'choices' => ['3', '4', '5', '6'],
                            'correct' => 1
                        ],
                        [
                            'question' => 'What color is the sky?',
                            'choices' => ['Green', 'Blue', 'Red', 'Yellow'],
                            'correct' => 1
                        ],
                        [
                            'question' => 'How many days in a week?',
                            'choices' => ['5', '6', '7', '8'],
                            'correct' => 2
                        ]
                    ]
                ];
                break;
                
            case 'ARCADE':
                $data = [
                    'sentences' => [
                        'The quick brown fox jumps over the lazy dog.',
                        'Practice makes perfect.',
                        'Typing speed improves with time.'
                    ]
                ];
                break;
        }
        
        if (!empty($data)) {
            $content->setData($data);
            $em->persist($content);
            $em->flush();
        }
    }
}
