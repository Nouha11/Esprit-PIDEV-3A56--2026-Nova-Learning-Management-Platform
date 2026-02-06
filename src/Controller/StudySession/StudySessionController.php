<?php

namespace App\Controller\StudySession;

use App\Entity\StudySession\Planning;
use App\Entity\StudySession\Course;
use App\Repository\StudySession\StudySessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;




#[Route('/study-session')]
#[IsGranted('ROLE_STUDENT')]
class StudySessionController extends AbstractController
{
    #[Route('/complete/{planning}', name: 'study_complete', methods: ['POST'])]
    public function complete( Planning $planning, EntityManagerInterface $em ): Response 
        {

            if ($planning->getStatus() === 'COMPLETED') {
                $this->addFlash('warning', 'This session has already been completed.');
                return $this->redirectToRoute('planning_index');
            }

            $duration = $planning->getPlannedDuration() ?? 0;
            if ($duration <= 0) {
                $this->addFlash('error', 'Invalid planned duration.');
                return $this->redirectToRoute('planning_index');
            }

            $energyUsed = intdiv($duration, 10);
            $xpEarned = $duration * 2;

            $burnoutRisk = match (true) {
                $energyUsed > 80 => 'HIGH',
                $energyUsed > 40 => 'MODERATE',
                default => 'LOW'
            };

            $planning->setStatus(Planning::STATUS_COMPLETED);

            $em->flush();

            $this->addFlash(
                'info',
                "Session completed. XP: $xpEarned | Burnout risk: $burnoutRisk"
            );

            return $this->redirectToRoute('planning_index');
        }

}
