<?php

namespace go\modules\community\marketplace\tests;

use Firebase\JWT\JWT;
use go\modules\community\marketplace\lib\LicenseVerifier;
use go\modules\community\marketplaceserver\lib\KeyPair;
use go\modules\community\marketplaceserver\lib\LicenseBuilder;
use PHPUnit\Framework\TestCase;

final class LicenseVerifierTest extends TestCase
{
    private const NOW = 1800000000;

    /**
     * NOW (2027) may be in the future relative to the real clock. firebase/php-jwt
     * throws BeforeValidException on a future `iat` unless JWT::$timestamp pins
     * "now" for the decode call. LicenseVerifier catches all decode throwables
     * internally (→ null claims), so without this pin every assertTrue() below
     * would fail even though the verifier logic itself is correct.
     */
    protected function setUp(): void
    {
        JWT::$timestamp = self::NOW;
    }

    protected function tearDown(): void
    {
        JWT::$timestamp = null;
    }

    /** @return array{0:string,1:string} [jwt, publicKeyPem] */
    private function signed(array $licenses, string $host = 'client.example.com'): array
    {
        $pair = KeyPair::generate();
        $jwt = LicenseBuilder::build('https://m.example.com', 1, $host, 'sf', $licenses, $pair['private'], self::NOW);
        return [$jwt, $pair['public']];
    }

    public function testValidModuleLicensePasses(): void
    {
        [$jwt, $pub] = $this->signed(['sf/chat' => ['expiresAt' => null]]);
        $v = new LicenseVerifier($jwt, $pub, 'client.example.com', self::NOW);
        $this->assertTrue($v->has('sf', 'chat'));
        $this->assertFalse($v->has('sf', 'tours'));
    }

    public function testWildcardSubscriptionCoversAnyModule(): void
    {
        [$jwt, $pub] = $this->signed(['sf/*' => ['expiresAt' => self::NOW + 1000]]);
        $v = new LicenseVerifier($jwt, $pub, 'client.example.com', self::NOW);
        $this->assertTrue($v->has('sf', 'chat'));
        $this->assertTrue($v->has('sf', 'anything'));
    }

    public function testExpiredModuleFails(): void
    {
        [$jwt, $pub] = $this->signed(['sf/chat' => ['expiresAt' => self::NOW - 1]]);
        $v = new LicenseVerifier($jwt, $pub, 'client.example.com', self::NOW);
        $this->assertFalse($v->has('sf', 'chat'));
    }

    public function testWrongHostnameFails(): void
    {
        [$jwt, $pub] = $this->signed(['sf/chat' => ['expiresAt' => null]], 'other.example.com');
        $v = new LicenseVerifier($jwt, $pub, 'client.example.com', self::NOW);
        $this->assertFalse($v->has('sf', 'chat'));
    }

    public function testWildcardHostnameMatches(): void
    {
        [$jwt, $pub] = $this->signed(['sf/chat' => ['expiresAt' => null]], '*.example.com');
        $v = new LicenseVerifier($jwt, $pub, 'node1.example.com', self::NOW);
        $this->assertTrue($v->has('sf', 'chat'));
    }

    /**
     * A bare "*" (or "*." with no suffix label) must NOT act as a universal
     * license. Even if such a JWT were somehow signed, the verifier rejects it on
     * any host it isn't literally bound to.
     */
    public function testBareWildcardHostnameDoesNotMatchEverything(): void
    {
        foreach (['*', '*.', ' * '] as $bound) {
            [$jwt, $pub] = $this->signed(['sf/chat' => ['expiresAt' => null]], $bound);
            $v = new LicenseVerifier($jwt, $pub, 'attacker.example.org', self::NOW);
            $this->assertFalse($v->has('sf', 'chat'), "bound '$bound' must not license arbitrary host");
        }
    }

    /**
     * A JWT carrying an EMPTY hostname claim must NOT license any host. The server
     * never signs such a token, but the verifier fails closed so a host-less JWT
     * (a future bug / alternate signing path) can never become a universal license.
     */
    public function testEmptyHostnameClaimFailsClosed(): void
    {
        [$jwt, $pub] = $this->signed(['sf/chat' => ['expiresAt' => null]], '');
        $v = new LicenseVerifier($jwt, $pub, 'client.example.com', self::NOW);
        $this->assertFalse($v->has('sf', 'chat'));
    }

    public function testTamperedSignatureFails(): void
    {
        [$jwt, $pub] = $this->signed(['sf/chat' => ['expiresAt' => null]]);
        $bad = KeyPair::generate()['public'];         // different key
        $v = new LicenseVerifier($jwt, $bad, 'client.example.com', self::NOW);
        $this->assertFalse($v->has('sf', 'chat'));    // signature verify fails → no license
    }

    public function testWrongPackageFails(): void
    {
        [$jwt, $pub] = $this->signed(['sf/chat' => ['expiresAt' => null]]);
        $v = new LicenseVerifier($jwt, $pub, 'client.example.com', self::NOW);
        $this->assertFalse($v->has('amd', 'chat'));   // token package is sf
    }

    public function testGarbageJwtFails(): void
    {
        $pub = KeyPair::generate()['public'];
        $v = new LicenseVerifier('not-a-jwt', $pub, 'client.example.com', self::NOW);
        $this->assertFalse($v->has('sf', 'chat'));
    }

    public function testTokenWithoutExpIsRefused(): void
    {
        $pair = KeyPair::generate();
        $jwt = JWT::encode([
            'iss' => 'https://m.example.com', 'sub' => 1, 'hostname' => 'client.example.com',
            'package' => 'sf', 'iat' => self::NOW, 'licenses' => ['sf/chat' => ['expiresAt' => null]],
        ], $pair['private'], 'RS256');
        $v = new LicenseVerifier($jwt, $pair['public'], 'client.example.com', self::NOW);
        $this->assertFalse($v->isValidFor('sf'));
        $this->assertFalse($v->has('sf', 'chat'));
    }

    /**
     * A perpetual module stops working once the token itself is older than its
     * 14-day lifetime: an instance that stops refreshing cannot keep it forever.
     */
    public function testExpiredTokenRevokesEvenPerpetualModules(): void
    {
        [$jwt, $pub] = $this->signed(['sf/chat' => ['expiresAt' => null]]);
        $ttl = LicenseBuilder::TTL_SECONDS;
        $this->assertTrue((new LicenseVerifier($jwt, $pub, 'client.example.com', self::NOW + $ttl))->has('sf', 'chat'));
        $this->assertFalse((new LicenseVerifier($jwt, $pub, 'client.example.com', self::NOW + $ttl + 1))->has('sf', 'chat'));
    }

    /**
     * A non-integer expiresAt must not read as "still valid". PHP 8 compares a
     * non-numeric string with an int as strings, so "never" >= 1800000000 is
     * true — which would license the module forever.
     */
    public function testNonIntegerExpiresAtIsNotLicensed(): void
    {
        [$jwt, $pub] = $this->signed(['sf/chat' => ['expiresAt' => 'never']]);
        $v = new LicenseVerifier($jwt, $pub, 'client.example.com', self::NOW);
        $this->assertFalse($v->has('sf', 'chat'));
    }

    public function testHostnameComparisonIgnoresCaseTrailingDotAndPort(): void
    {
        [$jwt, $pub] = $this->signed(['sf/chat' => ['expiresAt' => null]], 'Client.Example.com');
        foreach (['client.example.com', 'CLIENT.example.com.', 'client.example.com:8080'] as $host) {
            $v = new LicenseVerifier($jwt, $pub, $host, self::NOW);
            $this->assertTrue($v->has('sf', 'chat'), "host '$host'");
        }
    }

    public function testIsValidForChecksPackageAndHost(): void
    {
        [$jwt, $pub] = $this->signed([]);
        $this->assertTrue((new LicenseVerifier($jwt, $pub, 'client.example.com', self::NOW))->isValidFor('sf'));
        $this->assertFalse((new LicenseVerifier($jwt, $pub, 'client.example.com', self::NOW))->isValidFor('amd'));
        $this->assertFalse((new LicenseVerifier($jwt, $pub, 'other.example.com', self::NOW))->isValidFor('sf'));
    }
}
