<?php

namespace App\Controller\Admin;

use App\Entity\Gamification\Reward;
use App\Form\Admin\gamification\LevelMilestoneType;
use App\Repository\Gamification\RewardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/level-milestones')]
#[IsGranted('ROLE_ADMIN')]
class LevelMilestoneController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private RewardRepository $rewardRepository
    ) {
    }

    #[Route('', name: 'admin_level_milestone_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // Redirect to rewards page with milestones tab active
        return $this->redirectToRoute('admin_reward_index', ['tab' => 'milestones']);
    }

    #[Route('/new', name: 'admin_level_milestone_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $milestone = new Reward();
        $milestone->setType('LEVEL_MILESTONE');
        $milestone->setIsActive(true);

        $form = $this->createForm(LevelMilestoneType::class, $milestone);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $iconFile = $form->get('icon')->getData();
            if ($iconFile) {
                $newFilename = $this->handleFileUpload($iconFile);
                $milestone->setIcon($newFilename);
            }

            $this->em->persist($milestone);
            $this->em->flush();

            $this->addFlash('success', 'Level milestone created successfully!');
            return $this->redirectToRoute('admin_reward_index', ['tab' => 'milestones']);
        }

        return $this->render('admin/level_milestone/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_level_milestone_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reward $milestone): Response
    {
        if ($milestone->getType() !== 'LEVEL_MILESTONE') {
            throw $this->createNotFoundException('This is not a level milestone');
        }

        $form = $this->createForm(LevelMilestoneType::class, $milestone);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $iconFile = $form->get('icon')->getData();
            if ($iconFile) {
                // Delete old icon if exists
                if ($milestone->getIcon()) {
                    $oldIconPath = $this->getParameter('kernel.project_dir') . '/public/uploads/rewards/' . $milestone->getIcon();
                    if (file_exists($oldIconPath)) {
                        unlink($oldIconPath);
                    }
                }
                
                $newFilename = $this->handleFileUpload($iconFile);
                $milestone->setIcon($newFilename);
            }

            $this->em->flush();

            $this->addFlash('success', 'Level milestone updated successfully!');
            return $this->redirectToRoute('admin_reward_index', ['tab' => 'milestones']);
        }

        return $this->render('admin/level_milestone/edit.html.twig', [
            'form' => $form->createView(),
            'milestone' => $milestone,
        ]);
    }

    /**
     * Handle file upload for milestone icons
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

    #[Route('/{id}', name: 'admin_level_milestone_show', methods: ['GET'])]
    public function show(Reward $milestone): Response
    {
        if ($milestone->getType() !== 'LEVEL_MILESTONE') {
            throw $this->createNotFoundException('This is not a level milestone');
        }

        // Get students who earned this milestone
        $students = $milestone->getStudents();

        return $this->render('admin/level_milestone/show.html.twig', [
            'milestone' => $milestone,
            'students' => $students,
        ]);
    }

    #[Route('/{id}/toggle-status', name: 'admin_level_milestone_toggle_status', methods: ['POST'])]
    public function toggleStatus(Reward $milestone): Response
    {
        if ($milestone->getType() !== 'LEVEL_MILESTONE') {
            throw $this->createNotFoundException('This is not a level milestone');
        }

        $milestone->setIsActive(!$milestone->isActive());
        $this->em->flush();
        
        $status = $milestone->isActive() ? 'activated' : 'deactivated';
        $this->addFlash('success', sprintf('Level milestone "%s" has been %s successfully!', $milestone->getName(), $status));
        
        return $this->redirectToRoute('admin_reward_index', ['tab' => 'milestones']);
    }

    #[Route('/{id}/delete', name: 'admin_level_milestone_delete', methods: ['POST'])]
    public function delete(Request $request, Reward $milestone): Response
    {
        if ($milestone->getType() !== 'LEVEL_MILESTONE') {
            throw $this->createNotFoundException('This is not a level milestone');
        }

        if ($this->isCsrfTokenValid('delete' . $milestone->getId(), $request->request->get('_token'))) {
            $this->em->remove($milestone);
            $this->em->flush();

            $this->addFlash('success', 'Level milestone deleted successfully!');
        }

        return $this->redirectToRoute('admin_reward_index', ['tab' => 'milestones']);
    }
}
