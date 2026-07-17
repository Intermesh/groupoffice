<?php

namespace go\modules\community\marketplaceserver\tests;

use go\modules\community\marketplaceserver\lib\payment\StripeSignature;
use PHPUnit\Framework\TestCase;

final class StripeSignatureTest extends TestCase
{
    private const SECRET = 'whsec_test_secret';
    private const NOW = 1800000000;

    /** Build a valid Stripe-Signature header for a payload at time $t. */
    private function header(string $payload, int $t, string $secret = self::SECRET): string
    {
        $sig = hash_hmac('sha256', $t . '.' . $payload, $secret);
        return 't=' . $t . ',v1=' . $sig;
    }

    public function testValidSignaturePasses(): void
    {
        $payload = '{"id":"evt_1","type":"checkout.session.completed"}';
        $header = $this->header($payload, self::NOW);
        $this->assertTrue(StripeSignature::verify($payload, $header, self::SECRET, self::NOW));
    }

    public function testTamperedPayloadFails(): void
    {
        $payload = '{"id":"evt_1"}';
        $header = $this->header($payload, self::NOW);
        $this->assertFalse(StripeSignature::verify($payload . 'x', $header, self::SECRET, self::NOW));
    }

    public function testWrongSecretFails(): void
    {
        $payload = '{"id":"evt_1"}';
        $header = $this->header($payload, self::NOW, 'whsec_other');
        $this->assertFalse(StripeSignature::verify($payload, $header, self::SECRET, self::NOW));
    }

    public function testStaleTimestampFails(): void
    {
        $payload = '{"id":"evt_1"}';
        $header = $this->header($payload, self::NOW - 4000);   // > tolerance
        $this->assertFalse(StripeSignature::verify($payload, $header, self::SECRET, self::NOW));
    }

    public function testFutureTimestampFails(): void
    {
        $payload = '{"id":"evt_1"}';
        $header = $this->header($payload, self::NOW + 4000);
        $this->assertFalse(StripeSignature::verify($payload, $header, self::SECRET, self::NOW));
    }

    public function testMissingOrEmptyHeaderFails(): void
    {
        $this->assertFalse(StripeSignature::verify('{}', null, self::SECRET, self::NOW));
        $this->assertFalse(StripeSignature::verify('{}', '', self::SECRET, self::NOW));
    }

    public function testEmptySecretFails(): void
    {
        $payload = '{}';
        $header = $this->header($payload, self::NOW);
        $this->assertFalse(StripeSignature::verify($payload, $header, '', self::NOW));
    }

    public function testMultipleV1SignaturesOneValid(): void
    {
        $payload = '{"id":"evt_1"}';
        $valid = hash_hmac('sha256', self::NOW . '.' . $payload, self::SECRET);
        $header = 't=' . self::NOW . ',v1=deadbeef,v1=' . $valid;
        $this->assertTrue(StripeSignature::verify($payload, $header, self::SECRET, self::NOW));
    }
}
