<?php

namespace App\Controller\Admin\StudySession;

use App\Entity\StudySession\Planning;
use App\Form\Admin\PlanningStatusFormType;
use App\Repository\StudySession\PlanningRepository;
use App\Service\StudySession\PlanningService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/planning')]
//#[IsGranted('ROLE_ADMIN')]
class AdminPlanningController extends AbstractController
{
    public function __construct(
        private PlanningService $planningService,
        private PlanningRepository $planningRepository
    ) {
    }

    /**
     * List all planning sessions with optional filters
     */
    #[Route('', name: 'admin_planning_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $status = $request->query->get('status');
        $dateFrom = $request->query->get('dateFrom') 
            ? new \DateTimeImmutable($request->query->get('dateFrom')) 
            : null;
        $dateTo = $request->query->get('dateTo') 
            ? new \DateTimeImmutable($request->query->get('dateTo')) 
            : null;

        #$plannings = $this->planningService->findByFilters($status, $dateFrom, $dateTo);

        $plannings = $this->planningService->findByFilters($filters ?? []);

        return $this->render('admin/planning/index.html.twig', [
            'plannings' => $plannings,
            'current_status' => $status,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
    }

    /**
     * Show planning details
     */
    #[Route('/{id}', name: 'admin_planning_show', methods: ['GET'])]
    public function show(Planning $planning): Response
    {
        return $this->render('admin/planning/show.html.twig', [
            'planning' => $planning,
        ]);
    }

    /**
     * Edit planning status
     */
    #[Route('/{id}/edit-status', name: 'admin_planning_edit_status', methods: ['GET', 'POST'])]
    public function editStatus(Request $request, Planning $planning): Response
    {
        $form = $this->createForm(PlanningStatusFormType::class, $planning);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $newStatus = $form->get('status')->getData();
                $this->planningService->updateStatus($planning, $newStatus);
                $this->addFlash('success', 'Planning status updated successfully!');
                return $this->redirectToRoute('admin_planning_show', ['id' => $planning->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to update status: ' . $e->getMessage());
            }
        }

        return $this->render('admin/planning/edit_status.html.twig', [
            'form' => $form,
            'planning' => $planning,
        ]);
    }

    /**
     * Cancel planning session
     */
    #[Route('/{id}/cancel', name: 'admin_planning_cancel', methods: ['POST'])]
    public function cancel(Request $request, Planning $planning): Response
    {
        if ($this->isCsrfTokenValid('cancel'.$planning->getId(), $request->request->get('_token'))) {
            try {
                $this->planningService->cancelPlanning($planning);
                $this->addFlash('success', 'Planning session cancelled successfully!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to cancel planning: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('admin_planning_index');
    }
}
