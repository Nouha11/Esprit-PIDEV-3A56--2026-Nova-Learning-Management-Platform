<?php

namespace App\Controller\Front\Library;

use App\Entity\Library\Book;
use App\Entity\Library\DigitalPurchase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur pour la visualisation des PDFs des livres numériques
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

        // Récupérer le livre
        $book = $em->getRepository(Book::class)->find($id);
        
        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        // Vérifier que c'est un livre numérique
        if (!$book->isDigital()) {
            $this->addFlash('error', 'This is not a digital book.');
            return $this->redirectToRoute('book_show', ['id' => $id]);
        }

        // Vérifier que le livre a un PDF
        if (!$book->getPdfUrl()) {
            $this->addFlash('error', 'PDF not available for this book.');
            return $this->redirectToRoute('book_show', ['id' => $id]);
        }

        // Vérifier que l'utilisateur a acheté ce livre
        $purchase = $em->getRepository(DigitalPurchase::class)->findOneBy([
            'user' => $user,
            'book' => $book
        ]);

        if (!$purchase) {
            $this->addFlash('error', 'You must purchase this book to read it.');
            return $this->redirectToRoute('book_purchase', ['id' => $id]);
        }

        return $this->render('front/book/pdf_viewer.html.twig', [
            'book' => $book,
            'pdfUrl' => '/' . $book->getPdfUrl(),
        ]);
    }
}
