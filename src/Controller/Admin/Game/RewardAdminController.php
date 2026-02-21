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
    * List all rewards with Ajax filters
    */
    #[Route('', name: 'admin_reward_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // Get filter parameters
        $search = $request->query->get('search', '');
        $type = $request->query->get('type', '');
        $status = $request->query->get('status', '');

        // Build query with filters
        $queryBuilder = $this->rewardRepository->createQueryBuilder('r')
            ->where('r.type != :levelMilestone')
            ->setParameter('levelMilestone', 'LEVEL_MILESTONE');

        // Search filter
        if ($search) {
            $queryBuilder->andWhere('r.name LIKE :search OR r.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Type filter
        if ($type) {
            $queryBuilder->andWhere('r.type = :type')
                ->setParameter('type', $type);
        }

        // Status filter
        if ($status === 'active') {
            $queryBuilder->andWhere('r.isActive = true');
        } elseif ($status === 'inactive') {
            $queryBuilder->andWhere('r.isActive = false');
        }

        $queryBuilder->orderBy('r.id', 'DESC');

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10 // 10 rewards per page
        );

        // Get level milestones
        $milestones = $this->rewardRepository->createQueryBuilder('r')
            ->where('r.type = :type')
            ->setParameter('type', 'LEVEL_MILESTONE')
            ->orderBy('r.requiredLevel', 'ASC')
            ->getQuery()
            ->getResult();

        // Ajax request - return partial
        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/reward/_rewards_table.html.twig', [
                'rewards' => $pagination,
            ]);
        }

        // Full page render
        return $this->render('admin/reward/index.html.twig', [
            'rewards' => $pagination,
            'milestones' => $milestones,
            'search' => $search,
            'type' => $type,
            'status' => $status,
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
            // Handle file upload
            $iconFile = $form->get('iconFile')->getData();
            if ($iconFile) {
                $newFilename = $this->handleFileUpload($iconFile);
                $reward->setIcon($newFilename);
            }

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
    public function edit(Request $request, Reward $reward, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RewardFormType::class, $reward);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $iconFile = $form->get('iconFile')->getData();
            if ($iconFile) {
                // Delete old icon if exists
                if ($reward->getIcon()) {
                    $oldIconPath = $this->getParameter('kernel.project_dir') . '/public/uploads/rewards/' . $reward->getIcon();
                    if (file_exists($oldIconPath)) {
                        unlink($oldIconPath);
                    }
                }
                
                $newFilename = $this->handleFileUpload($iconFile);
                $reward->setIcon($newFilename);
            }

            $entityManager->flush();
            
            $this->addFlash('success', 'Reward updated successfully!');
            return $this->redirectToRoute('admin_reward_index');
        }
        return $this->render('admin/reward/edit.html.twig', [
        'form' => $form,
        'reward' => $reward,
        ]);
    }


    /**
     * Toggle reward active status
     */
    #[Route('/{id}/toggle-status', name: 'admin_reward_toggle_status', methods: ['POST'])]
    public function toggleStatus(Reward $reward, EntityManagerInterface $entityManager): Response
    {
        $reward->setIsActive(!$reward->isActive());
        $entityManager->flush();
        
        $status = $reward->isActive() ? 'activated' : 'deactivated';
        $this->addFlash('success', sprintf('Reward "%s" has been %s successfully!', $reward->getName(), $status));
        
        return $this->redirectToRoute('admin_reward_index');
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
    public function show(Reward $reward, Request $request): Response
    {
        // Paginate students who earned this reward
        $studentsQuery = $reward->getStudents();
        
        $studentsPagination = $this->paginator->paginate(
            $studentsQuery,
            $request->query->getInt('student_page', 1),
            10, // 10 students per page
            ['pageParameterName' => 'student_page']
        );
        
        return $this->render('admin/reward/show.html.twig', [
            'reward' => $reward,
            'games' => $reward->getGames(), // Games offering this reward
            'students' => $studentsPagination,
        ]);
    }

    /**
     * Handle file upload for reward icons
     */
    private function handleFileUpload($file): string
    {
        $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/rewards';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadsDirectory)) {
            mkdir($uploadsDirectory, 0777, true);
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $file->move($uploadsDirectory, $newFilename);

        return $newFilename;
    }
    
}