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
    public function list(EntityManagerInterface $em): Response
    {
        $books = $em->getRepository(Book::class)->findAll();

        return $this->render('front/book/list.html.twig', [
            'books' => $books,
        ]);
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
        // For now list all libraries. In a real app filter by availability.
        $libraries = $em->getRepository(LibraryEntity::class)->findAll();
        $book = $em->getRepository(Book::class)->find($id);

        return $this->render('front/book/libraries.html.twig', [
            'book' => $book,
            'libraries' => $libraries,
        ]);
    }

    #[Route('/books/{id}/loan', name: 'book_loan')]
    public function loan(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $book = $em->getRepository(Book::class)->find($id);
        if (! $book) {
            throw $this->createNotFoundException('Book not found');
        }

        $form = $this->createForm(LoanType::class, ['bookId' => $id]);
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

            $this->addFlash('success', 'Loan request submitted.');
            return $this->redirectToRoute('book_show', ['id' => $id]);
        }

        return $this->render('front/book/loan_form.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
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
            $data = $form->getData();
            $method = $data['method'] ?? null;

            if ($method === 'tokens') {
                // require login
                if (! $this->getUser()) {
                    return $this->redirectToRoute('app_login');
                }
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

                $this->addFlash('success', 'Book added to your library using tokens.');
                return $this->redirectToRoute('book_show', ['id' => $id]);
            }

            // For 'card' redirect to payment form (stub)
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

            $this->addFlash('success', 'Payment successful. Book added to your library.');
            return $this->redirectToRoute('book_show', ['id' => $id]);
        }

        return $this->render('front/book/payment_form.html.twig', [
            'book' => $book,
        ]);
    }
}
