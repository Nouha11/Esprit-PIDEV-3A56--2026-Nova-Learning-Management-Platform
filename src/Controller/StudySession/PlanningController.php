<?php

namespace App\Controller\StudySession;

use App\Entity\StudySession\Planning;
use App\Entity\StudySession\Course;
use App\Form\StudySession\PlanningType;
use App\Repository\StudySession\PlanningRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/planning')]
#[IsGranted('ROLE_STUDENT')]
class PlanningController extends AbstractController
{
    #[Route('/', name: 'planning_index')]
    public function index(
        PlanningRepository $repository
    ): Response {
        return $this->render('planning/index.html.twig', [
            'plannings' => $repository->findAll()
        ]);
    }

    #[Route('/new/{course}', name: 'planning_new')]
    public function new(
        Course $course,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $planning = new Planning();
        $planning->setCourse($course); //a3ml relation maa course that's the solution
        $planning->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(PlanningType::class, $planning);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($planning);
            $em->flush();

            $this->addFlash('success', 'Study session planned');
            return $this->redirectToRoute('planning_index');
        }

        return $this->render('planning/new.html.twig', [
            'form' => $form->createView(),
            'course' => $course
        ]);
    }
}
