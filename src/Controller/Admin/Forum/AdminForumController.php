<?php

namespace App\Controller\Admin\Forum;

use App\Entity\Forum\Post;
use App\Entity\Forum\Report; // <-- ADDED
use App\Repository\Forum\PostRepository;
use App\Repository\Forum\ReportRepository; // <-- ADDED
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
        // 1. Get the search query from the URL (e.g. ?q=spam)
        $searchQuery = $request->query->get('q');

        // 2. Fetch data based on search
        if ($searchQuery) {
            // Ensure you added the 'adminSearch' method to your PostRepository!
            $posts = $postRepository->adminSearch($searchQuery);
        } else {
            // Default: Show all, newest first
            $posts = $postRepository->findBy([], ['createdAt' => 'DESC']);
        }

        return $this->render('admin/forum/index.html.twig', [
            'posts' => $posts,
            'searchQuery' => $searchQuery // Pass back to view to keep input filled
        ]);
    }

    // --- NEW: LIST ALL REPORTS ---
    #[Route('/reports', name: 'app_admin_forum_reports', methods: ['GET'])]
    public function reports(ReportRepository $reportRepository): Response
    {
        // Fetch all reports, newest first
        $reports = $reportRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/forum/reports.html.twig', [
            'reports' => $reports,
        ]);
    }

    // --- NEW: DISMISS A REPORT (Keep the post, delete the report) ---
    #[Route('/report/{id}/dismiss', name: 'app_admin_forum_report_dismiss', methods: ['POST'])]
    public function dismissReport(Request $request, Report $report, EntityManagerInterface $entityManager): Response
    {
        // CSRF Security Check
        if ($this->isCsrfTokenValid('dismiss'.$report->getId(), $request->request->get('_token'))) {
            
            // We remove the report entity, NOT the post!
            $entityManager->remove($report);
            $entityManager->flush();
            
            $this->addFlash('success', 'The report was dismissed. The post was not deleted.');
        }

        return $this->redirectToRoute('app_admin_forum_reports');
    }

    // TOGGLE LOCK STATUS
    #[Route('/{id}/toggle-lock', name: 'app_admin_forum_toggle_lock', methods: ['POST'])]
    public function toggleLock(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        // CSRF Security Check (uses the same 'lock' token name we will put in the form)
        if ($this->isCsrfTokenValid('lock'.$post->getId(), $request->request->get('_token'))) {
            
            // Flip the boolean: If true -> false. If false -> true.
            $post->setIsLocked(!$post->isLocked());
            
            $entityManager->flush();
            
            // Nice message for the admin
            $status = $post->isLocked() ? 'locked' : 'unlocked';
            $this->addFlash('success', "Discussion has been $status.");
        }

        // Stay on the same page
        return $this->redirectToRoute('app_admin_forum_index');
    }

    // DELETE POST (Admin Power)
    #[Route('/{id}/delete', name: 'app_admin_forum_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            
            // --- FIX: Delete attached reports first to satisfy Foreign Key Constraints ---
            $reports = $entityManager->getRepository(Report::class)->findBy(['post' => $post]);
            foreach ($reports as $report) {
                $entityManager->remove($report);
            }
            // -------------------------------------------------------------------------

            // Now it is safe to delete the post!
            $entityManager->remove($post);
            $entityManager->flush();
            $this->addFlash('success', 'Discussion deleted by Moderator.');
        }

        // If we are deleting from the reports page, redirect back to reports
        $referer = $request->headers->get('referer');
        if ($referer && str_contains($referer, '/reports')) {
            return $this->redirectToRoute('app_admin_forum_reports');
        }

        return $this->redirectToRoute('app_admin_forum_index');
    }
}