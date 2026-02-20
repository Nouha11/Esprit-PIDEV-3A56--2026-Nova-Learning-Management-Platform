<?php

namespace App\Controller\Front\StudySession;

use App\Entity\StudySession\Note;
use App\Entity\StudySession\StudySession;
use App\Form\StudySession\NoteType;
use App\Repository\StudySession\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/study-session/{studySessionId}/note')]
#[IsGranted('ROLE_STUDENT')]
class NoteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private NoteRepository $noteRepository,
        private CsrfTokenManagerInterface $csrfTokenManager
    ) {}

    #[Route('/create', name: 'note_create', methods: ['GET', 'POST'])]
    public function create(int $studySessionId, Request $request): Response
    {
        $studySession = $this->entityManager->getRepository(StudySession::class)->find($studySessionId);
        
        if (!$studySession) {
            throw $this->createNotFoundException('Study session not found.');
        }

        // Ensure user can only add notes to their own sessions
        if ($studySession->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot add notes to this study session.');
        }

        $note = new Note();
        $note->setStudySession($studySession);
        
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->persist($note);
                $this->entityManager->flush();

                $this->addFlash('success', 'Note created successfully.');
                return $this->redirectToRoute('study_session_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to create note: ' . $e->getMessage());
            }
        }

        return $this->render('front/study_session/note_form.html.twig', [
            'form' => $form->createView(),
            'note' => $note,
            'study_session' => $studySession,
        ]);
    }

    #[Route('/{id}/edit', name: 'note_edit', methods: ['GET', 'POST'])]
    public function edit(int $studySessionId, int $id, Request $request): Response
    {
        $note = $this->noteRepository->find($id);
        
        if (!$note) {
            throw $this->createNotFoundException('Note not found.');
        }

        // Ensure the note belongs to the specified study session
        if ($note->getStudySession()->getId() !== $studySessionId) {
            throw $this->createNotFoundException('Note does not belong to this study session.');
        }

        // Ensure user can only edit notes from their own sessions
        if ($note->getStudySession()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot edit this note.');
        }

        // Preserve the original creation timestamp
        $originalCreatedAt = $note->getCreatedAt();
        
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Ensure createdAt is preserved
                $note->setCreatedAt($originalCreatedAt);
                
                $this->entityManager->flush();

                $this->addFlash('success', 'Note updated successfully.');
                return $this->redirectToRoute('study_session_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to update note: ' . $e->getMessage());
            }
        }

        return $this->render('front/study_session/note_form.html.twig', [
            'form' => $form->createView(),
            'note' => $note,
            'study_session' => $note->getStudySession(),
        ]);
    }

    #[Route('/{id}/delete', name: 'note_delete', methods: ['POST'])]
    public function delete(int $studySessionId, int $id, Request $request): Response
    {
        $note = $this->noteRepository->find($id);
        
        if (!$note) {
            throw $this->createNotFoundException('Note not found.');
        }

        // Ensure the note belongs to the specified study session
        if ($note->getStudySession()->getId() !== $studySessionId) {
            throw $this->createNotFoundException('Note does not belong to this study session.');
        }

        // Ensure user can only delete notes from their own sessions
        if ($note->getStudySession()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot delete this note.');
        }

        // CSRF validation
        $token = $request->request->get('_token');
        if (!$this->csrfTokenManager->isTokenValid(
            new \Symfony\Component\Security\Csrf\CsrfToken('delete_note_' . $note->getId(), $token)
        )) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('study_session_index');
        }

        try {
            $this->entityManager->remove($note);
            $this->entityManager->flush();

            $this->addFlash('success', 'Note deleted successfully.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to delete note: ' . $e->getMessage());
        }

        return $this->redirectToRoute('study_session_index');
    }

    #[Route('/search', name: 'note_search', methods: ['GET'])]
    public function search(int $studySessionId, Request $request): Response
    {
        $keyword = $request->query->get('keyword', '');
        
        $studySession = $this->entityManager->getRepository(StudySession::class)->find($studySessionId);
        
        if (!$studySession) {
            throw $this->createNotFoundException('Study session not found.');
        }

        // Ensure user can only search notes from their own sessions
        if ($studySession->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot search notes for this study session.');
        }

        $notes = [];
        if (!empty($keyword)) {
            // Search all notes by keyword, then filter by user's sessions
            $allNotes = $this->noteRepository->searchByKeyword($keyword);
            
            // Filter to only include notes from the current user's sessions
            $notes = array_filter($allNotes, function($note) {
                return $note->getStudySession()->getUser() === $this->getUser();
            });
        }

        return $this->render('front/study_session/notes.html.twig', [
            'notes' => $notes,
            'keyword' => $keyword,
            'study_session' => $studySession,
        ]);
    }
}
