<?php

namespace App\Controller\Admin\Forum;

use App\Entity\Forum\Post;
use App\Entity\Forum\Comment;
use App\Entity\Forum\Report;
use App\Repository\Forum\PostRepository;
use App\Repository\Forum\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/forum')]
class AdminForumController extends AbstractController
{
    // LIST ALL POSTS (With Search Logic)
    #[Route('/', name: 'app_admin_forum_index', methods: ['GET'])]
    public function index(PostRepository $postRepository, Request $request): Response
    {
        $searchQuery = $request->query->get('q');

        if ($searchQuery) {
            $posts = $postRepository->adminSearch($searchQuery);
        } else {
            $posts = $postRepository->findBy([], ['createdAt' => 'DESC']);
        }

        return $this->render('admin/forum/index.html.twig', [
            'posts' => $posts,
            'searchQuery' => $searchQuery 
        ]);
    }

    // LIST ALL REPORTS (Posts & Comments)
    #[Route('/reports', name: 'app_admin_forum_reports', methods: ['GET'])]
    public function reports(ReportRepository $reportRepository): Response
    {
        $reports = $reportRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/forum/reports.html.twig', [
            'reports' => $reports,
        ]);
    }

    // DISMISS ANY REPORT (Keep content, delete report)
    #[Route('/report/{id}/dismiss', name: 'app_admin_forum_report_dismiss', methods: ['POST'])]
    public function dismissReport(Request $request, Report $report, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('dismiss'.$report->getId(), $request->request->get('_token'))) {
            $entityManager->remove($report);
            $entityManager->flush();
            $this->addFlash('success', 'The report was dismissed.');
        }

        return $this->redirectToRoute('app_admin_forum_reports');
    }

    // TOGGLE LOCK STATUS
    #[Route('/{id}/toggle-lock', name: 'app_admin_forum_toggle_lock', methods: ['POST'])]
    public function toggleLock(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('lock'.$post->getId(), $request->request->get('_token'))) {
            $post->setIsLocked(!$post->isLocked());
            $entityManager->flush();
            
            $status = $post->isLocked() ? 'locked' : 'unlocked';
            $this->addFlash('success', "Discussion has been $status.");
        }

        return $this->redirectToRoute('app_admin_forum_index');
    }

    // DELETE POST
    #[Route('/{id}/delete', name: 'app_admin_forum_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            
            // Delete attached reports first
            $reports = $entityManager->getRepository(Report::class)->findBy(['post' => $post]);
            foreach ($reports as $report) {
                $entityManager->remove($report);
            }
            
            $entityManager->remove($post);
            $entityManager->flush();
            $this->addFlash('success', 'Discussion deleted by Moderator.');
        }

        $referer = $request->headers->get('referer');
        if ($referer && str_contains($referer, '/reports')) {
            return $this->redirectToRoute('app_admin_forum_reports');
        }

        return $this->redirectToRoute('app_admin_forum_index');
    }

    // --- DELETE COMMENT ---
    // --- UPDATED: SCRUB COMMENT (TOMBSTONE) INSTEAD OF HARD DELETE ---
    #[Route('/comment/{id}/delete', name: 'app_admin_forum_comment_delete', methods: ['POST'])]
    public function deleteComment(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_comment'.$comment->getId(), $request->request->get('_token'))) {
            
            // 1. Delete attached reports first so it clears from the admin dashboard
            $reports = $entityManager->getRepository(Report::class)->findBy(['comment' => $comment]);
            foreach ($reports as $report) {
                $entityManager->remove($report);
            }

            // 2. SCRUB THE COMMENT (Reddit Style) instead of hard removing it
            $comment->setContent('🚫 *[This comment was removed by a moderator for violating community guidelines]*');
            $comment->setImageName(null); // Removes the image if there is one
            
            // Notice we are NOT calling $entityManager->remove($comment) anymore!
            
            $entityManager->flush();
            $this->addFlash('success', 'Comment scrubbed! The replies to it have been preserved.');
        }

        return $this->redirectToRoute('app_admin_forum_reports');
    }
}