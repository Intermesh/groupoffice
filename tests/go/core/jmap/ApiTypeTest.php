<?php

namespace go\core\jmap;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ApiTypeTest extends TestCase
{
	public static function typeProvider(): array
	{
		return [
			'scalar' => ['string', ['type' => 'string']],
			'int alias' => ['int', ['type' => 'integer']],
			'mixed' => ['mixed', []],
			'nullable' => ['?string', ['type' => ['string', 'null']]],
			'union' => ['string|int', ['type' => ['string', 'integer']]],
			'list' => ['string[]', ['type' => 'array', 'items' => ['type' => 'string']]],
			'list of union' => ['(string|int)[]', ['type' => 'array', 'items' => ['type' => ['string', 'integer']]]],
			'own entity' => ['Note', ['$ref' => '#/$defs/Note']],
			'other entity' => ['User', ['type' => 'object', 'x-entity' => 'User']],
			'object' => ['{a: string, b?: int}', ['type' => 'object', 'properties' => ['a' => ['type' => 'string'], 'b' => ['type' => 'integer']], 'required' => ['a']]],
			'map' => ['{id: Note}', ['type' => 'object', 'additionalProperties' => ['$ref' => '#/$defs/Note']]],
			'object with id' => ['{id: string}', ['type' => 'object', 'properties' => ['id' => ['type' => 'string']], 'required' => ['id']]],
			'nullable object' => ['?{a: string}', ['anyOf' => [['type' => 'object', 'properties' => ['a' => ['type' => 'string']], 'required' => ['a']], ['type' => 'null']]]],
		];
	}

	#[DataProvider('typeProvider')]
	public function testToJsonSchema(string $type, array $expected): void
	{
		$this->assertEquals($expected, ApiType::toJsonSchema($type, 'Note'));
	}

	public static function invalidTypeProvider(): array
	{
		return [['NoSuchEntity'], ['{a string}'], ['string['], ['string extra']];
	}

	#[DataProvider('invalidTypeProvider')]
	public function testInvalidTypeThrows(string $type): void
	{
		$this->expectException(InvalidArgumentException::class);
		ApiType::toJsonSchema($type, 'Note');
	}

	public function testTypeLength(): void
	{
		$this->assertEquals(6, ApiType::typeLength('string name'));
		$this->assertEquals(19, ApiType::typeLength('{a: string, b: int} The result'));
	}
}
