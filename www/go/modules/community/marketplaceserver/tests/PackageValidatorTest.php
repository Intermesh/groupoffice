<?php

namespace go\modules\community\marketplaceserver\tests;

use go\modules\community\marketplaceserver\lib\PackageValidator;
use PHPUnit\Framework\TestCase;

final class PackageValidatorTest extends TestCase
{
    public function testSingleRootedArchiveIsSafe(): void
    {
        $this->assertNull(PackageValidator::validateEntries(
            ['chat/', 'chat/Module.php', 'chat/model/Message.php'],
            'chat'
        ));
    }

    public function testEmptyArchiveRejected(): void
    {
        $this->assertNotNull(PackageValidator::validateEntries([], 'chat'));
    }

    public function testWrongRootRejected(): void
    {
        $this->assertNotNull(PackageValidator::validateEntries(
            ['tours/', 'tours/Module.php'],
            'chat'
        ));
    }

    public function testMultipleRootsRejected(): void
    {
        $this->assertNotNull(PackageValidator::validateEntries(
            ['chat/Module.php', 'evil/backdoor.php'],
            'chat'
        ));
    }

    public function testPathTraversalRejected(): void
    {
        $this->assertNotNull(PackageValidator::validateEntries(
            ['chat/../../../etc/passwd'],
            'chat'
        ));
    }

    public function testAbsolutePathRejected(): void
    {
        $this->assertNotNull(PackageValidator::validateEntries(
            ['/etc/cron.d/evil'],
            'chat'
        ));
        $this->assertNotNull(PackageValidator::validateEntries(
            ['C:\\Windows\\System32\\evil.dll'],
            'chat'
        ));
    }

    public function testUnsafeModuleNameRejected(): void
    {
        $this->assertNotNull(PackageValidator::validateEntries(['x/'], '../evil'));
    }
}
