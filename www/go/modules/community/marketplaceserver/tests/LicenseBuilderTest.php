<?php

namespace go\modules\community\marketplaceserver\tests;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use go\modules\community\marketplaceserver\lib\KeyPair;
use go\modules\community\marketplaceserver\lib\LicenseBuilder;
use PHPUnit\Framework\TestCase;

final class LicenseBuilderTest extends TestCase
{
    private const NOW = 1800000000;

    public function testModuleEntitlementYieldsModuleKey(): void
    {
        $licenses = LicenseBuilder::resolveLicenses('sf', [
            ['type' => 'module', 'modules' => ['chat'], 'expiresAt' => null],
        ], self::NOW);

        $this->assertSame(['sf/chat' => ['expiresAt' => null]], $licenses);
    }

    public function testCollectionExpandsToMemberModules(): void
    {
        $exp = self::NOW + 86400;
        $licenses = LicenseBuilder::resolveLicenses('sf', [
            ['type' => 'collection', 'modules' => ['chat', 'tours'], 'expiresAt' => $exp],
        ], self::NOW);

        $this->assertSame(['sf/chat' => ['expiresAt' => $exp], 'sf/tours' => ['expiresAt' => $exp]], $licenses);
    }

    public function testEntitlementWithoutModulesYieldsNothing(): void
    {
        // The 'subscription' product type (which used to expand to the "sf/*"
        // wildcard) was removed — the marketplace sells modules and collections
        // only. An entitlement carrying no modules therefore licenses nothing.
        $licenses = LicenseBuilder::resolveLicenses('sf', [
            ['type' => 'collection', 'modules' => [], 'expiresAt' => self::NOW + 86400],
        ], self::NOW);

        $this->assertSame([], $licenses);
    }

    public function testOverlapKeepsMaximumExpiryAndNullWins(): void
    {
        $sooner = self::NOW + 100;
        $later = self::NOW + 200;

        $licenses = LicenseBuilder::resolveLicenses('sf', [
            ['type' => 'module', 'modules' => ['chat'], 'expiresAt' => $sooner],
            ['type' => 'module', 'modules' => ['chat'], 'expiresAt' => $later],
        ], self::NOW);
        $this->assertSame($later, $licenses['sf/chat']['expiresAt']);

        $licenses = LicenseBuilder::resolveLicenses('sf', [
            ['type' => 'module', 'modules' => ['chat'], 'expiresAt' => $sooner],
            ['type' => 'module', 'modules' => ['chat'], 'expiresAt' => null],
        ], self::NOW);
        $this->assertNull($licenses['sf/chat']['expiresAt']);
    }

    public function testNotPermittedRowsAreExcluded(): void
    {
        // Per-host instance binding: a row the caller marked not-permitted (seat
        // over limit, or hostname pinned elsewhere) contributes no modules.
        $licenses = LicenseBuilder::resolveLicenses('sf', [
            ['type' => 'module', 'modules' => ['chat'], 'expiresAt' => null, 'permitted' => true],
            ['type' => 'module', 'modules' => ['tours'], 'expiresAt' => null, 'permitted' => false],
        ], self::NOW);

        $this->assertSame(['sf/chat' => ['expiresAt' => null]], $licenses);
    }

    public function testMissingPermittedKeyDefaultsToIncluded(): void
    {
        $licenses = LicenseBuilder::resolveLicenses('sf', [
            ['type' => 'module', 'modules' => ['chat'], 'expiresAt' => null],
        ], self::NOW);

        $this->assertSame(['sf/chat' => ['expiresAt' => null]], $licenses);
    }

    public function testExpiredEntitlementsAreDropped(): void
    {
        $licenses = LicenseBuilder::resolveLicenses('sf', [
            ['type' => 'module', 'modules' => ['chat'], 'expiresAt' => self::NOW - 1],
        ], self::NOW);

        $this->assertSame([], $licenses);
    }

    public function testBuildSignsVerifiableJwtWithExpectedClaims(): void
    {
        $pair = KeyPair::generate();
        $licenses = ['sf/chat' => ['expiresAt' => null]];

        $jwt = LicenseBuilder::build(
            'https://market.example.com', 42, 'client.example.com', 'sf',
            $licenses, $pair['private'], self::NOW
        );
        // Pin the library's internal "now" so JWT::decode()'s iat-in-the-future
        // guard is evaluated relative to the fixed self::NOW test clock instead
        // of the real wall clock (self::NOW may be in the future relative to
        // whenever this test actually runs).
        JWT::$timestamp = self::NOW;
        try {
            $claims = JWT::decode($jwt, new Key($pair['public'], 'RS256'));
        } finally {
            JWT::$timestamp = null;
        }

        $this->assertSame('https://market.example.com', $claims->iss);
        $this->assertSame(42, $claims->sub);
        $this->assertSame('client.example.com', $claims->hostname);
        $this->assertSame('sf', $claims->package);
        $this->assertSame(self::NOW, $claims->iat);
        $this->assertNull($claims->licenses->{'sf/chat'}->expiresAt);
    }
}
