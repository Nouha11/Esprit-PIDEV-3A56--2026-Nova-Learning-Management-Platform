<?php
namespace App\Controller\Front\Game;

use App\Service\game\RewardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/rewards')]
//#[IsGranted('ROLE_STUDENT')]
class RewardController extends AbstractController
{
    public function __construct(
        private RewardService $rewardService
    ) {
    }

    /**
    * View my earned rewards
    */
    #[Route('/my-rewards', name: 'front_reward_my_rewards', methods: ['GET'])]
    public function myRewards(): Response
    {
        // TODO: Fetch user's earned rewards from database
        // For now, show all available rewards
        $rewards = $this->rewardService->getActiveRewards();
        return $this->render('front/reward/my_rewards.html.twig', [
            'rewards' => $rewards,
        ]);
    }

    /**
    * View all available rewards (gallery)
    */
    #[Route('/browse', name: 'front_reward_browse', methods: ['GET'])]
    public function browse(): Response
    {
        $rewards = $this->rewardService->getActiveRewards();
        return $this->render('front/reward/browse.html.twig', [
            'rewards' => $rewards,
        ]);
    }
}