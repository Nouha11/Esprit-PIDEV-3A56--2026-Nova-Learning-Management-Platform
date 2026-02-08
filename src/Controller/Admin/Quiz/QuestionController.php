<?php

namespace App\Controller\Admin\Quiz;

use App\Entity\Quiz\Question;
use App\Form\Quiz\QuestionType;
use App\Repository\Quiz\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/quiz/question')] // I added '/admin' here too
final class QuestionController extends AbstractController
{
    #[Route(name: 'app_quiz_question_index', methods: ['GET'])]
    public function index(QuestionRepository $questionRepository): Response
    {
        // 👇 UPDATED PATH: Points to admin/quiz/question_manager
        return $this->render('admin/quiz/question_manager/index.html.twig', [
            'questions' => $questionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_quiz_question_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($question);
            $entityManager->flush();

            return $this->redirectToRoute('app_quiz_show', ['id' => $question->getQuiz()->getId()], Response::HTTP_SEE_OTHER);
        }

        // 👇 UPDATED PATH
        return $this->render('admin/quiz/question_manager/new.html.twig', [
            'question' => $question,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_quiz_question_show', methods: ['GET'])]
    public function show(Question $question): Response
    {
        // 👇 UPDATED PATH
        return $this->render('admin/quiz/question_manager/show.html.twig', [
            'question' => $question,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_quiz_question_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Question $question, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_quiz_show', ['id' => $question->getQuiz()->getId()], Response::HTTP_SEE_OTHER);
        }

        // 👇 UPDATED PATH
        return $this->render('admin/quiz/question_manager/edit.html.twig', [
            'question' => $question,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_quiz_question_delete', methods: ['POST'])]
    public function delete(Request $request, Question $question, EntityManagerInterface $entityManager): Response
    {
        $quizId = $question->getQuiz()->getId();

        if ($this->isCsrfTokenValid('delete'.$question->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($question);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_quiz_show', ['id' => $quizId], Response::HTTP_SEE_OTHER);
    }
}