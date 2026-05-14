<?php

namespace App\Controller\Front\Library;

use App\Entity\Library\Book;
use App\Entity\Library\DigitalPurchase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Serves PDFs for purchased digital books.
 *
 * Handles three pdf_url formats stored in the shared DB:
 *
 *  1. Symfony upload  → "uploads/pdfs/file.pdf"
 *     File lives at:    public/uploads/pdfs/file.pdf
 *     Served as:        /uploads/pdfs/file.pdf  (web-accessible)
 *
 *  2. Java local path → "C:\Users\nahno\...\file.pdf"
 *     File may exist on THIS machine (same dev machine).
 *     If found → copy to public/uploads/pdfs/ and serve it.
 *     If not found → show a friendly error.
 *
 *  3. Remote URL      → "https://nova-learning-management-platform.onrender.com/..."
 *     Redirect the browser directly to that URL.
 */
class PdfViewerController extends AbstractController
{
    #[Route('/book/{id}/read', name: 'book_read_pdf')]
    public function readPdf(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'You must be logged in to read books.');
            return $this->redirectToRoute('app_login');
        }

        $book = $em->getRepository(Book::class)->find($id);
        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        if (!$book->isDigital()) {
            $this->addFlash('error', 'This is not a digital book.');
            return $this->redirectToRoute('book_show', ['id' => $id]);
        }

        if (!$book->getPdfUrl()) {
            $this->addFlash('error', 'PDF not available for this book.');
            return $this->redirectToRoute('book_show', ['id' => $id]);
        }

        // Check purchase
        $purchase = $em->getRepository(DigitalPurchase::class)->findOneBy([
            'user' => $user,
            'book' => $book,
        ]);

        if (!$purchase) {
            $this->addFlash('error', 'You must purchase this book to read it.');
            return $this->redirectToRoute('book_stripe_purchase', ['id' => $id]);
        }

        $pdfUrl = $this->resolvePdfUrl($book->getPdfUrl());

        if ($pdfUrl === null) {
            $this->addFlash('error', 'The PDF file for this book is not available on this server. It was uploaded from another machine.');
            return $this->redirectToRoute('book_show', ['id' => $id]);
        }

        return $this->render('front/book/pdf_viewer.html.twig', [
            'book'   => $book,
            'pdfUrl' => $pdfUrl,
        ]);
    }

    /**
     * Resolves the raw pdf_url from DB into a web-accessible URL.
     *
     * @return string|null  Web URL to embed, or null if unavailable
     */
    private function resolvePdfUrl(string $raw): ?string
    {
        // ── Case 3: Already a full HTTP/HTTPS URL ──────────────────────────
        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
            return $raw;
        }

        // ── Case 2: Java absolute local path (Windows or Unix) ────────────
        if (str_contains($raw, '\\') || preg_match('/^[A-Za-z]:/', $raw)) {
            return $this->resolveJavaLocalPath($raw);
        }

        // ── Case 1: Symfony relative path (uploads/pdfs/file.pdf) ─────────
        $publicPath = $this->getParameter('kernel.project_dir') . '/public/' . $raw;
        if (file_exists($publicPath)) {
            return '/' . ltrim($raw, '/');
        }

        // File missing — return null
        return null;
    }

    /**
     * Handles Java's absolute local path.
     * If the file exists on this machine, copies it to public/uploads/pdfs/
     * and returns the web URL. Otherwise returns null.
     */
    private function resolveJavaLocalPath(string $absolutePath): ?string
    {
        // Normalise Windows backslashes
        $normalised = str_replace('\\', '/', $absolutePath);
        $filename   = basename($normalised);

        // Destination inside Symfony's public folder
        $destDir  = $this->getParameter('kernel.project_dir') . '/public/uploads/pdfs/';
        $destPath = $destDir . $filename;

        // Already copied previously
        if (file_exists($destPath)) {
            return '/uploads/pdfs/' . $filename;
        }

        // Try to copy from the original local path
        $sourcePath = str_replace('/', DIRECTORY_SEPARATOR, $normalised);
        if (file_exists($sourcePath)) {
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            copy($sourcePath, $destPath);
            return '/uploads/pdfs/' . $filename;
        }

        // File not found on this machine
        return null;
    }
}
