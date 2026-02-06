<?php
namespace App\Controller\Admin\Game;

use App\Entity\Gamification\Reward;
use App\Form\Admin\RewardFormType;
use App\Repository\Gamification\RewardRepository;
use App\Service\game\RewardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/rewards')]
//#[IsGranted('ROLE_ADMIN')]
class RewardAdminController extends AbstractController
{
    public function __construct(
        private RewardService $rewardService,
        private RewardRepository $rewardRepository
    ) {
    }

    /**
    * List all rewards
    */
    #[Route('', name: 'admin_reward_index', methods: ['GET'])]
    public function index(): Response
    {
        $rewards = $this->rewardRepository->findAll();
        return $this->render('admin/reward/index.html.twig', [
        'rewards' => $rewards,
        ]);
    }

    /**
    * Add new reward
    */
    #[Route('/new', name: 'admin_reward_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $reward = new Reward();
        $form = $this->createForm(RewardFormType::class, $reward);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->rewardService->createReward($reward);
            $this->addFlash('success', 'Reward created successfully!');
            return $this->redirectToRoute('admin_reward_index');
        }
        return $this->render('admin/reward/new.html.twig', [
        'form' => $form,
        ]);
    }

    /**
    * Edit reward
    */
    #[Route('/{id}/edit', name: 'admin_reward_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reward $reward): Response
    {
        $form = $this->createForm(RewardFormType::class, $reward);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->rewardService->updateReward($reward);
            $this->addFlash('success', 'Reward updated successfully!');
            return $this->redirectToRoute('admin_reward_index');
        }
        return $this->render('admin/reward/edit.html.twig', [
        'form' => $form,
        'reward' => $reward,
        ]);
    }

    /**
    * Delete reward
    */
    #[Route('/{id}/delete', name: 'admin_reward_delete', methods: ['POST'])]
    public function delete(Request $request, Reward $reward): Response
    {
    if ($this->isCsrfTokenValid('delete'.$reward->getId(), $request->request->get('_token'))) {
        $this->rewardService->deleteReward($reward);
        $this->addFlash('success', 'Reward deleted successfully!');
    }
    return $this->redirectToRoute('admin_reward_index');
    }
}