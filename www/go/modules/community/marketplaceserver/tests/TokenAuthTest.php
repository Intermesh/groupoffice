<?php

namespace go\modules\community\marketplaceserver\tests;

use go\modules\community\marketplaceserver\lib\TokenAuth;
use PHPUnit\Framework\TestCase;

final class TokenAuthTest extends TestCase
{
    public function testGeneratedTokenHasPrefixAndLength(): void
    {
        $token = TokenAuth::generateToken();
        $this->assertMatchesRegularExpression('/^marketplaceserver_[0-9a-f]{40}$/', $token);
        $this->assertNotSame($token, TokenAuth::generateToken());
    }

    public function testHashIsDeterministicSha256(): void
    {
        $token = TokenAuth::generateToken();
        $this->assertSame(hash('sha256', $token), TokenAuth::hash($token));
        $this->assertSame(TokenAuth::hash($token), TokenAuth::hash($token));
    }

    public function testParseBearerExtractsToken(): void
    {
        $this->assertSame('marketplaceserver_abc', TokenAuth::parseBearer('Bearer marketplaceserver_abc'));
        $this->assertSame('marketplaceserver_abc', TokenAuth::parseBearer('bearer marketplaceserver_abc'));
    }

    public function testParseBearerRejectsGarbage(): void
    {
        $this->assertNull(TokenAuth::parseBearer(null));
        $this->assertNull(TokenAuth::parseBearer(''));
        $this->assertNull(TokenAuth::parseBearer('Basic dXNlcjpwYXNz'));
        $this->assertNull(TokenAuth::parseBearer('Bearer'));
        $this->assertNull(TokenAuth::parseBearer('Bearer  '));
    }
}
