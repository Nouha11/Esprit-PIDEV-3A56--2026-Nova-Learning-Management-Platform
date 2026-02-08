<?php
namespace App\Controller\Front\Library;

use App\Entity\Library\Book;
use App\Entity\Library\Library as LibraryEntity;
use App\Entity\Library\Loan;
use App\Entity\Library\DigitalPurchase;
use App\Entity\Library\UserLibrary;
use App\Form\Library\LoanType;
use App\Form\Library\PurchaseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BookController extends AbstractController
{
    #[Route('/books', name: 'book_list')]
    public function list(Request $request, EntityManagerInterface $em): Response
    {
        $search = $request->query->get('search', '');
        $productType = $request->query->get('product_type', '');
        $author = $request->query->get('author', '');

        $qb = $em->getRepository(Book::class)->createQueryBuilder('b');

        // Apply search filter
        if ($search) {
            $qb->andWhere('b.title LIKE :search OR b.author LIKE :search OR b.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Apply product type filter
        if ($productType !== '') {
            $isDigital = ($productType === 'digital');
            $qb->andWhere('b.isDigital = :isDigital')
               ->setParameter('isDigital', $isDigital);
        }

        // Apply author filter
        if ($author) {
            $qb->andWhere('b.author = :author')
               ->setParameter('author', $author);
        }

        $books = $qb->getQuery()->getResult();

        // Get all unique authors for the filter dropdown
        $authors = $em->getRepository(Book::class)
            ->createQueryBuilder('b')
            ->select('DISTINCT b.author')
            ->where('b.author IS NOT NULL')
            ->orderBy('b.author', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('front/book/list.html.twig', [
            'books' => $books,
            'authors' => array_column($authors, 'author'),
            'currentSearch' => $search,
            'currentProductType' => $productType,
            'currentAuthor' => $author,
        ]);
    }

    #[Route('/books/search-ajax', name: 'book_search_ajax')]
    public function searchAjax(Request $request, EntityManagerInterface $em): Response
    {
        $query = $request->query->get('q', '');
        
        if (strlen($query) < 2) {
            return $this->json([]);
        }

        $books = $em->getRepository(Book::class)
            ->createQueryBuilder('b')
            ->where('b.title LIKE :query OR b.author LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($books as $book) {
            $results[] = [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor() ?? 'Unknown Author',
                'price' => $book->getPrice() ?? '0.00',
                'coverImage' => $book->getCoverImage() ?? 'assets/images/book/01.jpg',
                'isDigital' => $book->isDigital(),
            ];
        }

        return $this->json($results);
    }

    #[Route('/books/{id}', name: 'book_show')]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $book = $em->getRepository(Book::class)->find($id);
        if (! $book) {
            throw $this->createNotFoundException('Book not found');
        }

        return $this->render('front/book/show.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/books/{id}/libraries', name: 'book_libraries')]
    public function libraries(int $id, EntityManagerInterface $em): Response
    {
        $book = $em->getRepository(Book::class)->find($id);
        
        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        // Get inventory data for each library
        $inventoryData = [];
        foreach ($book->getLibraries() as $library) {
            $inventory = $em->getRepository(\App\Entity\Library\BookLibraryInventory::class)
                ->findOneBy(['book' => $book, 'library' => $library]);
            $inventoryData[$library->getId()] = $inventory;
        }

        return $this->render('front/book/libraries.html.twig', [
            'book' => $book,
            'inventoryData' => $inventoryData,
        ]);
    }

    #[Route('/books/{id}/loan', name: 'book_loan')]
    public function loan(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $book = $em->getRepository(Book::class)->find($id);
        if (! $book) {
            throw $this->createNotFoundException('Book not found');
        }

        // Get pre-selected library from query parameter
        $libraryId = $request->query->get('library');
        $selectedLibrary = null;
        if ($libraryId) {
            $selectedLibrary = $em->getRepository(LibraryEntity::class)->find($libraryId);
        }

        $form = $this->createForm(LoanType::class, ['bookId' => $id, 'libraryId' => $libraryId]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (! $this->getUser()) {
                return $this->redirectToRoute('app_login');
            }
            $data = $form->getData();
            $loan = new Loan();
            $loan->setBook($book);
            $loan->setUser($this->getUser());

            $startRaw = $data['startAt'] ?? null;
            $start = null;
            if (is_string($startRaw)) {
                $start = new \DateTimeImmutable($startRaw);
            } elseif ($startRaw instanceof \DateTimeImmutable) {
                $start = $startRaw;
            } elseif ($startRaw instanceof \DateTimeInterface) {
                $start = \DateTimeImmutable::createFromMutable(new \DateTime($startRaw->format('Y-m-d H:i:s')));
            }

            $endRaw = $data['endAt'] ?? null;
            $end = null;
            if (is_string($endRaw)) {
                $end = new \DateTimeImmutable($endRaw);
            } elseif ($endRaw instanceof \DateTimeImmutable) {
                $end = $endRaw;
            } elseif ($endRaw instanceof \DateTimeInterface) {
                $end = \DateTimeImmutable::createFromMutable(new \DateTime($endRaw->format('Y-m-d H:i:s')));
            }

            $loan->setStartAt($start);
            $loan->setEndAt($end);
            $em->persist($loan);
            $em->flush();

            // Store loan ID in session for confirmation page
            $request->getSession()->set('last_loan_id', $loan->getId());

            return $this->redirectToRoute('book_loan_confirmation', ['id' => $id]);
        }

        return $this->render('front/book/loan_form.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
            'selectedLibrary' => $selectedLibrary,
        ]);
    }

    #[Route('/books/{id}/loan/confirmation', name: 'book_loan_confirmation')]
    public function loanConfirmation(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $book = $em->getRepository(Book::class)->find($id);
        if (! $book) {
            throw $this->createNotFoundException('Book not found');
        }

        // Get the last loan ID from session
        $loanId = $request->getSession()->get('last_loan_id');
        $loan = null;
        
        if ($loanId) {
            $loan = $em->getRepository(Loan::class)->find($loanId);
            // Clear the session
            $request->getSession()->remove('last_loan_id');
        }

        return $this->render('front/book/loan_confirmation.html.twig', [
            'book' => $book,
            'loan' => $loan,
        ]);
    }

    #[Route('/books/{id}/purchase', name: 'book_purchase')]
    public function purchase(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $book = $em->getRepository(Book::class)->find($id);
        if (! $book) {
            throw $this->createNotFoundException('Book not found');
        }

        $form = $this->createForm(PurchaseType::class, ['bookId' => $id]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Require login for purchase
            if (! $this->getUser()) {
                $this->addFlash('warning', 'Please login to complete your purchase.');
                return $this->redirectToRoute('app_login');
            }

            $data = $form->getData();
            $method = $data['method'] ?? 'credit_card';

            // Store payment method in session and redirect to payment form
            $request->getSession()->set('payment_method', $method);
            $request->getSession()->set('book_id', $id);
            
            return $this->redirectToRoute('book_payment', ['id' => $id]);
        }

        return $this->render('front/book/purchase_form.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/books/{id}/payment', name: 'book_payment')]
    public function payment(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $book = $em->getRepository(Book::class)->find($id);
        if (! $book) {
            throw $this->createNotFoundException('Book not found');
        }

        // Get payment method from session
        $paymentMethod = $request->getSession()->get('payment_method', 'credit_card');

        if ($request->isMethod('POST')) {
            if (! $this->getUser()) {
                return $this->redirectToRoute('app_login');
            }
            
            // Stub payment processing: assume success
            $purchase = new DigitalPurchase();
            $purchase->setBook($book);
            $purchase->setUser($this->getUser());
            $purchase->setPurchasedAt(new \DateTimeImmutable());
            $em->persist($purchase);

            $userLib = new UserLibrary();
            $userLib->setBook($book);
            $userLib->setUser($this->getUser());
            $userLib->setGrantedAt(new \DateTimeImmutable());
            $em->persist($userLib);

            $em->flush();

            // Clear session data
            $request->getSession()->remove('payment_method');
            $request->getSession()->remove('book_id');

            // Success message based on payment method
            $methodNames = [
                'credit_card' => 'Credit Card',
                'paypal' => 'PayPal',
            ];
            $methodName = $methodNames[$paymentMethod] ?? 'selected payment method';

            $this->addFlash('success', "Payment successful via {$methodName}! The book has been added to your library.");
            return $this->redirectToRoute('book_show', ['id' => $id]);
        }

        return $this->render('front/book/payment_form.html.twig', [
            'book' => $book,
            'paymentMethod' => $paymentMethod,
        ]);
    }
}
