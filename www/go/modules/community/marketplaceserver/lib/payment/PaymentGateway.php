<?php

namespace go\modules\community\marketplaceserver\lib\payment;

use go\modules\community\marketplaceserver\model\Product;

/**
 * A payment gateway driver. The marketplace is gateway-agnostic: the checkout
 * endpoint asks the active gateway to start a hosted checkout and hands the buyer
 * its redirect URL, and the webhook endpoint asks the SAME gateway to verify +
 * translate an incoming callback into a {@see PaymentEvent}. Add Stripe / GoPay /
 * Comgate / PayPal by implementing this interface and registering it in
 * {@see PaymentGateways}. Nothing else in the module is gateway-specific.
 */
interface PaymentGateway
{
    /**
     * Stable identifier used in the webhook URL (/paymentWebhook/{id}) and stored
     * as the active-gateway setting. Lowercase, no spaces (e.g. "stripe").
     *
     * @return string
     */
    public function id(): string;

    /**
     * Human-readable name for the settings dropdown.
     *
     * @return string
     */
    public function label(): string;

    /**
     * Whether this gateway has the credentials it needs to operate.
     *
     * @return bool
     */
    public function isConfigured(): bool;

    /**
     * The HTTP request header carrying this gateway's webhook signature (e.g.
     * "Stripe-Signature"), so the Module can read it without knowing the gateway.
     *
     * @return string
     */
    public function signatureHeaderName(): string;

    /**
     * Start a hosted checkout for $product on behalf of $customerId and return the
     * URL the buyer's browser must be sent to. Implementations MUST embed the
     * customer + product ids in the session so the webhook can attribute the
     * resulting payment (metadata / client_reference_id).
     *
     * @param int $customerId the marketplace customer buying
     * @param \go\modules\community\marketplaceserver\model\Product $product
     * @param string $successUrl where the gateway returns the buyer on success
     * @param string $cancelUrl where the gateway returns the buyer on cancel
     * @return string absolute redirect URL to the hosted checkout page
     * @throws \Exception when the session cannot be created
     */
    public function createCheckoutSession(int $customerId, Product $product, string $successUrl, string $cancelUrl): string;

    /**
     * Verify the raw webhook payload against its signature and translate it into a
     * normalised {@see PaymentEvent}. MUST return null when the signature does not
     * verify (the caller answers 400 and grants nothing). A verified-but-unhandled
     * payload returns {@see PaymentEvent::ignored()} so the caller can 200-ack it.
     *
     * @param string $payload the exact raw request body
     * @param string|null $signatureHeader the gateway's signature header value
     * @return \go\modules\community\marketplaceserver\lib\payment\PaymentEvent|null null = signature invalid
     */
    public function parseWebhook(string $payload, ?string $signatureHeader): ?PaymentEvent;
}
