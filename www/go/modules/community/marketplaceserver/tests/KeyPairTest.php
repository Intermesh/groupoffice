<?php

namespace go\modules\community\marketplaceserver\tests;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use go\modules\community\marketplaceserver\lib\KeyPair;
use PHPUnit\Framework\TestCase;

final class KeyPairTest extends TestCase
{
    public function testGenerateProducesValidRsaPemPair(): void
    {
        $pair = KeyPair::generate();

        $this->assertArrayHasKey('private', $pair);
        $this->assertArrayHasKey('public', $pair);
        $this->assertStringContainsString('PRIVATE KEY', $pair['private']);
        $this->assertStringContainsString('PUBLIC KEY', $pair['public']);
        $this->assertNotFalse(openssl_pkey_get_private($pair['private']));
        $this->assertNotFalse(openssl_pkey_get_public($pair['public']));
    }

    public function testPairSignsAndVerifiesRs256Jwt(): void
    {
        $pair = KeyPair::generate();

        $jwt = JWT::encode(['foo' => 'bar'], $pair['private'], 'RS256');
        $decoded = JWT::decode($jwt, new Key($pair['public'], 'RS256'));

        $this->assertSame('bar', $decoded->foo);
    }

    public function testConsecutivePairsDiffer(): void
    {
        $this->assertNotSame(KeyPair::generate()['private'], KeyPair::generate()['private']);
    }
}
