<?php

namespace App\Controller\Quiz;

use Doctrine\ORM\EntityManagerInterface; 
use App\Entity\users\User; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/quiz/result')]
class QuizResultController extends AbstractController
{
    #[Route('/', name: 'app_quiz_result')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        
        // 1. Get the score from the session
        $score = $session->get('quiz_score', 0);

        // 2. Get the current User
        /** @var User $user */  // trust me bro
        $user = $this->getUser();

        // 3. SAVE TO DATABASE 
        if ($user && $score > 0) {
            
            // Calculate new total
            $currentXp = $user->getXp() ?? 0;
            $newTotal = $currentXp + $score;
            
            // Update User
            $user->setXp($newTotal);

            // Save changes
            $entityManager->persist($user);
            $entityManager->flush();

            // Optional: Confirm save
            $this->addFlash('success', '🏆 Progress Saved! +' . $score . ' XP added to your profile.');
        }

        // 4. Clear the session (Reset the game)
        $session->remove('quiz_queue');
        $session->remove('quiz_score');

        return $this->render('quiz/quiz_result/index.html.twig', [
            'score' => $score
        ]);
    }
}