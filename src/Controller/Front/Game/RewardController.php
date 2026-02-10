<?php
namespace App\Controller\Front\Game;

use App\Entity\Gamification\Reward;
use App\Service\game\RewardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/rewards')]
class RewardController extends AbstractController
{
    public function __construct(
        private RewardService $rewardService
    ) {
    }

    /**
    * View my earned rewards (placeholder - rewards tracking removed)
    */
    #[Route('/my-rewards', name: 'front_reward_my_rewards', methods: ['GET'])]
    public function myRewards(): Response
    {
        $user = $this->getUser();
        $student = $user->getStudentProfile();

        if (!$student) {
            $this->addFlash('error', 'Student profile not found');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('front/game/my_rewards.html.twig', [
            'student' => $student,
        ]);
    }

    /**
    * View all available rewards (gallery)
    */
    #[Route('/browse', name: 'front_reward_browse', methods: ['GET'])]
    public function browse(): Response
    {
        $user = $this->getUser();
        $student = $user ? $user->getStudentProfile() : null;

        $allRewards = $this->rewardService->getActiveRewards();

        return $this->render('front/game/browse.html.twig', [
            'rewards' => $allRewards,
            'student' => $student,
        ]);
    }

    /**
     * View reward details and associated games
     */
    #[Route('/{id}', name: 'front_reward_show', methods: ['GET'])]
    public function show(Reward $reward): Response
    {
        $user = $this->getUser();
        $student = $user ? $user->getStudentProfile() : null;

        return $this->render('front/game/reward_show.html.twig', [
            'reward' => $reward,
            'games' => $reward->getGames(), // Games that offer this reward
            'student' => $student,
        ]);
    }
}
