<?php

namespace App\Service\Library;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Creates Stripe PaymentIntents — mirrors Java's StripeService.
 * The client_secret is passed to Stripe.js embedded in the page.
 */
class StripeService
{
    public function __construct(
        #[Autowire(env: 'STRIPE_SECRET_KEY')] private string $secretKey,
        #[Autowire(env: 'STRIPE_PUBLISHABLE_KEY')] private string $publishableKey
    ) {
    }

    /**
     * Creates a PaymentIntent on Stripe's servers.
     * Returns the client_secret which Stripe.js uses to confirm the payment.
     *
     * @param int    $amountCents price in smallest currency unit (e.g. $9.99 = 999)
     * @param string $currency    e.g. "usd", "eur"
     * @param string $description shown on Stripe dashboard (e.g. book title)
     */
    public function createPaymentIntent(int $amountCents, string $currency, string $description): string
    {
        Stripe::setApiKey($this->secretKey);

        $intent = PaymentIntent::create([
            'amount'               => $amountCents,
            'currency'             => $currency,
            'description'          => $description,
            'payment_method_types' => ['card'],
        ]);

        return $intent->client_secret;
    }

    public function getPublishableKey(): string
    {
        return $this->publishableKey;
    }
}
