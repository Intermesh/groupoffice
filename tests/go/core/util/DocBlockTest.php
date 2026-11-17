<?php

namespace go\core\util;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The description of a doc block is its summary, or the text after the type of "@var"
 */
class DocBlockTest extends TestCase
{
	public static function docComments(): array
	{
		return [
			'summary' => ["/** The name of the note */", 'The name of the note'],
			'var with description' => ["/** @var string 'needs-action', 'accepted' or 'declined' */", "'needs-action', 'accepted' or 'declined'"],
			'var on several lines' => ["/**\n\t * @var int mask of\n\t * the roles\n\t */", 'mask of the roles'],
			'var with generic type' => ["/** @var array<string, int> Count per name */", 'Count per name'],
			'var with variable name' => ["/** @var ?string \$comment A note from the participant */", 'A note from the participant'],
			'var without description' => ["/** @var string */", null],
			'summary wins' => ["/**\n * The summary\n *\n * @var string Other text\n */", 'The summary'],
			'no doc block' => [false, null]
		];
	}

	#[DataProvider('docComments')]
	public function testDescription(string|false $docComment, ?string $expected): void
	{
		$this->assertSame($expected, DocBlock::description($docComment));
	}
}
