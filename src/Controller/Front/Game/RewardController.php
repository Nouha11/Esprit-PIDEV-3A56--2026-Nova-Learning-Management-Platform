<?php
namespace App\Controller\Front\Game;

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

        return $this->render('front/reward/my_rewards.html.twig', [
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
        $student = $user->getStudentProfile();

        $allRewards = $this->rewardService->getActiveRewards();

        return $this->render('front/reward/browse.html.twig', [
            'rewards' => $allRewards,
            'student' => $student,
        ]);
    }
}
