<?php

namespace go\modules\community\marketplaceserver\tests;

use go\modules\community\marketplace\lib\PackageSigner as ClientVerifier;
use go\modules\community\marketplaceserver\lib\KeyPair;
use go\modules\community\marketplaceserver\lib\PackageSigner as ServerSigner;
use PHPUnit\Framework\TestCase;

/**
 * The server signs a package, the client verifies it against the pinned public
 * key. Exercised as a pair so a regression on either side is caught.
 */
final class PackageSignerTest extends TestCase
{
    public function testSignedPackageVerifiesWithMatchingPublicKey(): void
    {
        $pair = KeyPair::generate();
        $bytes = "PK\x03\x04 fake zip bytes";

        $sig = ServerSigner::sign($bytes, $pair['private']);
        $this->assertTrue(ClientVerifier::verify($bytes, $sig, $pair['public']));
    }

    public function testTamperedBytesFailVerification(): void
    {
        $pair = KeyPair::generate();
        $sig = ServerSigner::sign('original', $pair['private']);
        $this->assertFalse(ClientVerifier::verify('tampered', $sig, $pair['public']));
    }

    public function testWrongKeyFailsVerification(): void
    {
        $signer = KeyPair::generate();
        $other = KeyPair::generate();
        $sig = ServerSigner::sign('data', $signer['private']);
        $this->assertFalse(ClientVerifier::verify('data', $sig, $other['public']));
    }

    public function testGarbageSignatureIsRejected(): void
    {
        $pair = KeyPair::generate();
        $this->assertFalse(ClientVerifier::verify('data', '', $pair['public']));
        $this->assertFalse(ClientVerifier::verify('data', 'not-base64-@@@', $pair['public']));
        $this->assertFalse(ClientVerifier::verify('data', base64_encode('short'), $pair['public']));
    }
}
