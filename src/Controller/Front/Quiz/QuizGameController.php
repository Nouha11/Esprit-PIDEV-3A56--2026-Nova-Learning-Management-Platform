<?php

namespace App\Controller\Front\Quiz;

use App\Entity\Quiz;
use App\Repository\QuizRepository;
use App\Repository\Quiz\QuestionRepository;
use App\Repository\Quiz\ChoiceRepository;
use App\Service\Quiz\QuizHintService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/game/quiz')]
final class QuizGameController extends AbstractController
{
    // ---------------------------------------------------
    // 1. THE ARCADE PAGE (List of Quizzes)
    // ---------------------------------------------------
    #[Route('/', name: 'app_front_quiz_index', methods: ['GET'])]
    public function index(QuizRepository $quizRepository, PaginatorInterface $paginator, Request $request): Response
    {
        // Get filter data from query parameters
        $filters = [];
        $sortBy = $request->query->get('sortBy', 'title');
        $sortOrder = $request->query->get('sortOrder', 'ASC');
        
        // Build filters array
        if ($search = $request->query->get('search')) {
            $filters['search'] = $search;
        }
        if ($minQuestions = $request->query->get('minQuestions')) {
            $filters['minQuestions'] = (int)$minQuestions;
        }
        if ($maxQuestions = $request->query->get('maxQuestions')) {
            $filters['maxQuestions'] = (int)$maxQuestions;
        }
        
        // Get query builder with filters and sorting
        $queryBuilder = $quizRepository->findWithFiltersAndSort($filters, $sortBy, $sortOrder);
        
        // Paginate the results
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            9 // items per page (3x3 grid)
        );
        
        return $this->render('front/quiz/game/index.html.twig', [            
            'pagination' => $pagination,
            'currentFilters' => $filters,
            'currentSort' => ['by' => $sortBy, 'order' => $sortOrder]
        ]);
    }

    // ---------------------------------------------------
    // 2. THE GAME ENGINE (Play Logic)
    // ---------------------------------------------------
    #[Route('/play/{id}', name: 'app_quiz_play', defaults: ['id' => null], methods: ['GET', 'POST'])]
    public function play(
        ?Quiz $quiz, 
        QuestionRepository $questionRepository,
        ChoiceRepository $choiceRepository,
        Request $request
    ): Response 
    {
        $session = $request->getSession();

        // --- A. INITIALIZE GAME (If starting new) ---
        if (!$session->has('quiz_queue') || ($quiz && $session->get('current_quiz_id') !== $quiz->getId())) {
            
            // If a specific quiz is requested, get those questions. Otherwise, get ALL (Chaos Mode).
            if ($quiz) {
                $questions = $quiz->getQuestions()->toArray();
                $session->set('current_quiz_id', $quiz->getId());
            } else {
                $questions = $questionRepository->findAll();
                $session->remove('current_quiz_id');
            }

            if (empty($questions)) {
                $this->addFlash('warning', 'This quiz has no questions yet!');
                return $this->redirectToRoute('app_front_quiz_index');
            }

            $ids = array_map(fn($q) => $q->getId(), $questions);
            shuffle($ids); // Randomize order

            // Save state to session
            $session->set('quiz_queue', $ids);
            $session->set('quiz_score', 0); 
            $session->set('quiz_total', count($ids));
        }

        // --- B. CHECK QUEUE ---
        $queue = $session->get('quiz_queue', []);

        if (empty($queue)) {
            // No more questions! Go to results.
            return $this->redirectToRoute('app_quiz_result');
        }

        // --- C. LOAD CURRENT QUESTION ---
        $currentQuestionId = $queue[0]; 
        $question = $questionRepository->find($currentQuestionId);

        // Safety check: If question was deleted from DB mid-game
        if (!$question) {
            array_shift($queue);
            $session->set('quiz_queue', $queue);
            return $this->redirectToRoute('app_quiz_play');
        }

        // --- D. HANDLE ANSWER SUBMISSION ---
        if ($request->isMethod('POST')) {
            $submittedChoiceId = $request->request->get('answer');
            $selectedChoice = $choiceRepository->find($submittedChoiceId);

            // Check if hint was used for this question
            $hintUsed = $session->get('hint_used_' . $currentQuestionId, false);
            $xpToAward = $question->getXpValue();
            
            // Apply hint penalty if hint was used
            if ($hintUsed) {
                $xpToAward = (int) ceil($xpToAward / 2);
            }

            if ($selectedChoice && $selectedChoice->isCorrect()) {
                // CORRECT
                $currentScore = $session->get('quiz_score');
                $session->set('quiz_score', $currentScore + $xpToAward);
                
                if ($hintUsed) {
                    $this->addFlash('success', '✅ Correct! +' . $xpToAward . ' XP (hint penalty applied)');
                } else {
                    $this->addFlash('success', '✅ Correct! +' . $xpToAward . ' XP');
                }
            } else {
                // WRONG
                $this->addFlash('danger', '❌ Wrong answer!');
            }

            // Clear hint flag for this question
            $session->remove('hint_used_' . $currentQuestionId);

            // Remove played question and save
            array_shift($queue); 
            $session->set('quiz_queue', $queue);

            return $this->redirectToRoute('app_quiz_play');
        }

        // Check if hint was already used for this question
        $hintUsed = $session->get('hint_used_' . $currentQuestionId, false);
        $currentHint = $session->get('hint_text_' . $currentQuestionId, null);

        // Render the "Play" template
        return $this->render('front/quiz/game/play.html.twig', [
            'question' => $question,
            'hintUsed' => $hintUsed,
            'currentHint' => $currentHint
        ]);
    }

    // ---------------------------------------------------
    // 3. AI HINT ENDPOINT
    // ---------------------------------------------------
    #[Route('/hint/{questionId}', name: 'app_quiz_hint', methods: ['POST'])]
    public function getHint(
        int $questionId,
        QuestionRepository $questionRepository,
        QuizHintService $hintService,
        Request $request
    ): JsonResponse 
    {
        $session = $request->getSession();
        
        // Check if this is the current question
        $queue = $session->get('quiz_queue', []);
        if (empty($queue) || $queue[0] !== $questionId) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Invalid question'
            ], 400);
        }

        // Check if hint already used for this question
        if ($session->get('hint_used_' . $questionId, false)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Hint already used for this question'
            ], 400);
        }

        $question = $questionRepository->find($questionId);
        if (!$question) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Question not found'
            ], 404);
        }

        try {
            // Generate hint using AI
            $hint = $hintService->generateHint($question);
            
            // Calculate XP with penalty
            $originalXp = $question->getXpValue();
            $reducedXp = $hintService->calculateXpWithHintPenalty($originalXp);
            
            // Mark hint as used
            $session->set('hint_used_' . $questionId, true);
            $session->set('hint_text_' . $questionId, $hint);
            
            return new JsonResponse([
                'success' => true,
                'hint' => $hint,
                'originalXp' => $originalXp,
                'reducedXp' => $reducedXp,
                'penalty' => $originalXp - $reducedXp
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to generate hint. Please try again.'
            ], 500);
        }
    }

    #[Route('/restart', name: 'app_quiz_restart')]
    public function restart(Request $request): Response
    {
        $session = $request->getSession();
        $session->remove('quiz_queue');
        $session->remove('quiz_score');
        $session->remove('current_quiz_id');
        
        return $this->redirectToRoute('app_front_quiz_index');
    }
}