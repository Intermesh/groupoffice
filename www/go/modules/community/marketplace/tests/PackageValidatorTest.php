<?php

namespace go\modules\community\marketplace\tests;

use go\modules\community\marketplace\lib\PackageValidator;
use PHPUnit\Framework\TestCase;

final class PackageValidatorTest extends TestCase
{
    public function testAcceptsSingleModuleRoot(): void
    {
        $err = PackageValidator::validateEntries([
            'chat/', 'chat/Module.php', 'chat/model/Channel.php', 'chat/views/extjs3/Module.js',
        ], 'chat');
        $this->assertNull($err);
    }

    public function testRejectsParentTraversal(): void
    {
        $this->assertNotNull(PackageValidator::validateEntries(['chat/', 'chat/../evil.php'], 'chat'));
        $this->assertNotNull(PackageValidator::validateEntries(['../evil.php'], 'chat'));
    }

    public function testRejectsAbsolutePath(): void
    {
        $this->assertNotNull(PackageValidator::validateEntries(['/etc/passwd'], 'chat'));
    }

    public function testRejectsWrongOrMultipleRoot(): void
    {
        $this->assertNotNull(PackageValidator::validateEntries(['other/', 'other/Module.php'], 'chat'));
        $this->assertNotNull(PackageValidator::validateEntries(['chat/Module.php', 'other/x.php'], 'chat'));
    }

    public function testRejectsBackslashTraversal(): void
    {
        $this->assertNotNull(PackageValidator::validateEntries(['chat\\..\\evil.php'], 'chat'));
    }

    public function testRejectsEmptyList(): void
    {
        $this->assertNotNull(PackageValidator::validateEntries([], 'chat'));
    }

    public function testModuleNameMustBeSafe(): void
    {
        // guards against a caller passing a crafted module name
        $this->assertNotNull(PackageValidator::validateEntries(['x/'], '../x'));
        $this->assertNotNull(PackageValidator::validateEntries(['x/'], 'a/b'));
    }
}
