<?php

namespace go\modules\community\marketplaceserver\tests;

use go\modules\community\marketplaceserver\lib\payment\PaymentEvent;
use go\modules\community\marketplaceserver\lib\payment\StripeGateway;
use PHPUnit\Framework\TestCase;

/**
 * Covers the PURE payload→event mapping (no signature/Settings/HTTP). Signature
 * verification is covered separately in StripeSignatureTest.
 */
final class StripeMapEventTest extends TestCase
{
    public function testPaidCheckoutGrantsWithPaymentRef(): void
    {
        $e = StripeGateway::mapEvent([
            'id' => 'evt_1',
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'payment_status' => 'paid',
                'payment_intent' => 'pi_123',
                'amount_total' => 4900,
                'currency' => 'eur',
                'metadata' => ['customerId' => '7', 'productId' => '42'],
            ]],
        ]);
        $this->assertSame(PaymentEvent::PURCHASE_COMPLETED, $e->type);
        $this->assertSame(7, $e->customerId);
        $this->assertSame(42, $e->productId);
        $this->assertSame('pi_123', $e->paymentRef);
        $this->assertSame(4900, $e->amount);
        $this->assertSame('EUR', $e->currency);
        $this->assertNull($e->expiresAt);
    }

    public function testAsyncPaymentSucceededGrants(): void
    {
        $e = StripeGateway::mapEvent([
            'id' => 'evt_2',
            'type' => 'checkout.session.async_payment_succeeded',
            'data' => ['object' => [
                'payment_status' => 'paid',
                'payment_intent' => 'pi_sepa',
                'metadata' => ['customerId' => '7', 'productId' => '42'],
            ]],
        ]);
        $this->assertSame(PaymentEvent::PURCHASE_COMPLETED, $e->type);
        $this->assertSame(42, $e->productId);
        $this->assertSame('pi_sepa', $e->paymentRef);
        $this->assertSame('evt_2', $e->externalRef);
    }

    public function testUnpaidCheckoutIsIgnored(): void
    {
        $e = StripeGateway::mapEvent([
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['payment_status' => 'unpaid', 'metadata' => ['customerId' => '1', 'productId' => '2']]],
        ]);
        $this->assertSame(PaymentEvent::IGNORED, $e->type);
    }

    public function testCheckoutWithoutMetadataIsIgnored(): void
    {
        $e = StripeGateway::mapEvent([
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['payment_status' => 'paid']],
        ]);
        $this->assertSame(PaymentEvent::IGNORED, $e->type);
    }

    public function testFullRefundRevokesByPaymentRef(): void
    {
        $e = StripeGateway::mapEvent([
            'id' => 'evt_r',
            'type' => 'charge.refunded',
            'data' => ['object' => ['refunded' => true, 'payment_intent' => 'pi_123', 'amount' => 1000, 'amount_refunded' => 1000, 'currency' => 'usd']],
        ]);
        $this->assertSame(PaymentEvent::ACCESS_REVOKED, $e->type);
        $this->assertSame('pi_123', $e->paymentRef);
        $this->assertSame(1000, $e->amount);
        $this->assertSame('USD', $e->currency);
        $this->assertNull($e->subscriptionId);
    }

    public function testFullRefundByAmountRevokes(): void
    {
        $e = StripeGateway::mapEvent([
            'type' => 'charge.refunded',
            'data' => ['object' => ['refunded' => false, 'payment_intent' => 'pi_9', 'amount' => 500, 'amount_refunded' => 500]],
        ]);
        $this->assertSame(PaymentEvent::ACCESS_REVOKED, $e->type);
        $this->assertSame('pi_9', $e->paymentRef);
    }

    public function testPartialRefundIsIgnored(): void
    {
        $e = StripeGateway::mapEvent([
            'type' => 'charge.refunded',
            'data' => ['object' => ['refunded' => false, 'payment_intent' => 'pi_5', 'amount' => 1000, 'amount_refunded' => 400]],
        ]);
        $this->assertSame(PaymentEvent::IGNORED, $e->type);
    }

    public function testRefundWithoutPaymentIntentIsIgnored(): void
    {
        $e = StripeGateway::mapEvent([
            'type' => 'charge.refunded',
            'data' => ['object' => ['refunded' => true, 'amount' => 100, 'amount_refunded' => 100]],
        ]);
        $this->assertSame(PaymentEvent::IGNORED, $e->type);
    }

    public function testLostDisputeRevokesByPaymentRef(): void
    {
        $e = StripeGateway::mapEvent([
            'id' => 'evt_d1',
            'type' => 'charge.dispute.closed',
            'data' => ['object' => [
                'status' => 'lost',
                'payment_intent' => 'pi_999',
                'amount' => 4900,
                'currency' => 'eur',
            ]],
        ]);
        $this->assertSame(PaymentEvent::ACCESS_REVOKED, $e->type);
        $this->assertSame('pi_999', $e->paymentRef);
        $this->assertSame(PaymentEvent::REASON_CHARGEBACK, $e->reason);
        $this->assertSame(4900, $e->amount);
        $this->assertSame('EUR', $e->currency);
    }

    public function testWonDisputeIsIgnored(): void
    {
        $e = StripeGateway::mapEvent([
            'type' => 'charge.dispute.closed',
            'data' => ['object' => ['status' => 'won', 'payment_intent' => 'pi_999']],
        ]);
        $this->assertSame(PaymentEvent::IGNORED, $e->type);
    }

    public function testDisputeWithoutPaymentIntentIsIgnored(): void
    {
        $e = StripeGateway::mapEvent([
            'type' => 'charge.dispute.closed',
            'data' => ['object' => ['status' => 'lost']],
        ]);
        $this->assertSame(PaymentEvent::IGNORED, $e->type);
    }

    public function testFullRefundCarriesRefundReason(): void
    {
        $e = StripeGateway::mapEvent([
            'type' => 'charge.refunded',
            'data' => ['object' => ['refunded' => true, 'payment_intent' => 'pi_1']],
        ]);
        $this->assertSame(PaymentEvent::REASON_REFUND, $e->reason);
    }

    public function testSubscriptionDeletedRevokesBySubscriptionId(): void
    {
        $e = StripeGateway::mapEvent([
            'type' => 'customer.subscription.deleted',
            'data' => ['object' => ['id' => 'sub_77']],
        ]);
        $this->assertSame(PaymentEvent::ACCESS_REVOKED, $e->type);
        $this->assertSame('sub_77', $e->subscriptionId);
        $this->assertNull($e->paymentRef);
    }

    public function testUnknownEventIsIgnored(): void
    {
        $e = StripeGateway::mapEvent(['type' => 'invoice.created', 'data' => ['object' => []]]);
        $this->assertSame(PaymentEvent::IGNORED, $e->type);
    }
}
