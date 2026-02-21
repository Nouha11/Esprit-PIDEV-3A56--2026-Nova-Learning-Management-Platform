<?php

namespace App\Controller\Front\Quiz;

use App\Entity\Quiz;
use App\Repository\QuizRepository;
use App\Repository\Quiz\QuestionRepository;
use App\Repository\Quiz\ChoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/game/quiz')]
final class QuizGameController extends AbstractController
{
    // ---------------------------------------------------
    // 1. THE ARCADE PAGE (List of Quizzes)
    // ---------------------------------------------------
    #[Route('/', name: 'app_front_quiz_index', methods: ['GET'])]
    public function index(QuizRepository $quizRepository, Request $request): Response
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
        
        // Get quizzes with filters and sorting
        $quizzes = $quizRepository->findWithFiltersAndSort($filters, $sortBy, $sortOrder);
        
        return $this->render('front/quiz/game/index.html.twig', [            
            'quizzes' => $quizzes,
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

            if ($selectedChoice && $selectedChoice->isCorrect()) {
                // CORRECT
                $currentScore = $session->get('quiz_score');
                $session->set('quiz_score', $currentScore + $question->getXpValue());
                $this->addFlash('success', '✅ Correct! +' . $question->getXpValue() . ' XP');
            } else {
                // WRONG
                $this->addFlash('danger', '❌ Wrong answer!');
            }

            // Remove played question and save
            array_shift($queue); 
            $session->set('quiz_queue', $queue);

            return $this->redirectToRoute('app_quiz_play');
        }

        // Render the "Play" template
        return $this->render('front/quiz/game/play.html.twig', [
            'question' => $question
        ]);
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