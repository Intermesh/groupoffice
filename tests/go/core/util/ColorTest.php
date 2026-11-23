<?php
namespace go\core\util;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ColorTest extends TestCase
{
	public static function colors(): array
	{
		return [
			['FF0000', true],
			['ff0000', true],
			['F00', true],
			['F00A', true],
			['FF000080', true],
			['', false],
			['#FF0000', false],
			['FF00000', false],
			['GG0000', false],
			['red', false],
			['FF0000;background:url(x)', false],
			['000"><img src=x onerror=alert(1)>', false],
			["FF0000\n", false],
		];
	}

	#[DataProvider('colors')]
	public function testIsHex(string $color, bool $valid): void
	{
		$this->assertSame($valid, Color::isHex($color));
	}
}
