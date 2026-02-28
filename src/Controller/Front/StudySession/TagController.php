<?php

namespace App\Controller\Front\StudySession;

use App\Entity\StudySession\Tag;
use App\Form\StudySession\TagType;
use App\Repository\StudySession\TagRepository;
use App\Repository\StudySession\StudySessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/tag')]
#[IsGranted('ROLE_STUDENT')]
class TagController extends AbstractController
{
    public function __construct(
        private TagRepository $tagRepository,
        private StudySessionRepository $studySessionRepository,
        private EntityManagerInterface $entityManager,
        private CsrfTokenManagerInterface $csrfTokenManager
    ) {}

    #[Route('/', name: 'tag_index', methods: ['GET'])]
    public function index(): Response
    {
        // Get all tags with usage counts
        $tagsWithCounts = $this->tagRepository->getTagsWithUsageCounts();

        return $this->render('front/study_session/tags.html.twig', [
            'tags' => $tagsWithCounts,
        ]);
    }

    #[Route('/new', name: 'tag_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $tag = new Tag();
        
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check for duplicate tag name (case-insensitive)
            $existingTag = $this->tagRepository->findByName($tag->getName());
            
            if ($existingTag) {
                $this->addFlash('error', 'A tag with this name already exists.');
                return $this->render('front/study_session/tag_form.html.twig', [
                    'form' => $form->createView(),
                    'tag' => $tag,
                ]);
            }

            try {
                $this->entityManager->persist($tag);
                $this->entityManager->flush();

                $this->addFlash('success', 'Tag created successfully.');
                return $this->redirectToRoute('tag_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to create tag: ' . $e->getMessage());
            }
        }

        return $this->render('front/study_session/tag_form.html.twig', [
            'form' => $form->createView(),
            'tag' => $tag,
        ]);
    }

    #[Route('/{id}/delete', name: 'tag_delete', methods: ['POST'])]
    public function delete(Request $request, Tag $tag): Response
    {
        // CSRF validation
        $token = $request->request->get('_token');
        if (!$this->csrfTokenManager->isTokenValid(
            new \Symfony\Component\Security\Csrf\CsrfToken('delete_tag_' . $tag->getId(), $token)
        )) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('tag_index');
        }

        try {
            // Remove tag from all associated study sessions
            foreach ($tag->getStudySessions() as $studySession) {
                $studySession->removeTag($tag);
            }
            
            // Delete the tag (sessions are preserved)
            $this->entityManager->remove($tag);
            $this->entityManager->flush();

            $this->addFlash('success', 'Tag deleted successfully. Study sessions have been preserved.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to delete tag: ' . $e->getMessage());
        }

        return $this->redirectToRoute('tag_index');
    }

    #[Route('/{id}/filter', name: 'tag_filter', methods: ['GET'])]
    public function filter(Tag $tag): Response
    {
        $user = $this->getUser();
        
        // ADDED: PHPStan User Type Verification
        if (!$user instanceof \App\Entity\users\User) {
            throw $this->createAccessDeniedException('You must be logged in.');
        }
        
        // Get all study sessions for the current user
        $allSessions = $this->studySessionRepository->findByFilters(
            userId: $user->getId()
        );

        // Filter sessions by the selected tag
        $filteredSessions = array_filter($allSessions, function($session) use ($tag) {
            return $session->getTags()->contains($tag);
        });

        return $this->render('study_session/index.html.twig', [
            'study_sessions' => $filteredSessions,
            'filtered_by_tag' => $tag,
        ]);
    }
}