<?php

namespace go\modules\community\marketplaceserver\lib\payment;

use go\core\http\Client;
use go\modules\community\marketplaceserver\model\Product;
use go\modules\community\marketplaceserver\model\Settings;

/**
 * Stripe payment driver. Talks to the Stripe REST API with a thin HTTP client
 * (no vendored SDK — only two interactions are needed: create a Checkout Session,
 * and verify + parse a webhook), keeping the dependency surface small and the
 * secret handling auditable. The signature verification itself lives in the pure,
 * unit-tested {@see StripeSignature}.
 *
 * Credentials come from module Settings (secret key + webhook signing secret,
 * both stored encrypted). Products are matched to Stripe prices via
 * {@see Product::$stripePriceId}; the buyer + product ids ride along in the
 * session metadata so the webhook can attribute the payment.
 */
class StripeGateway implements PaymentGateway
{
    const API_BASE = 'https://api.stripe.com/v1';

    /**
     * Pinned on every API call so a Stripe-side account upgrade cannot silently
     * change the response shape under us. Bump deliberately, after checking the
     * changelog for the fields this driver reads.
     */
    const API_VERSION = '2025-03-31.basil';

    /**
     * @var \go\modules\community\marketplaceserver\model\Settings
     */
    private $settings;

    /**
     * @param \go\modules\community\marketplaceserver\model\Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function id(): string
    {
        return 'stripe';
    }

    public function label(): string
    {
        return 'Stripe';
    }

    public function isConfigured(): bool
    {
        return $this->settings->getStripeSecretConfigured()
            && $this->settings->getStripeWebhookConfigured();
    }

    public function signatureHeaderName(): string
    {
        return 'Stripe-Signature';
    }

    /**
     * @param int $customerId
     * @param \go\modules\community\marketplaceserver\model\Product $product
     * @param string $successUrl
     * @param string $cancelUrl
     * @return string
     * @throws \Exception
     */
    public function createCheckoutSession(int $customerId, Product $product, string $successUrl, string $cancelUrl): string
    {
        $secret = $this->settings->decryptStripeSecretKey();
        if ($secret === null) {
            throw new \Exception('Stripe is not configured');
        }
        if (empty($product->stripePriceId)) {
            throw new \Exception('This product has no Stripe price configured');
        }

        // x-www-form-urlencoded with Stripe's bracket notation. mode=payment: the
        // marketplace sells perpetual modules/collections, so a one-off charge maps
        // to a perpetual entitlement. (Recurring prices would use mode=subscription;
        // left as a follow-up — PaymentEvent already carries an expiry for it.)
        $params = [
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string) $customerId,
            'line_items' => [['price' => $product->stripePriceId, 'quantity' => 1]],
            'metadata' => [
                'customerId' => (string) $customerId,
                'productId' => (string) $product->id,
            ],
        ];

        $client = new Client();
        $client->setOption(CURLOPT_FOLLOWLOCATION, false);
        $client->setHeader('Authorization', 'Bearer ' . $secret);
        $client->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $client->setHeader('Stripe-Version', self::API_VERSION);
        // Pass a STRING body so curl sends x-www-form-urlencoded (an array would be
        // multipart, which the Stripe API rejects).
        $res = $client->post(self::API_BASE . '/checkout/sessions', http_build_query($params));

        $status = (int) ($res['status'] ?? 0);
        $body = json_decode((string) ($res['body'] ?? ''), true);
        if ($status !== 200 || !is_array($body) || empty($body['url'])) {
            $msg = is_array($body) && isset($body['error']['message'])
                ? (string) $body['error']['message']
                : ('Stripe checkout failed (HTTP ' . $status . ')');
            throw new \Exception($msg);
        }
        return (string) $body['url'];
    }

    /**
     * @param string $payload
     * @param string|null $signatureHeader
     * @return \go\modules\community\marketplaceserver\lib\payment\PaymentEvent|null
     */
    public function parseWebhook(string $payload, ?string $signatureHeader): ?PaymentEvent
    {
        $secret = $this->settings->decryptStripeWebhookSecret();
        if ($secret === null) {
            // Same 400 to the caller as a bad signature (never tell an unauthenticated
            // client how we are configured), but the two are very different for us:
            // this one means every webhook is being dropped until an admin pastes the
            // whsec into settings. Say so in the log, or it looks like an attack.
            \go\core\ErrorHandler::log(
                'marketplaceserver: Stripe webhook received but no signing secret is configured '
                . '- the event was dropped. Set the webhook signing secret in the module settings.'
            );
            return null;
        }
        if (!StripeSignature::verify($payload, $signatureHeader, $secret, time())) {
            return null;   // bad signature → caller answers 400, grants nothing
        }

        $event = json_decode($payload, true);
        if (!is_array($event)) {
            return PaymentEvent::ignored();
        }
        return self::mapEvent($event);
    }

    /**
     * Translate a decoded Stripe event into a {@see PaymentEvent}. PURE (no
     * signature/Settings/HTTP) so it is unit-testable — parseWebhook verifies the
     * signature first, then delegates here. Handled events:
     *   - checkout.session.completed (paid)  → PURCHASE_COMPLETED (+ paymentRef)
     *   - checkout.session.async_payment_succeeded → PURCHASE_COMPLETED (a delayed
     *     method such as SEPA debit: the session completed unpaid, money came later)
     *   - charge.refunded (FULLY refunded)   → ACCESS_REVOKED (by paymentRef)
     *   - charge.dispute.closed (status=lost)→ ACCESS_REVOKED (by paymentRef)
     *   - customer.subscription.deleted      → ACCESS_REVOKED (by subscriptionId)
     * Anything else (incl. a partial refund, a won dispute) → IGNORED.
     *
     * @param array<string, mixed> $event a decoded Stripe event
     * @return \go\modules\community\marketplaceserver\lib\payment\PaymentEvent
     */
    public static function mapEvent(array $event): PaymentEvent
    {
        $type = (string) ($event['type'] ?? '');
        $object = is_array($event['data']['object'] ?? null) ? $event['data']['object'] : [];
        $externalRef = isset($event['id']) ? (string) $event['id'] : null;

        if ($type === 'checkout.session.completed' || $type === 'checkout.session.async_payment_succeeded') {
            // Only act once the session is actually paid. A delayed payment method
            // completes the session 'unpaid' and follows up with
            // async_payment_succeeded once the money arrives; a card is 'paid'
            // immediately.
            if (($object['payment_status'] ?? '') !== 'paid') {
                return PaymentEvent::ignored();
            }
            $meta = is_array($object['metadata'] ?? null) ? $object['metadata'] : [];
            $customerId = isset($meta['customerId']) ? (int) $meta['customerId'] : null;
            $productId = isset($meta['productId']) ? (int) $meta['productId'] : null;
            if (!$customerId || !$productId) {
                return PaymentEvent::ignored();
            }
            $e = new PaymentEvent(
                PaymentEvent::PURCHASE_COMPLETED,
                $customerId,
                $productId,
                null,                                                        // mode=payment → perpetual
                !empty($object['subscription']) ? (string) $object['subscription'] : null,
                !empty($object['payment_intent']) ? (string) $object['payment_intent'] : null,
                $externalRef
            );
            if (isset($object['amount_total'])) {
                $e->amount = (int) $object['amount_total'];
            }
            if (!empty($object['currency'])) {
                $e->currency = strtoupper((string) $object['currency']);
            }
            return $e;
        }

        if ($type === 'charge.refunded') {
            // Revoke ONLY on a FULL refund — a partial refund leaves the purchase
            // (and its entitlement) intact. Stripe fires charge.refunded for both.
            $fullyRefunded = !empty($object['refunded'])
                || (isset($object['amount'], $object['amount_refunded'])
                    && (int) $object['amount_refunded'] >= (int) $object['amount'] && (int) $object['amount'] > 0);
            if (!$fullyRefunded) {
                return PaymentEvent::ignored();
            }
            $paymentRef = !empty($object['payment_intent']) ? (string) $object['payment_intent'] : null;
            if ($paymentRef === null) {
                return PaymentEvent::ignored();
            }
            $e = new PaymentEvent(
                PaymentEvent::ACCESS_REVOKED,
                null, null, null, null,
                $paymentRef,
                $externalRef
            );
            $e->reason = PaymentEvent::REASON_REFUND;
            if (isset($object['amount_refunded'])) {
                $e->amount = (int) $object['amount_refunded'];
            }
            if (!empty($object['currency'])) {
                $e->currency = strtoupper((string) $object['currency']);
            }
            return $e;
        }

        if ($type === 'charge.dispute.closed') {
            // A LOST chargeback takes the money back without ever firing
            // charge.refunded — the charge's `refunded` flag stays false — so
            // without this branch the buyer keeps the entitlement for free.
            // 'won' and 'warning_closed' close in our favour and change nothing.
            if (($object['status'] ?? '') !== 'lost') {
                return PaymentEvent::ignored();
            }
            // The dispute object carries payment_intent directly, so a lost dispute
            // revokes through exactly the same key as a refund.
            $paymentRef = !empty($object['payment_intent']) ? (string) $object['payment_intent'] : null;
            if ($paymentRef === null) {
                return PaymentEvent::ignored();
            }
            $e = new PaymentEvent(
                PaymentEvent::ACCESS_REVOKED,
                null, null, null, null,
                $paymentRef,
                $externalRef
            );
            $e->reason = PaymentEvent::REASON_CHARGEBACK;
            if (isset($object['amount'])) {
                $e->amount = (int) $object['amount'];
            }
            if (!empty($object['currency'])) {
                $e->currency = strtoupper((string) $object['currency']);
            }
            return $e;
        }

        if ($type === 'customer.subscription.deleted') {
            $subscriptionId = !empty($object['id']) ? (string) $object['id'] : null;
            if ($subscriptionId === null) {
                return PaymentEvent::ignored();
            }
            return new PaymentEvent(
                PaymentEvent::ACCESS_REVOKED,
                null, null, null,
                $subscriptionId,
                null,
                $externalRef
            );
        }

        return PaymentEvent::ignored();
    }
}
