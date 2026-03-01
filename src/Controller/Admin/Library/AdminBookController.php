<?php

namespace App\Controller\Admin\Library;

use App\Entity\Library\Book;
use App\Entity\users\User;
use App\Form\Library\BookType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/books')]
#[IsGranted('ROLE_ADMIN')]
class AdminBookController extends AbstractController
{
    // Removed unused $booksDirectory and $slugger to fix PHPStan property.onlyWritten errors

    #[Route('/analytics', name: 'admin_books_analytics')]
    public function analytics(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        // ADDED: PHPStan User Type Verification
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }
        
        // Get all books by this admin
        $books = $em->getRepository(Book::class)->findBy(
            ['uploaderId' => $user->getId()],
            ['createdAt' => 'DESC']
        );

        // Get sales data over time (last 12 months)
        $salesByMonth = [];
        $revenueByMonth = [];
        $months = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = new \DateTime();
            $date->modify("-$i months");
            $monthKey = $date->format('Y-m');
            $monthLabel = $date->format('M Y');
            
            $months[] = $monthLabel;
            $salesByMonth[$monthKey] = 0;
            $revenueByMonth[$monthKey] = 0;
        }

        // Query payments grouped by month
        $payments = $em->getRepository(\App\Entity\Library\Payment::class)
            ->createQueryBuilder('p')
            ->select('p')
            ->join('p.book', 'b')
            ->where('b.uploaderId = :userId')
            ->andWhere('p.status = :status')
            ->andWhere('p.createdAt >= :startDate')
            ->setParameter('userId', $user->getId())
            ->setParameter('status', \App\Entity\Library\Payment::STATUS_COMPLETED)
            ->setParameter('startDate', new \DateTime('-11 months'))
            ->orderBy('p.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($payments as $payment) {
            $monthKey = $payment->getCreatedAt()->format('Y-m');
            if (isset($salesByMonth[$monthKey])) {
                $salesByMonth[$monthKey]++;
                $revenueByMonth[$monthKey] += (float)$payment->getAmount();
            }
        }

        // Get sales by book (top 10)
        $salesByBook = [];
        foreach ($books as $book) {
            $sales = $em->getRepository(\App\Entity\Library\Payment::class)
                ->createQueryBuilder('p')
                ->select('COUNT(p.id) as count, SUM(p.amount) as revenue')
                ->where('p.book = :book')
                ->andWhere('p.status = :status')
                ->setParameter('book', $book)
                ->setParameter('status', \App\Entity\Library\Payment::STATUS_COMPLETED)
                ->getQuery()
                ->getSingleResult();

            if ($sales['count'] > 0) {
                $salesByBook[] = [
                    'title' => $book->getTitle(),
                    'count' => (int)$sales['count'],
                    'revenue' => (float)($sales['revenue'] ?? 0)
                ];
            }
        }

        // Sort by count and take top 10
        usort($salesByBook, fn($a, $b) => $b['count'] - $a['count']);
        $salesByBook = array_slice($salesByBook, 0, 10);

        // Calculate overall statistics
        $totalSales = array_sum($salesByMonth);
        $totalRevenue = array_sum($revenueByMonth);
        $avgOrderValue = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

        return $this->render('admin/book/analytics.html.twig', [
            'months' => $months,
            'salesByMonth' => array_values($salesByMonth),
            'revenueByMonth' => array_values($revenueByMonth),
            'salesByBook' => $salesByBook,
            'totalSales' => $totalSales,
            'totalRevenue' => $totalRevenue,
            'avgOrderValue' => $avgOrderValue,
            'totalBooks' => count($books),
        ]);
    }

    #[Route('/', name: 'admin_books_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        // ADDED: PHPStan User Type Verification
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }
        
        // Get books uploaded by the current admin/author
        $books = $em->getRepository(Book::class)->findBy(
            ['uploaderId' => $user->getId()],
            ['createdAt' => 'DESC']
        );

        // Calculate sales statistics for each book
        $salesData = [];
        $totalRevenue = 0;
        $totalSales = 0;

        foreach ($books as $book) {
            // Count completed payments for this book
            $sales = $em->getRepository(\App\Entity\Library\Payment::class)
                ->createQueryBuilder('p')
                ->select('COUNT(p.id) as count, SUM(p.amount) as revenue')
                ->where('p.book = :book')
                ->andWhere('p.status = :status')
                ->setParameter('book', $book)
                ->setParameter('status', \App\Entity\Library\Payment::STATUS_COMPLETED)
                ->getQuery()
                ->getSingleResult();

            $salesData[$book->getId()] = [
                'count' => (int)$sales['count'],
                'revenue' => (float)($sales['revenue'] ?? 0)
            ];

            $totalSales += $salesData[$book->getId()]['count'];
            $totalRevenue += $salesData[$book->getId()]['revenue'];
        }

        return $this->render('admin/book/index.html.twig', [
            'books' => $books,
            'salesData' => $salesData,
            'totalSales' => $totalSales,
            'totalRevenue' => $totalRevenue,
        ]);
    }

    #[Route('/search', name: 'admin_books_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $em): Response
    {
        $query = $request->query->get('q', '');
        
        if (strlen($query) < 2) {
            return $this->json([]);
        }
        
        $user = $this->getUser();
        
        // ADDED: PHPStan User Type Verification
        if (!$user instanceof User) {
            return $this->json([]);
        }

        // Recherche des livres par titre ou auteur
        $qb = $em->getRepository(Book::class)->createQueryBuilder('b');
        $books = $qb
            ->where('b.uploaderId = :userId')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(b.title)', ':query'),
                    $qb->expr()->like('LOWER(b.author)', ':query')
                )
            )
            ->setParameter('userId', $user->getId())
            ->setParameter('query', '%' . strtolower($query) . '%')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // Formater les résultats pour JSON
        $results = array_map(function($book) {
            return [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor() ?? 'Unknown',
                'price' => $book->getPrice() ?? '0.00',
                'coverImage' => $book->getCoverImage() ? '/public/' . $book->getCoverImage() : '/assets/images/book/01.jpg',
                'isDigital' => $book->isDigital(),
                'url' => $this->generateUrl('admin_books_show', ['id' => $book->getId()])
            ];
        }, $books);

        return $this->json($results);
    }

    #[Route('/new', name: 'admin_books_new')]
    public function new(Request $request, EntityManagerInterface $em, \App\Service\Library\FileUploadService $fileUploadService): Response
    {
        $user = $this->getUser();
        
        // ADDED: PHPStan User Type Verification
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }
        
        $book = new Book();
        $book->setUploaderId($user->getId());
        $book->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si une date de publication est fournie, on met l'heure à minuit (00:00:00)
            if ($book->getPublishedAt()) {
                $publishedDate = $book->getPublishedAt();
                $book->setPublishedAt(new \DateTimeImmutable($publishedDate->format('Y-m-d') . ' 00:00:00'));
            }

            // Handle cover image upload locally
            $coverImageFile = $form->get('coverImage')->getData();
            if ($coverImageFile) {
                $result = $fileUploadService->uploadCoverImage($coverImageFile);
                if ($result['success']) {
                    $book->setCoverImage($result['path']);
                } else {
                    $this->addFlash('error', 'Failed to upload cover image: ' . $result['error']);
                    return $this->redirectToRoute('admin_books_new');
                }
            }

            // Handle PDF upload locally (for digital books)
            $pdfFile = $form->get('pdfFile')->getData();
            if ($pdfFile) {
                if ($book->isDigital()) {
                    try {
                        $result = $fileUploadService->uploadPdf($pdfFile);
                        if ($result['success']) {
                            $book->setPdfUrl($result['path']);
                            $sizeMB = round($result['size'] / 1024 / 1024, 2);
                            $this->addFlash('success', 'PDF uploaded successfully! Size: ' . $sizeMB . ' MB');
                        } else {
                            $this->addFlash('error', 'Failed to upload PDF: ' . ($result['error'] ?? 'Unknown error'));
                        }
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Exception uploading PDF: ' . $e->getMessage());
                    }
                } else {
                    $this->addFlash('warning', 'PDF file ignored - book is not marked as digital');
                }
            }

            $em->persist($book);
            $em->flush();

            $this->addFlash('success', 'Book created successfully!');
            return $this->redirectToRoute('admin_books_added', ['id' => $book->getId()]);
        }

        return $this->render('admin/book/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/added', name: 'admin_books_added')]
    public function added(int $id, EntityManagerInterface $em): Response
    {
        $book = $em->getRepository(Book::class)->find($id);
        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        $user = $this->getUser();
        
        // ADDED: PHPStan User Type Verification
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        // Check if user is the owner or admin
        if ($book->getUploaderId() !== $user->getId() && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('You can only view your own books.');
        }

        return $this->render('admin/book/added.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/{id}', name: 'admin_books_show')]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $book = $em->getRepository(Book::class)->find($id);
        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        $user = $this->getUser();
        
        // ADDED: PHPStan User Type Verification
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        // Check if user is the owner or admin
        if ($book->getUploaderId() !== $user->getId() && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('You can only view your own books.');
        }

        return $this->render('admin/book/show.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_books_edit')]
    public function edit(Request $request, int $id, EntityManagerInterface $em, \App\Service\Library\FileUploadService $fileUploadService): Response
    {
        $book = $em->getRepository(Book::class)->find($id);
        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        $user = $this->getUser();
        
        // ADDED: PHPStan User Type Verification
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        // Check if user is the owner or admin
        if ($book->getUploaderId() !== $user->getId() && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('You can only edit your own books.');
        }

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // DEBUG: Log form submission
            $pdfFile = $form->get('pdfFile')->getData();
            $this->addFlash('info', 'Form submitted. PDF file received: ' . ($pdfFile ? 'YES (' . $pdfFile->getClientOriginalName() . ')' : 'NO'));
            $this->addFlash('info', 'Book is digital: ' . ($book->isDigital() ? 'YES' : 'NO'));
            
            // Si une date de publication est fournie, on met l'heure à minuit (00:00:00)
            if ($book->getPublishedAt()) {
                $publishedDate = $book->getPublishedAt();
                $book->setPublishedAt(new \DateTimeImmutable($publishedDate->format('Y-m-d') . ' 00:00:00'));
            }

            // Handle cover image upload locally
            $coverImageFile = $form->get('coverImage')->getData();
            if ($coverImageFile) {
                $result = $fileUploadService->uploadCoverImage($coverImageFile);
                if ($result['success']) {
                    // Delete old cover image if exists
                    if ($book->getCoverImage()) {
                        $fileUploadService->deleteFile($book->getCoverImage());
                    }
                    $book->setCoverImage($result['path']);
                    $this->addFlash('success', 'Cover image uploaded successfully!');
                } else {
                    $this->addFlash('error', 'Failed to upload cover image: ' . $result['error']);
                    return $this->redirectToRoute('admin_books_edit', ['id' => $book->getId()]);
                }
            }

            // Handle PDF upload locally
            if ($pdfFile) {
                $this->addFlash('info', 'PDF file detected, starting upload...');
                
                if ($book->isDigital()) {
                    try {
                        $this->addFlash('info', 'Uploading PDF locally...');
                        $result = $fileUploadService->uploadPdf($pdfFile);
                        
                        if ($result['success']) {
                            // Delete old PDF if exists
                            if ($book->getPdfUrl()) {
                                $fileUploadService->deleteFile($book->getPdfUrl());
                            }
                            $book->setPdfUrl($result['path']);
                            $sizeMB = round($result['size'] / 1024 / 1024, 2);
                            $this->addFlash('success', 'PDF uploaded successfully! Path: ' . $result['path'] . ' (' . $sizeMB . ' MB)');
                        } else {
                            $this->addFlash('error', 'Failed to upload PDF: ' . ($result['error'] ?? 'Unknown error'));
                        }
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Exception uploading PDF: ' . $e->getMessage());
                    }
                } else {
                    $this->addFlash('warning', 'PDF file ignored - book is not marked as digital. Please check the "Digital Book" checkbox.');
                }
            } else {
                $this->addFlash('info', 'No PDF file was uploaded.');
            }

            $book->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            $this->addFlash('success', 'Book updated successfully!');
            return $this->redirectToRoute('admin_books_show', ['id' => $book->getId()]);
        }

        return $this->render('admin/book/edit.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
            'inventory' => [],
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_books_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $book = $em->getRepository(Book::class)->find($id);
        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        $user = $this->getUser();
        
        // ADDED: PHPStan User Type Verification
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        // Check if user is the owner or admin
        if ($book->getUploaderId() !== $user->getId() && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('You can only delete your own books.');
        }

        if ($this->isCsrfTokenValid('delete' . $book->getId(), (string)$request->request->get('_token'))) {
            // Delete cover image if exists
            if ($book->getCoverImage()) {
                $imagePath = $this->getParameter('kernel.project_dir') . '/public/' . $book->getCoverImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $em->remove($book);
            $em->flush();

            $this->addFlash('success', 'Book deleted successfully!');
        }

        return $this->redirectToRoute('admin_books_index');
    }
}