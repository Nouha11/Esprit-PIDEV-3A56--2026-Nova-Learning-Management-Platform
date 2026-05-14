<?php

namespace App\Controller\Front\Library;

use App\Entity\Library\Book;
use App\Entity\Library\DigitalPurchase;
use App\Entity\Library\Payment;
use App\Service\Library\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/books')]
#[IsGranted('ROLE_USER')]
class StripePaymentController extends AbstractController
{
    public function __construct(
        private StripeService $stripeService,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Purchase page — creates a PaymentIntent and renders the embedded Stripe form.
     * Mirrors Java's PaymentFormController.loadStripeForm()
     */
    #[Route('/{id}/stripe-purchase', name: 'book_stripe_purchase', methods: ['GET'])]
    public function purchaseAction(int $id): Response
    {
        $book = $this->em->getRepository(Book::class)->find($id);
        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        if (!$book->isDigital()) {
            $this->addFlash('error', 'Only digital books can be purchased online.');
            return $this->redirectToRoute('book_show', ['id' => $id]);
        }

        // Check if already purchased
        $existing = $this->em->getRepository(DigitalPurchase::class)->findOneBy([
            'user' => $this->getUser(),
            'book' => $book,
        ]);
        if ($existing) {
            $this->addFlash('info', 'You already own this book.');
            return $this->redirectToRoute('book_read_pdf', ['id' => $id]);
        }

        // Create Stripe PaymentIntent — same as Java's StripeService.createPaymentIntent()
        $amountCents = (int) round((float) $book->getPrice() * 100);
        $clientSecret = $this->stripeService->createPaymentIntent(
            $amountCents,
            'usd',
            'Purchase: ' . $book->getTitle()
        );

        return $this->render('front/book/stripe_payment.html.twig', [
            'book'           => $book,
            'clientSecret'   => $clientSecret,
            'publishableKey' => $this->stripeService->getPublishableKey(),
        ]);
    }

    /**
     * Confirm endpoint — called by JS after Stripe confirms the payment.
     * Mirrors Java's JavaBridge.onPaymentSuccess()
     * Saves Payment + DigitalPurchase records to DB.
     */
    #[Route('/{id}/stripe-confirm', name: 'book_stripe_confirm', methods: ['POST'])]
    public function confirmAction(int $id, Request $request): JsonResponse
    {
        $book = $this->em->getRepository(Book::class)->find($id);
        if (!$book) {
            return $this->json(['success' => false, 'message' => 'Book not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $paymentIntentId = $data['paymentIntentId'] ?? null;

        if (!$paymentIntentId) {
            return $this->json(['success' => false, 'message' => 'Missing paymentIntentId'], 400);
        }

        // Check if already purchased (prevent double-save)
        $existing = $this->em->getRepository(DigitalPurchase::class)->findOneBy([
            'user' => $this->getUser(),
            'book' => $book,
        ]);
        if ($existing) {
            return $this->json(['success' => true, 'message' => 'Already purchased', 'redirectUrl' => $this->generateUrl('book_read_pdf', ['id' => $id])]);
        }

        try {
            // Save Payment record — mirrors Java's paymentService.ajouter(payment)
            $payment = new Payment();
            $payment->setUser($this->getUser());
            $payment->setBook($book);
            $payment->setAmount($book->getPrice() ?? '0.00');
            $payment->setPaymentMethod(Payment::METHOD_CREDIT_CARD);
            $payment->setTransactionId($paymentIntentId); // Store Stripe's PaymentIntent ID
            $payment->setStatus(Payment::STATUS_COMPLETED);
            $payment->setCompletedAt(new \DateTimeImmutable());
            $this->em->persist($payment);

            // Save DigitalPurchase record — grants access to the PDF
            $purchase = new DigitalPurchase();
            $purchase->setBook($book);
            $purchase->setUser($this->getUser());
            $purchase->setPurchasedAt(new \DateTimeImmutable());
            $this->em->persist($purchase);

            $this->em->flush();

            return $this->json([
                'success'     => true,
                'message'     => 'Payment confirmed! You can now read the book.',
                'redirectUrl' => $this->generateUrl('book_read_pdf', ['id' => $id]),
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'DB error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
