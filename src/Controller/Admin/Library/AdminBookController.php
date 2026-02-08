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
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/books')]
#[IsGranted('ROLE_ADMIN')]
class AdminBookController extends AbstractController
{
    private string $booksDirectory = 'public/uploads/books';

    public function __construct(private SluggerInterface $slugger)
    {
    }

    #[Route('/', name: 'admin_books_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        // Get books uploaded by the current admin/author
        $books = $em->getRepository(Book::class)->findBy(
            ['uploaderId' => $user->getId()],
            ['createdAt' => 'DESC']
        );

        return $this->render('admin/book/index.html.twig', [
            'books' => $books,
        ]);
    }

    #[Route('/search', name: 'admin_books_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $em): Response
    {
        $query = $request->query->get('q', '');
        
        if (strlen($query) < 2) {
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
            ->setParameter('userId', $this->getUser()->getId())
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
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $book = new Book();
        $book->setUploaderId($this->getUser()->getId());
        $book->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si une date de publication est fournie, on met l'heure à minuit (00:00:00)
            if ($book->getPublishedAt()) {
                $publishedDate = $book->getPublishedAt();
                $book->setPublishedAt(new \DateTimeImmutable($publishedDate->format('Y-m-d') . ' 00:00:00'));
            }

            // Handle cover image upload
            $coverImageFile = $form->get('coverImage')->getData();
            if ($coverImageFile) {
                $originalFilename = pathinfo($coverImageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $coverImageFile->guessExtension();

                try {
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/' . $this->booksDirectory;
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $coverImageFile->move($uploadDir, $newFilename);
                    $book->setCoverImage('uploads/books/' . $newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Failed to upload cover image: ' . $e->getMessage());
                    return $this->redirectToRoute('admin_books_new');
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

        // Check if user is the owner or admin
        if ($book->getUploaderId() !== $this->getUser()->getId() && !$this->isGranted('ROLE_SUPER_ADMIN')) {
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

        // Check if user is the owner or admin
        if ($book->getUploaderId() !== $this->getUser()->getId() && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('You can only view your own books.');
        }

        return $this->render('admin/book/show.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_books_edit')]
    public function edit(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $book = $em->getRepository(Book::class)->find($id);
        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        // Check if user is the owner or admin
        if ($book->getUploaderId() !== $this->getUser()->getId() && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('You can only edit your own books.');
        }

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si une date de publication est fournie, on met l'heure à minuit (00:00:00)
            if ($book->getPublishedAt()) {
                $publishedDate = $book->getPublishedAt();
                $book->setPublishedAt(new \DateTimeImmutable($publishedDate->format('Y-m-d') . ' 00:00:00'));
            }

            // Handle cover image upload
            $coverImageFile = $form->get('coverImage')->getData();
            if ($coverImageFile) {
                // Delete old image if exists
                if ($book->getCoverImage()) {
                    $oldImagePath = $this->getParameter('kernel.project_dir') . '/public/' . $book->getCoverImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $originalFilename = pathinfo($coverImageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $coverImageFile->guessExtension();

                try {
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/' . $this->booksDirectory;
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $coverImageFile->move($uploadDir, $newFilename);
                    $book->setCoverImage('uploads/books/' . $newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Failed to upload cover image: ' . $e->getMessage());
                    return $this->redirectToRoute('admin_books_edit', ['id' => $book->getId()]);
                }
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

        // Check if user is the owner or admin
        if ($book->getUploaderId() !== $this->getUser()->getId() && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('You can only delete your own books.');
        }

        if ($this->isCsrfTokenValid('delete' . $book->getId(), $request->request->get('_token'))) {
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
