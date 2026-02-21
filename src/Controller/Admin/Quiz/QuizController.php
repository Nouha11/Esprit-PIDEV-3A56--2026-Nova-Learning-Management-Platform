<?php

namespace App\Controller\Admin\Quiz;

use App\Entity\Quiz;
use App\Form\Admin\Quiz\QuizType;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
            'currentSort' => ['by' => $sortBy, 'order' => $sortOrder]
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