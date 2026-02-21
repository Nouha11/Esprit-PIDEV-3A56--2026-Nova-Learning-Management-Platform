<?php

namespace App\Controller\Admin\Quiz;

use App\Entity\Quiz\QuizReport;
use App\Repository\Quiz\QuizReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/{prefix}/quiz-reports', requirements: ['prefix' => 'admin|tutor'])]
class QuizReportController extends AbstractController
{
    #[Route('/', name: 'app_quiz_reports_index', methods: ['GET'])]
    public function index(QuizReportRepository $reportRepository, string $prefix): Response
    {
        $templatePrefix = $prefix === 'admin' ? 'admin/' : '';
        
        $pendingReports = $reportRepository->findPendingReports();
        $resolvedReports = $reportRepository->findResolvedReports();

        return $this->render($templatePrefix . 'quiz/reports/index.html.twig', [
            'pendingReports' => $pendingReports,
            'resolvedReports' => $resolvedReports,
        ]);
    }

    #[Route('/{id}', name: 'app_quiz_reports_show', methods: ['GET'])]
    public function show(QuizReport $report, string $prefix): Response
    {
        $templatePrefix = $prefix === 'admin' ? 'admin/' : '';

        return $this->render($templatePrefix . 'quiz/reports/show.html.twig', [
            'report' => $report,
        ]);
    }

    #[Route('/{id}/resolve', name: 'app_quiz_reports_resolve', methods: ['POST'])]
    public function resolve(
        QuizReport $report,
        Request $request,
        EntityManagerInterface $entityManager,
        string $prefix
    ): Response {
        $action = $request->request->get('action');
        $adminNotes = $request->request->get('admin_notes');

        if ($action === 'resolve') {
            $report->setStatus('resolved');
        } elseif ($action === 'dismiss') {
            $report->setStatus('dismissed');
        }

        $report->setResolvedAt(new \DateTime());
        $report->setResolvedBy($this->getUser());
        $report->setAdminNotes($adminNotes);

        $entityManager->flush();

        $this->addFlash('success', 'Report has been ' . $report->getStatus() . '.');
        return $this->redirectToRoute('app_quiz_reports_index', ['prefix' => $prefix]);
    }

    #[Route('/{id}/delete-quiz', name: 'app_quiz_reports_delete_quiz', methods: ['POST'])]
    public function deleteQuiz(
        QuizReport $report,
        Request $request,
        EntityManagerInterface $entityManager,
        string $prefix
    ): Response {
        if ($this->isCsrfTokenValid('delete-quiz-' . $report->getId(), $request->request->get('_token'))) {
            $quiz = $report->getQuiz();
            
            // Mark report as resolved
            $report->setStatus('resolved');
            $report->setResolvedAt(new \DateTime());
            $report->setResolvedBy($this->getUser());
            $report->setAdminNotes('Quiz deleted due to report');
            
            $entityManager->flush();
            
            // Delete the quiz
            $entityManager->remove($quiz);
            $entityManager->flush();

            $this->addFlash('success', 'Quiz has been deleted and report marked as resolved.');
        }

        return $this->redirectToRoute('app_quiz_reports_index', ['prefix' => $prefix]);
    }
}
