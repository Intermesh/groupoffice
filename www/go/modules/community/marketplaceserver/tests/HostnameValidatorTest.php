<?php

namespace go\modules\community\marketplaceserver\tests;

use go\modules\community\marketplaceserver\lib\HostnameValidator;
use PHPUnit\Framework\TestCase;

final class HostnameValidatorTest extends TestCase
{
    public function testAcceptsConcreteHostnames(): void
    {
        $this->assertTrue(HostnameValidator::isValid('client.example.com'));
        $this->assertTrue(HostnameValidator::isValid('go'));                 // single-label / docker
        $this->assertTrue(HostnameValidator::isValid('localhost.localdomain'));
        $this->assertTrue(HostnameValidator::isValid('a-b.example.co.uk'));
        $this->assertTrue(HostnameValidator::isValid('192.168.1.10'));
        $this->assertTrue(HostnameValidator::isValid('client.example.com:8443'));
    }

    public function testRejectsWildcardsAndListsThatWidenBinding(): void
    {
        $this->assertFalse(HostnameValidator::isValid('*'));
        $this->assertFalse(HostnameValidator::isValid('*.example.com'));
        $this->assertFalse(HostnameValidator::isValid('a.com,b.com'));
        $this->assertFalse(HostnameValidator::isValid('a.com, b.com'));
    }

    public function testRejectsEmptyWhitespaceAndOverlong(): void
    {
        $this->assertFalse(HostnameValidator::isValid(''));
        $this->assertFalse(HostnameValidator::isValid('   '));
        $this->assertFalse(HostnameValidator::isValid('has space.com'));
        $this->assertFalse(HostnameValidator::isValid("evil.com\r\nX-Injected: 1"));
        $this->assertFalse(HostnameValidator::isValid(str_repeat('a', 254)));
        $this->assertFalse(HostnameValidator::isValid('-leadinghyphen.com'));
    }
}
