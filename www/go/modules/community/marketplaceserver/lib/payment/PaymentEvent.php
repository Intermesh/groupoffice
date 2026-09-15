<?php

namespace go\modules\community\marketplaceserver\lib\payment;

/**
 * A gateway-agnostic, already-verified payment outcome. Each concrete gateway
 * translates its own webhook payload into one of these, so the Module's webhook
 * handler grants/revokes entitlements without knowing anything gateway-specific.
 * Pure value object — no GO deps.
 */
class PaymentEvent
{
    /** A one-off purchase (or an initial subscription payment) succeeded. */
    const PURCHASE_COMPLETED = 'purchase.completed';

    /** A subscription renewed for another period (extends expiry). */
    const SUBSCRIPTION_RENEWED = 'subscription.renewed';

    /** A subscription ended / was canceled / a charge was refunded (revoke). */
    const ACCESS_REVOKED = 'access.revoked';

    /** A payload we recognised + verified but do not act on (acknowledge with 200). */
    const IGNORED = 'ignored';

    /**
     * @var string one of the constants above
     */
    public $type;

    /**
     * @var int|null the marketplace customer id (from checkout metadata)
     */
    public $customerId;

    /**
     * @var int|null the marketplace product id (from checkout metadata)
     */
    public $productId;

    /**
     * @var int|null new entitlement expiry as a unix timestamp, or null = perpetual
     */
    public $expiresAt;

    /**
     * @var string|null the gateway's subscription id, when this is a subscription
     */
    public $subscriptionId;

    /**
     * @var string|null the gateway's payment reference (payment-intent) for a
     *   one-off purchase, so a later refund can be linked back to its grant
     */
    public $paymentRef;

    /**
     * @var string|null the gateway's own event/object id, for idempotency/audit
     */
    public $externalRef;

    /**
     * @var int|null the transacted amount in the currency's minor unit (cents),
     *   for the activity log — set on purchase / refund, else null
     */
    public $amount;

    /**
     * @var string|null ISO currency for {@see $amount}
     */
    public $currency;

    /**
     * @param string $type
     * @param int|null $customerId
     * @param int|null $productId
     * @param int|null $expiresAt
     * @param string|null $subscriptionId
     * @param string|null $paymentRef
     * @param string|null $externalRef
     */
    public function __construct(
        string $type,
        ?int $customerId = null,
        ?int $productId = null,
        ?int $expiresAt = null,
        ?string $subscriptionId = null,
        ?string $paymentRef = null,
        ?string $externalRef = null
    ) {
        $this->type = $type;
        $this->customerId = $customerId;
        $this->productId = $productId;
        $this->expiresAt = $expiresAt;
        $this->subscriptionId = $subscriptionId;
        $this->paymentRef = $paymentRef;
        $this->externalRef = $externalRef;
    }

    /**
     * @return \go\modules\community\marketplaceserver\lib\payment\PaymentEvent
     */
    public static function ignored(): self
    {
        return new self(self::IGNORED);
    }
}
