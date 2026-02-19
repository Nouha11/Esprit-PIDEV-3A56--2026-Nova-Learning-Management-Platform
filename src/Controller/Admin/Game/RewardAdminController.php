<?php
namespace App\Controller\Admin\Game;

use App\Entity\Gamification\Reward;
use App\Form\Admin\RewardFormType;
use App\Repository\Gamification\RewardRepository;
use App\Service\game\RewardService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/rewards')]
#[IsGranted('ROLE_ADMIN')]
class RewardAdminController extends AbstractController
{
    public function __construct(
        private RewardService $rewardService,
        private RewardRepository $rewardRepository,
        private PaginatorInterface $paginator
    ) {
    }

    /**
    * List all rewards
    */
    #[Route('', name: 'admin_reward_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $query = $this->rewardRepository->createQueryBuilder('r')
            ->orderBy('r.id', 'DESC')
            ->getQuery();

        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10 // 10 rewards per page
        );

        return $this->render('admin/reward/index.html.twig', [
            'rewards' => $pagination,
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
    public function delete(Request $request, Reward $reward, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reward->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($reward);
                $entityManager->flush();
                
                $this->addFlash('success', 'Reward deleted successfully!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error deleting reward: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Invalid security token.');
        }
        
        return $this->redirectToRoute('admin_reward_index');
    }

    #[Route('/{id}', name: 'admin_reward_show', methods: ['GET'])]
    public function show(Reward $reward): Response
    {
        return $this->render('admin/reward/show.html.twig', [
            'reward' => $reward,
            'games' => $reward->getGames(), // Games offering this reward
        ]);
    }
    
}