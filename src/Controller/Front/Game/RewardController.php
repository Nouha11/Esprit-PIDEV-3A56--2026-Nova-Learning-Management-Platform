<?php
namespace App\Controller\Front\Game;

use App\Repository\Gamification\StudentRewardRepository;
use App\Service\game\RewardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/rewards')]
#[IsGranted('ROLE_STUDENT')]
class RewardController extends AbstractController
{
    public function __construct(
        private RewardService $rewardService,
        private StudentRewardRepository $studentRewardRepository
    ) {
    }

    /**
    * View my earned rewards
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

        // Fetch student's earned rewards
        $earnedRewards = $this->studentRewardRepository->findByStudent($student);
        $unviewedCount = count($this->studentRewardRepository->findUnviewedByStudent($student));

        // Mark all as viewed
        foreach ($earnedRewards as $reward) {
            if (!$reward->isViewed()) {
                $reward->setIsViewed(true);
            }
        }
        $this->getDoctrine()->getManager()->flush();

        return $this->render('front/reward/my_rewards.html.twig', [
            'earnedRewards' => $earnedRewards,
            'student' => $student,
            'unviewedCount' => $unviewedCount,
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
        $earnedRewardIds = [];

        if ($student) {
            $earnedRewards = $this->studentRewardRepository->findByStudent($student);
            $earnedRewardIds = array_map(
                fn($sr) => $sr->getReward()->getId(),
                $earnedRewards
            );
        }

        return $this->render('front/reward/browse.html.twig', [
            'rewards' => $allRewards,
            'earnedRewardIds' => $earnedRewardIds,
            'student' => $student,
        ]);
    }
}