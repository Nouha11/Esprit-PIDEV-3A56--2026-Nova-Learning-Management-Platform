<?php

namespace App\Controller\Front\Quiz;

use Doctrine\ORM\EntityManagerInterface; 
use App\Entity\users\User; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/game/result')]
final class QuizResultController extends AbstractController
{
    #[Route('/', name: 'app_quiz_result')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        
        // 1. Get stats from session
        $score = $session->get('quiz_score', 0);
        $totalQuestions = $session->get('quiz_total', 0);

        // 2. Update User XP
        /** @var User $user */
        $user = $this->getUser();

        if ($user && $score > 0) {
            $currentXp = $user->getXp() ?? 0;
            $user->setXp($currentXp + $score);

            $entityManager->persist($user);
            $entityManager->flush();

            // Only show flash message once per game
            if ($session->has('quiz_queue')) {
                $this->addFlash('success', '🏆 Game Over! You earned ' . $score . ' XP.');
            }
        }

        // 3. Clear the session (Reset the game)
        $session->remove('quiz_queue');
        $session->remove('quiz_score');
        $session->remove('current_quiz_id');

        // 4. Render Result Page
        return $this->render('front/quiz/result/index.html.twig', [
            'score' => $score,
            'total' => $totalQuestions
        ]);
    }
}