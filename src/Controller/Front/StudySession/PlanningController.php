<?php

namespace App\Controller\Front\StudySession;

use App\Entity\StudySession\Planning;
use App\Entity\StudySession\Course;
use App\Form\StudySession\PlanningType;
use App\Service\StudySession\PlanningService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/planning')]
#[IsGranted('ROLE_STUDENT')]
class PlanningController extends AbstractController
{
    public function __construct(
        private PlanningService $planningService
    ) {}

    #[Route('/', name: 'planning_index')]
    public function index(Request $request): Response
    {
        $status = $request->query->get('status');
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');

        $filters = array_filter([
            'status' => $status,
            'startDate' => $startDate ? new \DateTime($startDate) : null,
            'endDate' => $endDate ? new \DateTime($endDate) : null,
        ]);

        $plannings = empty($filters) 
            ? $this->planningService->findAll()
            : $this->planningService->findByFilters($filters);

        return $this->render('front/planning/index.html.twig', [
            'plannings' => $plannings,
            'currentStatus' => $status,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    #[Route('/new/{course}', name: 'planning_new')]
    public function new(
        Course $course,
        Request $request
    ): Response {
        $planning = new Planning();
        $planning->setCourse($course);
        $planning->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(PlanningType::class, $planning);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->planningService->create($planning);
                $this->addFlash('success', 'Study session planned successfully!');
                return $this->redirectToRoute('planning_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to plan study session: ' . $e->getMessage());
            }
        }

        return $this->render('front/planning/new.html.twig', [
            'form' => $form->createView(),
            'course' => $course
        ]);
    }

    #[Route('/{id}', name: 'planning_show', requirements: ['id' => '\d+'])]
    public function show(Planning $planning): Response
    {
        return $this->render('front/planning/show.html.twig', [
            'planning' => $planning
        ]);
    }
}
