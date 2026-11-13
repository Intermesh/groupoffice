<?php

namespace go\core\orm;

use go\core\data\Model;
use go\core\db\Table;
use go\core\util\DocBlock;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;
use Throwable;

/**
 * Builds the JSON schema (draft 2020-12) of an entity or property as it's returned and accepted by the JMAP API.
 *
 * The schema is built from {@see Model::buildApiProperties()} for the public properties and their descriptions, and
 * from the ORM mapping for the types of columns and relations. Related properties, like the addresses of a contact,
 * are added to "$defs".
 *
 * Besides standard JSON schema keywords it uses "x-phpType" for the PHP type when it could not be converted to a JSON type.
 *
 * Required properties are not given. Many NOT NULL columns are filled by the server on save, so the database can't tell
 * what a client must send. Foo/set returns an "invalidProperties" error with the missing properties.
 *
 * @example
 * ```
 * $schema = PropertySchema::build(Note::class);
 * ```
 */
final class PropertySchema
{
	/**
	 * Definition name => schema
	 */
	private array $defs = [];

	/**
	 * Definition name => class name
	 *
	 * @var array<string, class-string<Property>>
	 */
	private array $defClasses = [];

	private function __construct(private readonly bool $withCustomFields)
	{
	}

	/**
	 * Build the JSON schema of an entity or property class
	 *
	 * The root is a reference to the definition of the given class in "$defs". When embedding this schema in another
	 * schema, move "$defs" to the root of that schema.
	 *
	 * @param class-string<Property> $cls
	 * @param bool $withCustomFields Describe each custom field of the "customFields" property. Custom fields are installation
	 *  specific, so leave this off for public documentation. The fields are not checked against the permissions of their
	 *  field set, so filter them for the user when exposing this to a client.
	 * @return array
	 */
	public static function build(string $cls, bool $withCustomFields = false): array
	{
		$builder = new self($withCustomFields);
		$name = $builder->addDef($cls);

		return [
			'$schema' => 'https://json-schema.org/draft/2020-12/schema',
			'$ref' => '#/$defs/' . $name,
			'$defs' => $builder->defs
		];
	}

	/**
	 * Add the definition of a class and return its name in "$defs"
	 *
	 * @param class-string<Property> $cls
	 */
	private function addDef(string $cls): string
	{
		$name = array_search($cls, $this->defClasses, true);
		if ($name !== false) {
			return $name;
		}

		$name = $this->defName($cls);

		// Reserve the name before building so that circular relations end here
		$this->defClasses[$name] = $cls;
		$this->defs[$name] = [];
		$this->defs[$name] = $this->objectSchema($cls);

		return $name;
	}

	/**
	 * Entities use their entity name. Other properties the short class name, or the full class name when that's taken.
	 *
	 * @param class-string<Property> $cls
	 */
	private function defName(string $cls): string
	{
		if (is_a($cls, Entity::class, true)) {
			// Not entityType(), because that registers classes that are not an entity type yet in the database
			$name = $cls::getClientName();
		} else {
			$name = (new ReflectionClass($cls))->getShortName();
		}

		if (isset($this->defClasses[$name])) {
			$name = str_replace('\\', '.', $cls);
		}

		return $name;
	}

	/**
	 * @param class-string<Property> $cls
	 */
	private function objectSchema(string $cls): array
	{
		$mapping = $cls::getMapping();

		$properties = [];

		foreach ($this->apiProperties($cls) as $name => $meta) {
			$access = $this->accessOfNonPublicProperty($cls, $name);
			if ($access === false) {
				continue;
			}
			if (isset($access)) {
				$meta['access'] = $access;
			}

			$schema = $this->propertySchema($cls, $name, $meta, $mapping);

			if (!empty($meta['description'])) {
				$schema['description'] = trim($meta['description']);
			}

			if ($name === 'id' || $meta['access'] === Model::PROP_PUBLIC_READONLY) {
				$schema['readOnly'] = true;
			}

			$properties[$name] = $schema;
		}

		ksort($properties, SORT_STRING | SORT_FLAG_CASE);

		return [
			'type' => 'object',
			'properties' => (object) $properties
		];
	}

	/**
	 * The API properties from {@see Model::buildApiProperties()}, plus protected properties with a public getter. The
	 * docs mode of buildApiProperties() drops those, for example the permissionLevel of an entity.
	 *
	 * @param class-string<Property> $cls
	 */
	private function apiProperties(string $cls): array
	{
		$props = $cls::buildApiProperties(true);

		foreach ((new ReflectionClass($cls))->getProperties() as $prop) {
			$name = $prop->getName();
			if (isset($props[$name]) || $prop->isStatic() || $prop->isPublic() || in_array($name, $cls::atypicalApiProperties())) {
				continue;
			}

			$getter = 'get' . ucfirst($name);
			if (!method_exists($cls, $getter)) {
				continue;
			}
			$method = new ReflectionMethod($cls, $getter);
			if (!$method->isPublic() || $method->isStatic() || $method->getNumberOfRequiredParameters() > 0) {
				continue;
			}

			$returnType = $method->getReturnType();
			$props[$name] = [
				'access' => Model::PROP_PUBLIC_READONLY,
				'type' => $returnType instanceof ReflectionNamedType ? ($returnType->allowsNull() ? '?' : '') . $returnType->getName() : null,
				'description' => $this->docDescription($method->getDocComment())
			];
		}

		return $props;
	}

	/**
	 * Description from a doc block
	 */
	private function docDescription(string|false $docComment): ?string
	{
		return DocBlock::description($docComment);
	}

	/**
	 * Mapped columns are added by {@see Property::buildApiProperties()} even when the PHP property is protected, like
	 * the contactId of a contact's e-mail address. Those are not in the API unless there's a public getter.
	 *
	 * @param class-string<Property> $cls
	 * @return int|false|null The access when the property is not public, false when it's not in the API, null when it's public or not declared
	 */
	private function accessOfNonPublicProperty(string $cls, string $name): int|false|null
	{
		if (!property_exists($cls, $name) || (new ReflectionProperty($cls, $name))->isPublic()) {
			return null;
		}

		$hasPublic = fn(string $method) => method_exists($cls, $method) && (new ReflectionMethod($cls, $method))->isPublic();

		if (!$hasPublic('get' . ucfirst($name))) {
			return false;
		}

		return $hasPublic('set' . ucfirst($name)) ? Model::PROP_PUBLIC : Model::PROP_PUBLIC_READONLY;
	}

	/**
	 * Schema of a single property. The mapping is used first because it knows the database and relation types. The PHP
	 * type is the fallback.
	 *
	 * @param class-string<Property> $cls
	 */
	private function propertySchema(string $cls, string $name, array $meta, Mapping $mapping): array
	{
		if ($name === 'customFields' && method_exists($cls, 'getCustomFieldModels')) {
			return $this->customFieldsSchema($cls);
		}

		$relation = $mapping->getRelation($name);
		if ($relation) {
			$schema = $this->relationSchema($relation);
			if (isset($schema)) {
				return $schema;
			}
		}

		$column = $mapping->getColumn($name) ?: $mapping->getScalarProperty($name);
		if ($column) {
			$schema = $column->toJsonSchema();
			// The declared PHP type is what the API returns. For example ID columns are integers but string properties.
			$declared = $this->declaredJsonTypes($cls, $name);
			if (isset($declared)) {
				$schema['type'] = count($declared) == 1 ? $declared[0] : $declared;
				if (!in_array('string', $declared)) {
					unset($schema['format'], $schema['maxLength']);
				}
			}
			return $schema;
		}

		return isset($meta['type']) ? $this->phpTypeSchema($meta['type']) : [];
	}

	/**
	 * JSON types of the declared type of a public PHP property
	 *
	 * @param class-string<Property> $cls
	 * @return string[]|null Null when the property has no type declaration or a type that's not a plain scalar
	 */
	private function declaredJsonTypes(string $cls, string $name): ?array
	{
		if (!property_exists($cls, $name)) {
			return null;
		}

		$type = (new ReflectionProperty($cls, $name))->getType();
		if (!$type instanceof ReflectionNamedType) {
			return null;
		}

		$jsonType = match ($type->getName()) {
			'int' => 'integer',
			'float' => 'number',
			'bool' => 'boolean',
			'string' => 'string',
			default => null
		};

		if (!isset($jsonType)) {
			return null;
		}

		return $type->allowsNull() ? [$jsonType, 'null'] : [$jsonType];
	}

	/**
	 * @return array|null Null when the relation type is unknown
	 */
	private function relationSchema(Relation $relation): ?array
	{
		if ($relation->type === Relation::TYPE_SCALAR) {
			try {
				$column = Table::getInstance($relation->tableName)->getColumn($relation->getScalarColumn());
				$items = $column ? $column->toJsonSchema() : [];
			} catch (Throwable) {
				$items = [];
			}
			return ['type' => 'array', 'items' => $items];
		}

		if (!isset($relation->propertyName) || !is_a($relation->propertyName, Property::class, true)) {
			return null;
		}

		$ref = ['$ref' => '#/$defs/' . $this->addDef($relation->propertyName)];

		return match ($relation->type) {
			Relation::TYPE_HAS_ONE => ['anyOf' => [$ref, ['type' => 'null']]],
			Relation::TYPE_ARRAY => ['type' => 'array', 'items' => $ref],
			Relation::TYPE_MAP => ['type' => ['object', 'null'], 'additionalProperties' => $ref, 'description' => 'Map with the ID as key'],
			default => null
		};
	}

	/**
	 * @param class-string<Property> $cls
	 */
	private function customFieldsSchema(string $cls): array
	{
		$schema = [
			'type' => 'object',
			'description' => 'Custom field values with the database name of the field as key.'
		];

		if ($this->withCustomFields) {
			$fields = [];
			/** @var \go\core\model\Field[] $models */
			$models = call_user_func([$cls, 'getCustomFieldModels']);
			foreach ($models as $field) {
				if (empty($field->databaseName)) {
					continue;
				}
				// name may be missing in models cached before it was selected
				$fields[$field->databaseName] = [
					'description' => (isset($field->name) ? 'Custom field "' . $field->name . '"' : 'Custom field') . ' (' . $field->type . ').'
				];
			}
			ksort($fields, SORT_STRING | SORT_FLAG_CASE);
			$schema['properties'] = (object) $fields;
		}

		return $schema;
	}

	/**
	 * Convert a PHP type from a type declaration or doc block, like "?string", "int|null" or "Foo[]", to a schema.
	 */
	private function phpTypeSchema(string $phpType): array
	{
		$types = [];
		$schemas = [];

		foreach (explode('|', $phpType) as $type) {
			$type = trim($type);
			if (str_starts_with($type, '?')) {
				$types[] = 'null';
				$type = substr($type, 1);
			}
			$type = ltrim($type, '\\');

			if (str_ends_with($type, '[]')) {
				$schemas[] = ['type' => 'array', 'items' => $this->phpTypeSchema(substr($type, 0, -2))];
				continue;
			}

			$lower = strtolower($type);
			switch ($lower) {
				case 'int':
				case 'integer':
					$types[] = 'integer';
					break;
				case 'float':
				case 'double':
					$types[] = 'number';
					break;
				case 'bool':
				case 'boolean':
				case 'true':
				case 'false':
					$types[] = 'boolean';
					break;
				case 'string':
					$types[] = 'string';
					break;
				case 'null':
					$types[] = 'null';
					break;
				case 'array':
					$types[] = 'array';
					break;
				case 'mixed':
				case '':
					return [];
				default:
					if (str_ends_with($lower, 'datetime') || str_ends_with($lower, 'datetimeinterface') || str_ends_with($lower, 'datetimeimmutable')) {
						$schemas[] = ['type' => 'string', 'format' => 'date-time'];
					} else if (str_starts_with($lower, 'array<')) {
						$types[] = 'array';
					} else if (class_exists($type) && is_a($type, Property::class, true) && !(new ReflectionClass($type))->isAbstract()) {
						// Abstract classes are left as PHP type because their properties depend on the subclass
						$schemas[] = ['$ref' => '#/$defs/' . $this->addDef($type)];
					} else {
						return ['x-phpType' => $phpType];
					}
			}
		}

		$types = array_values(array_unique($types));
		if (!empty($types)) {
			$schemas[] = ['type' => count($types) == 1 ? $types[0] : $types];
		}

		return count($schemas) == 1 ? $schemas[0] : ['anyOf' => $schemas];
	}
}
