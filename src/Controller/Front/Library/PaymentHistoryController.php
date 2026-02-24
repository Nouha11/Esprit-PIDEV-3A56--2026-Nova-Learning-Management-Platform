<?php

namespace App\Controller\Front\Library;

use App\Entity\Library\Payment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur pour l'historique des paiements de l'utilisateur
 */
class PaymentHistoryController extends AbstractController
{
    #[Route('/my-payments', name: 'my_payments')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Récupérer tous les paiements de l'utilisateur
        $payments = $em->getRepository(Payment::class)->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );
        
        // Statistiques
        $stats = [
            'total' => count($payments),
            'completed' => 0,
            'failed' => 0,
            'totalAmount' => 0.0,
        ];
        
        foreach ($payments as $payment) {
            if ($payment->getStatus() === Payment::STATUS_COMPLETED) {
                $stats['completed']++;
                $stats['totalAmount'] += (float)$payment->getAmount();
            } elseif ($payment->getStatus() === Payment::STATUS_FAILED) {
                $stats['failed']++;
            }
        }
        
        return $this->render('front/payment/history.html.twig', [
            'payments' => $payments,
            'stats' => $stats,
        ]);
    }
}
