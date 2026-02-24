<?php

namespace App\Service\Library;

use App\Entity\Library\Payment;

/**
 * Service de traitement des paiements
 * Simule la validation de carte bancaire pour un projet scolaire
 */
class PaymentService
{
    /**
     * Valide un numéro de carte bancaire (algorithme de Luhn)
     */
    public function validateCardNumber(string $cardNumber): bool
    {
        // Retirer les espaces et tirets
        $cardNumber = preg_replace('/[\s\-]/', '', $cardNumber);
        
        // Vérifier que c'est bien des chiffres
        if (!ctype_digit($cardNumber)) {
            return false;
        }
        
        // Vérifier la longueur (13-19 chiffres)
        $length = strlen($cardNumber);
        if ($length < 13 || $length > 19) {
            return false;
        }
        
        // Algorithme de Luhn
        $sum = 0;
        $isSecond = false;
        
        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int)$cardNumber[$i];
            
            if ($isSecond) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
            $isSecond = !$isSecond;
        }
        
        return ($sum % 10) === 0;
    }

    /**
     * Valide la date d'expiration (format MM/YY)
     */
    public function validateExpiryDate(string $expiry): bool
    {
        if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiry, $matches)) {
            return false;
        }
        
        $month = (int)$matches[1];
        $year = (int)$matches[2] + 2000; // Convertir YY en YYYY
        
        $now = new \DateTime();
        $expiryDate = new \DateTime("$year-$month-01");
        $expiryDate->modify('last day of this month');
        
        return $expiryDate >= $now;
    }

    /**
     * Valide le code CVC (3 ou 4 chiffres)
     */
    public function validateCVC(string $cvc): bool
    {
        return preg_match('/^[0-9]{3,4}$/', $cvc) === 1;
    }

    /**
     * Valide le nom du titulaire
     */
    public function validateCardHolder(string $name): bool
    {
        return strlen(trim($name)) >= 3 && preg_match('/^[a-zA-Z\s\-\']+$/', $name);
    }

    /**
     * Traite un paiement par carte bancaire
     * Retourne true si succès, false si échec
     */
    public function processCreditCardPayment(
        Payment $payment,
        string $cardNumber,
        string $cardHolder,
        string $expiry,
        string $cvc
    ): array {
        // Validation des données
        $errors = [];
        
        if (!$this->validateCardNumber($cardNumber)) {
            $errors[] = 'Invalid card number';
        }
        
        if (!$this->validateCardHolder($cardHolder)) {
            $errors[] = 'Invalid cardholder name';
        }
        
        if (!$this->validateExpiryDate($expiry)) {
            $errors[] = 'Card expired or invalid expiry date';
        }
        
        if (!$this->validateCVC($cvc)) {
            $errors[] = 'Invalid CVC code';
        }
        
        if (!empty($errors)) {
            $payment->setStatus(Payment::STATUS_FAILED);
            $payment->setFailureReason(implode(', ', $errors));
            return ['success' => false, 'errors' => $errors];
        }
        
        // Simuler un traitement de paiement (95% de succès)
        $success = (random_int(1, 100) <= 95);
        
        if ($success) {
            $payment->setStatus(Payment::STATUS_COMPLETED);
            $payment->setCompletedAt(new \DateTimeImmutable());
            $payment->setCardLastFour(substr($cardNumber, -4));
            $payment->setCardHolderName($cardHolder);
            
            return ['success' => true, 'transaction_id' => $payment->getTransactionId()];
        } else {
            $payment->setStatus(Payment::STATUS_FAILED);
            $payment->setFailureReason('Payment declined by bank');
            
            return ['success' => false, 'errors' => ['Payment declined by bank']];
        }
    }

    /**
     * Traite un paiement PayPal (simulation)
     */
    public function processPayPalPayment(Payment $payment): array
    {
        // Simuler un traitement PayPal (98% de succès)
        $success = (random_int(1, 100) <= 98);
        
        if ($success) {
            $payment->setStatus(Payment::STATUS_COMPLETED);
            $payment->setCompletedAt(new \DateTimeImmutable());
            
            return ['success' => true, 'transaction_id' => $payment->getTransactionId()];
        } else {
            $payment->setStatus(Payment::STATUS_FAILED);
            $payment->setFailureReason('PayPal payment cancelled or failed');
            
            return ['success' => false, 'errors' => ['PayPal payment cancelled or failed']];
        }
    }

    /**
     * Extrait les 4 derniers chiffres d'une carte
     */
    public function getCardLastFour(string $cardNumber): string
    {
        $cardNumber = preg_replace('/[\s\-]/', '', $cardNumber);
        return substr($cardNumber, -4);
    }

    /**
     * Détermine le type de carte (Visa, Mastercard, etc.)
     */
    public function getCardType(string $cardNumber): string
    {
        $cardNumber = preg_replace('/[\s\-]/', '', $cardNumber);
        
        if (preg_match('/^4/', $cardNumber)) {
            return 'Visa';
        } elseif (preg_match('/^5[1-5]/', $cardNumber)) {
            return 'Mastercard';
        } elseif (preg_match('/^3[47]/', $cardNumber)) {
            return 'American Express';
        } elseif (preg_match('/^6(?:011|5)/', $cardNumber)) {
            return 'Discover';
        }
        
        return 'Unknown';
    }
}
