<?php

namespace go\core\jmap;

use go\core\orm\EntityType;
use InvalidArgumentException;

/**
 * Converts the PHP-like types of the "@apiParam" and "@apiResponse" doc block tags to JSON schema (draft 2020-12).
 *
 * Supported syntax:
 *
 * - Scalars: string, int, float, bool, null, mixed, array and object
 * - Lists: string[] or (string|int)[]
 * - Nullable: ?string or string|null
 * - Objects: {id: string, name?: string}. A "?" after the name makes it optional.
 * - Maps: {id: Note} with "id" as the only key is an object with IDs as keys
 * - Entity names, like Note. The entity of the method itself becomes a "$ref" to "#/$defs/<name>". Other entities
 *   become {"type": "object", "x-entity": "<name>"}.
 *
 * @example
 * ```
 * $schema = ApiType::toJsonSchema('{list: Note[], notFound?: string[]}', 'Note');
 * ```
 */
final class ApiType
{
	private string $type;
	private int $pos = 0;

	private function __construct(string $type, private readonly string $entityName)
	{
		$this->type = trim($type);
	}

	/**
	 * Convert a type to JSON schema
	 *
	 * @param string $type The type, like "string[]" or "{list: Note[]}"
	 * @param string $entityName The entity of the method, which is referenced with "#/$defs/<name>"
	 * @return array
	 * @throws InvalidArgumentException When the type can't be parsed
	 */
	public static function toJsonSchema(string $type, string $entityName): array
	{
		$parser = new self($type, $entityName);
		$schema = $parser->parseUnion();
		$parser->skipSpace();
		if ($parser->pos < strlen($parser->type)) {
			throw new InvalidArgumentException("Unexpected '" . substr($parser->type, $parser->pos) . "' in type '" . $type . "'");
		}
		return $schema;
	}

	/**
	 * Get the length of the type at the start of a string, so that the rest can be read as name and description.
	 *
	 * Spaces inside braces and parentheses belong to the type. For example "{a: string, b: int} The result" returns 20.
	 *
	 * @param string $text Text that starts with a type
	 * @return int
	 */
	public static function typeLength(string $text): int
	{
		$depth = 0;
		for ($i = 0, $l = strlen($text); $i < $l; $i++) {
			$c = $text[$i];
			if ($c === '{' || $c === '(') {
				$depth++;
			} else if ($c === '}' || $c === ')') {
				$depth--;
			} else if ($depth === 0 && ctype_space($c)) {
				return $i;
			}
		}
		return strlen($text);
	}

	private function skipSpace(): void
	{
		while ($this->pos < strlen($this->type) && ctype_space($this->type[$this->pos])) {
			$this->pos++;
		}
	}

	private function peek(): string
	{
		$this->skipSpace();
		return $this->type[$this->pos] ?? '';
	}

	private function expect(string $char): void
	{
		if ($this->peek() !== $char) {
			throw new InvalidArgumentException("Expected '" . $char . "' at position " . $this->pos . " in type '" . $this->type . "'");
		}
		$this->pos++;
	}

	private function parseUnion(): array
	{
		$schemas = [$this->parseList()];
		while ($this->peek() === '|') {
			$this->pos++;
			$schemas[] = $this->parseList();
		}

		// Merge plain types into one "type" array, like ["string", "null"]
		$plain = [];
		$other = [];
		foreach ($schemas as $schema) {
			if (count($schema) === 1 && isset($schema['type'])) {
				$plain = array_merge($plain, (array) $schema['type']);
			} else {
				$other[] = $schema;
			}
		}
		$plain = array_values(array_unique($plain));

		if (empty($other)) {
			return ['type' => count($plain) === 1 ? $plain[0] : $plain];
		}
		if (!empty($plain)) {
			$other[] = ['type' => count($plain) === 1 ? $plain[0] : $plain];
		}
		return count($other) === 1 ? $other[0] : ['anyOf' => $other];
	}

	private function parseList(): array
	{
		$nullable = false;
		if ($this->peek() === '?') {
			$this->pos++;
			$nullable = true;
		}

		$schema = $this->parseAtom();

		while ($this->peek() === '[') {
			$this->pos++;
			$this->expect(']');
			$schema = ['type' => 'array', 'items' => $schema];
		}

		if ($nullable) {
			if (count($schema) === 1 && isset($schema['type'])) {
				$schema['type'] = array_values(array_unique(array_merge((array) $schema['type'], ['null'])));
			} else {
				$schema = ['anyOf' => [$schema, ['type' => 'null']]];
			}
		}

		return $schema;
	}

	private function parseAtom(): array
	{
		$c = $this->peek();

		if ($c === '(') {
			$this->pos++;
			$schema = $this->parseUnion();
			$this->expect(')');
			return $schema;
		}

		if ($c === '{') {
			return $this->parseObject();
		}

		$name = $this->parseName();

		return match (strtolower($name)) {
			'string' => ['type' => 'string'],
			'int', 'integer' => ['type' => 'integer'],
			'float', 'number' => ['type' => 'number'],
			'bool', 'boolean' => ['type' => 'boolean'],
			'null' => ['type' => 'null'],
			'array' => ['type' => 'array'],
			'object' => ['type' => 'object'],
			'mixed' => [],
			default => $this->entitySchema($name)
		};
	}

	private function entitySchema(string $name): array
	{
		if ($name === $this->entityName) {
			return ['$ref' => '#/$defs/' . $name];
		}

		if (!EntityType::existsByName($name)) {
			throw new InvalidArgumentException("Unknown type '" . $name . "' in type '" . $this->type . "'");
		}

		return ['type' => 'object', 'x-entity' => $name];
	}

	private function parseName(): string
	{
		$this->skipSpace();
		if (!preg_match('/[A-Za-z_][A-Za-z0-9_]*/A', $this->type, $matches, 0, $this->pos)) {
			throw new InvalidArgumentException("Expected a type name at position " . $this->pos . " in type '" . $this->type . "'");
		}
		$this->pos += strlen($matches[0]);
		return $matches[0];
	}

	private function parseObject(): array
	{
		$this->expect('{');

		$properties = [];
		$required = [];

		while ($this->peek() !== '}') {
			$name = $this->parseName();
			$optional = false;
			if ($this->peek() === '?') {
				$this->pos++;
				$optional = true;
			}
			$this->expect(':');
			$properties[$name] = $this->parseUnion();
			if (!$optional) {
				$required[] = $name;
			}

			if ($this->peek() === ',') {
				$this->pos++;
			} else {
				break;
			}
		}

		$this->expect('}');

		// {id: Foo} is a map with IDs as keys
		if (array_keys($properties) === ['id'] && $required === ['id'] && !in_array($properties['id']['type'] ?? null, ['string', 'integer'], true)) {
			return ['type' => 'object', 'additionalProperties' => $properties['id']];
		}

		$schema = ['type' => 'object', 'properties' => $properties];
		if (!empty($required)) {
			$schema['required'] = $required;
		}
		return $schema;
	}
}
