<?php

namespace App\Controller\Admin\Quiz;

use App\Entity\Quiz;
use App\Form\Admin\Quiz\QuizType;
use App\Repository\QuizRepository;
use App\Service\Quiz\QuizStatisticsService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route(path: '/{prefix}/quiz', requirements: ['prefix' => 'admin|tutor'])]
final class QuizController extends AbstractController
{
    #[Route(name: 'app_quiz_index', methods: ['GET'])]
    public function index(QuizRepository $quizRepository, PaginatorInterface $paginator, Request $request, string $prefix): Response
    {
        $templatePrefix = $prefix === 'admin' ? 'admin/' : '';
        
        // Get filter data from query parameters
        $filters = [];
        $sortBy = $request->query->get('sortBy', 'title');
        $sortOrder = $request->query->get('sortOrder', 'ASC');
        
        // Build filters array
        if ($search = $request->query->get('search')) {
            $filters['search'] = $search;
        }
        if ($minQuestions = $request->query->get('minQuestions')) {
            $filters['minQuestions'] = (int)$minQuestions;
        }
        if ($maxQuestions = $request->query->get('maxQuestions')) {
            $filters['maxQuestions'] = (int)$maxQuestions;
        }
        
        // Get query builder with filters and sorting
        $queryBuilder = $quizRepository->findWithFiltersAndSort($filters, $sortBy, $sortOrder);
        
        // Paginate the results
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            12 // items per page
        );
        
        // Get statistics for the UI
        $statistics = $quizRepository->getQuizStatistics();
        
        return $this->render($templatePrefix . 'quiz/index.html.twig', [
            'pagination' => $pagination,
            'statistics' => $statistics,
            'currentFilters' => $filters,
            'currentSort' => ['by' => $sortBy, 'order' => $sortOrder],
            'current_prefix' => $prefix
        ]);
    }

    #[Route('/new', name: 'app_quiz_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, string $prefix): Response
    {
        $quiz = new Quiz();
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($quiz);
            $entityManager->flush();

            return $this->redirectToRoute('app_quiz_show', [
                'id' => $quiz->getId(),
                'prefix' => $prefix
            ], Response::HTTP_SEE_OTHER);
        }

        $templatePrefix = $prefix === 'admin' ? 'admin/' : '';
        
        return $this->render($templatePrefix . 'quiz/new.html.twig', [
            'quiz' => $quiz,
            'form' => $form,
        ]);
    }

    #[Route('/statistics', name: 'app_quiz_statistics', methods: ['GET'])]
    public function statistics(
        QuizStatisticsService $statisticsService, 
        ChartBuilderInterface $chartBuilder,
        string $prefix
    ): Response
    {
        $templatePrefix = $prefix === 'admin' ? 'admin/' : '';
        
        $stats = $statisticsService->getStatistics();
        $difficultyDistribution = $statisticsService->getDifficultyDistribution();
        $reportStatusDistribution = $statisticsService->getReportStatusDistribution();
        
        // Create Difficulty Distribution Doughnut Chart
        $difficultyChart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $difficultyChart->setData([
            'labels' => ['Easy', 'Medium', 'Hard'],
            'datasets' => [
                [
                    'label' => 'Questions',
                    'data' => [
                        $difficultyDistribution['Easy'],
                        $difficultyDistribution['Medium'],
                        $difficultyDistribution['Hard']
                    ],
                    'backgroundColor' => [
                        'rgba(40, 167, 69, 0.8)',   // Green for Easy
                        'rgba(255, 193, 7, 0.8)',   // Yellow for Medium
                        'rgba(220, 53, 69, 0.8)'    // Red for Hard
                    ],
                    'borderColor' => [
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    'borderWidth' => 2,
                ],
            ],
        ]);
        
        $difficultyChart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 20,
                        'font' => [
                            'size' => 14
                        ]
                    ]
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            const label = context.label || "";
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return label + ": " + value + " (" + percentage + "%)";
                        }'
                    ]
                ]
            ]
        ]);
        
        // Create Report Status Bar Chart
        $reportChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $reportChart->setData([
            'labels' => ['Pending', 'Resolved', 'Dismissed'],
            'datasets' => [
                [
                    'label' => 'Reports',
                    'data' => [
                        $reportStatusDistribution['pending'],
                        $reportStatusDistribution['resolved'],
                        $reportStatusDistribution['dismissed']
                    ],
                    'backgroundColor' => [
                        'rgba(255, 193, 7, 0.8)',   // Yellow for Pending
                        'rgba(40, 167, 69, 0.8)',   // Green for Resolved
                        'rgba(108, 117, 125, 0.8)'  // Gray for Dismissed
                    ],
                    'borderColor' => [
                        'rgba(255, 193, 7, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(108, 117, 125, 1)'
                    ],
                    'borderWidth' => 2,
                    'borderRadius' => 8,
                ],
            ],
        ]);
        
        $reportChart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                        'font' => [
                            'size' => 12
                        ]
                    ],
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.05)'
                    ]
                ],
                'x' => [
                    'ticks' => [
                        'font' => [
                            'size' => 12
                        ]
                    ],
                    'grid' => [
                        'display' => false
                    ]
                ]
            ],
            'plugins' => [
                'legend' => [
                    'display' => false
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.label + ": " + context.parsed.y + " report(s)";
                        }'
                    ]
                ]
            ]
        ]);
        
        return $this->render($templatePrefix . 'quiz/statistics.html.twig', [
            'stats' => $stats,
            'difficultyChart' => $difficultyChart,
            'reportChart' => $reportChart,
        ]);
    }

    #[Route('/{id}', name: 'app_quiz_show', methods: ['GET'])]
    public function show(Quiz $quiz, string $prefix): Response
    {
        $templatePrefix = $prefix === 'admin' ? 'admin/' : '';
        
        return $this->render($templatePrefix . 'quiz/show.html.twig', [
            'quiz' => $quiz,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_quiz_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quiz $quiz, EntityManagerInterface $entityManager, string $prefix): Response
    {
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_quiz_index', ['prefix' => $prefix], Response::HTTP_SEE_OTHER);
        }

        $templatePrefix = $prefix === 'admin' ? 'admin/' : '';
        
        return $this->render($templatePrefix . 'quiz/edit.html.twig', [
            'quiz' => $quiz,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_quiz_delete', methods: ['POST'])]
    public function delete(Request $request, Quiz $quiz, EntityManagerInterface $entityManager, string $prefix): Response
    {
        if ($this->isCsrfTokenValid('delete'.$quiz->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($quiz);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_quiz_index', ['prefix' => $prefix], Response::HTTP_SEE_OTHER);
    }
}