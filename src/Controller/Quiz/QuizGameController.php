<?php

namespace App\Controller\Quiz;

use App\Repository\Quiz\QuestionRepository;
use App\Repository\Quiz\ChoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/quiz/game')]
class QuizGameController extends AbstractController
{
    #[Route('/play', name: 'app_quiz_play')]
    public function play(
        QuestionRepository $questionRepository,
        ChoiceRepository $choiceRepository,
        Request $request
    ): Response 
    {
        $session = $request->getSession();

        // ---------------------------------------------------
        // 1. INITIALIZE GAME (If starting for the first time)
        // ---------------------------------------------------
        if (!$session->has('quiz_queue')) {
            // Fetch ALL Question IDs
            $questions = $questionRepository->findAll();
            $ids = array_map(fn($q) => $q->getId(), $questions);
            
            shuffle($ids); // Randomize order

            // Save to session
            $session->set('quiz_queue', $ids);
            $session->set('quiz_score', 0); // Reset score
        }

        // Get the current queue
        $queue = $session->get('quiz_queue');

        // ---------------------------------------------------
        // 2. CHECK GAME OVER
        // ---------------------------------------------------
        if (empty($queue)) {
            // No more questions! Go to results.
            return $this->redirectToRoute('app_quiz_result');
        }

        // ---------------------------------------------------
        // 3. LOAD CURRENT QUESTION
        // ---------------------------------------------------
        $currentQuestionId = $queue[0]; // Take the first one
        $question = $questionRepository->find($currentQuestionId);

        // Safety check: If question was deleted from DB but is still in session
        if (!$question) {
            array_shift($queue);
            $session->set('quiz_queue', $queue);
            return $this->redirectToRoute('app_quiz_play');
        }

        // ---------------------------------------------------
        // 4. HANDLE ANSWER SUBMISSION
        // ---------------------------------------------------
        if ($request->isMethod('POST')) {
            $submittedChoiceId = $request->request->get('answer');
            $selectedChoice = $choiceRepository->find($submittedChoiceId);

            if ($selectedChoice && $selectedChoice->isCorrect()) {
                // CORRECT: Update Score
                $currentScore = $session->get('quiz_score');
                $session->set('quiz_score', $currentScore + $question->getXpValue());
                
                $this->addFlash('success', '✅ Correct! +' . $question->getXpValue() . ' XP');
            } else {
                // WRONG
                $this->addFlash('danger', '❌ Wrong answer!');
            }

            // REMOVE Question from Queue (so it doesn't show again)
            array_shift($queue); 
            $session->set('quiz_queue', $queue);

            return $this->redirectToRoute('app_quiz_play');
        }

        return $this->render('quiz/quiz_game/play.html.twig', [
            'question' => $question
        ]);
    }

    // Optional: Route to force restart the game (useful for testing)
    #[Route('/restart', name: 'app_quiz_restart')]
    public function restart(Request $request): Response
    {
        $request->getSession()->remove('quiz_queue');
        $request->getSession()->remove('quiz_score');
        return $this->redirectToRoute('app_quiz_play');
    }
}