<?php

namespace App\Controller\Front\Quiz;

use App\Entity\Quiz;
use App\Entity\Quiz\QuizReport;
use App\Form\Quiz\QuizReportType;
use App\Service\Quiz\QuizReportNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/game/quiz/report')]
#[IsGranted('ROLE_STUDENT')]
class QuizReportController extends AbstractController
{
    #[Route('/{id}', name: 'app_quiz_report', methods: ['GET', 'POST'])]
    public function report(
        Quiz $quiz,
        Request $request,
        EntityManagerInterface $entityManager,
        QuizReportNotificationService $notificationService
    ): Response {
        $report = new QuizReport();
        $report->setQuiz($quiz);
        $report->setReportedBy($this->getUser());

        $form = $this->createForm(QuizReportType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($report);
            $entityManager->flush();

            // Send notification to admins
            $notificationService->notifyAdminsOfNewReport($report);

            $this->addFlash('success', 'Thank you for your report. Our team will review it shortly.');
            return $this->redirectToRoute('app_front_quiz_index');
        }

        return $this->render('front/quiz/report.html.twig', [
            'quiz' => $quiz,
            'form' => $form,
        ]);
    }
}
