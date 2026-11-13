<?php

namespace go\core\jmap;

use go\core\orm\EntityType;
use go\core\orm\PropertySchema;

/**
 * JSON schemas (draft 2020-12) of the parameters and responses of the standard entity methods, like Foo/get and
 * Foo/query, as implemented by {@see EntityController}.
 *
 * The schemas refer to the entity with "#/$defs/<entity name>" and to the filter with "#/$defs/Filter". Use
 * {@see forEntity()} to get them with those definitions.
 *
 * Other methods of a controller declare their parameters and response with doc block tags. See {@see fromDocComment()}.
 */
final class MethodSchema
{
	/**
	 * Get the parameters and response schema of the standard methods
	 *
	 * @param string $entityName Name of the entity definition the schemas refer to
	 * @return array<string, array{params: array, response: array}> Method name => schemas
	 */
	public static function standard(string $entityName): array
	{
		$entity = ['$ref' => '#/$defs/' . $entityName];
		$id = ['type' => 'string'];
		$ids = ['type' => 'array', 'items' => $id];
		$accountId = ['type' => ['string', 'null'], 'description' => 'Not used. GroupOffice has one account per user.'];
		$state = ['type' => 'string', 'description' => 'State of the entity type. It changes when an entity of this type changes.'];
		$setErrors = [
			'type' => ['object', 'null'],
			'additionalProperties' => ['$ref' => '#/$defs/SetError'],
			'description' => 'Errors by ID'
		];

		$capabilities = Capabilities::get();

		return [
			'get' => [
				'params' => self::object([
					'accountId' => $accountId,
					'ids' => ['type' => ['array', 'null'], 'items' => $id, 'description' => 'IDs of the entities to get. When null all readable entities are returned.'],
					'properties' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Properties to return. When empty all default properties are returned. The "id" is always returned.']
				]),
				'response' => self::object([
					'accountId' => $accountId,
					'state' => $state,
					'list' => ['type' => 'array', 'items' => $entity, 'description' => 'The entities in the order of the given IDs.'],
					'notFound' => ['type' => 'array', 'items' => $id, 'description' => 'IDs that don\'t exist or may not be read.']
				])
			],
			'query' => [
				'params' => self::object([
					'accountId' => $accountId,
					'filter' => ['$ref' => '#/$defs/Filter'],
					'sort' => [
						'type' => 'array',
						'items' => self::object([
							'property' => ['type' => 'string', 'description' => 'A property, "customFields.<databaseName>", "creator" or "modifier". Entities may support more.'],
							'isAscending' => ['type' => 'boolean', 'default' => true]
						], ['property'])
					],
					'position' => ['type' => 'integer', 'minimum' => 0, 'default' => 0, 'description' => 'Index of the first ID to return. Only used together with "limit".'],
					'limit' => ['type' => 'integer', 'minimum' => 0, 'default' => 0, 'description' => 'Maximum number of IDs to return. 0 returns all.'],
					'calculateTotal' => ['type' => 'boolean', 'default' => false, 'description' => 'Return the total number of results in "total".'],
					'calculateHasMore' => ['type' => 'boolean', 'default' => false, 'description' => 'Return "hasMore". Faster than calculateTotal. Requires a limit.']
				]),
				'response' => self::object([
					'accountId' => $accountId,
					'queryState' => $state,
					'state' => ['type' => 'string', 'deprecated' => true, 'description' => 'Deprecated. Same as queryState.'],
					'ids' => $ids,
					'notfound' => ['type' => 'array', 'deprecated' => true, 'description' => 'Deprecated. Always empty.'],
					'total' => ['type' => 'integer', 'description' => 'Only with calculateTotal.'],
					'hasMore' => ['type' => 'boolean', 'description' => 'Only with calculateHasMore.'],
					'canCalculateUpdates' => ['type' => 'boolean', 'description' => 'Always false. Foo/queryChanges is not supported.']
				])
			],
			'set' => [
				'params' => self::object([
					'accountId' => $accountId,
					'ifInState' => ['type' => 'string', 'description' => 'Only apply the changes when the current state is this state. Otherwise a "stateMismatch" error is returned.'],
					'create' => ['type' => 'object', 'additionalProperties' => $entity, 'description' => 'Entities to create with a key chosen by the client. The new IDs are returned in "created" under the same key.'],
					'update' => ['type' => 'object', 'additionalProperties' => ['type' => 'object'], 'description' => 'Patches by ID. A patch has the properties to change. A key can also be a path like "a/b" to change part of a property.'],
					'destroy' => ['type' => 'array', 'items' => $id, 'description' => 'IDs of the entities to destroy.']
				], [], 'At least one of create, update or destroy is required. At most ' . $capabilities->maxObjectsInSet . ' entities per call.'),
				'response' => self::object([
					'accountId' => $accountId,
					'oldState' => $state,
					'newState' => $state,
					'created' => ['type' => ['object', 'null'], 'additionalProperties' => ['type' => 'object'], 'description' => 'By client ID: the ID and the properties the server set or changed.'],
					'updated' => ['type' => ['object', 'null'], 'additionalProperties' => ['type' => ['object', 'null']], 'description' => 'By ID: the properties the server changed differently than requested, or null.'],
					'destroyed' => ['type' => ['array', 'null'], 'items' => $id],
					'notCreated' => $setErrors,
					'notUpdated' => $setErrors,
					'notDestroyed' => $setErrors
				])
			],
			'changes' => [
				'params' => self::object([
					'accountId' => $accountId,
					'sinceState' => ['type' => 'string', 'description' => 'The state from a previous get, query or changes response.'],
					'maxChanges' => ['type' => 'integer', 'minimum' => 1, 'maximum' => $capabilities->maxObjectsInGet, 'default' => $capabilities->maxObjectsInGet]
				], ['sinceState']),
				'response' => self::object([
					'accountId' => $accountId,
					'oldState' => $state,
					'newState' => $state,
					'hasMoreChanges' => ['type' => 'boolean', 'description' => 'Call again with newState to get the rest.'],
					'changed' => ['type' => 'array', 'items' => $id, 'description' => 'IDs of created and updated entities.'],
					'updated' => ['type' => 'array', 'items' => $id, 'description' => 'Same as "changed". Created entities are not returned separately.'],
					'removed' => ['type' => 'array', 'items' => $id, 'description' => 'IDs of destroyed entities.'],
					'totalChanges' => ['type' => 'integer', 'deprecated' => true, 'description' => 'Deprecated. Always 0.']
				], [], 'Returns a "cannotCalculateChanges" error when the state is too old. Then get all entities again.')
			],
			'export' => [
				'params' => self::object([
					'ids' => $ids,
					'properties' => ['type' => 'array', 'items' => ['type' => 'string']],
					'extension' => ['type' => 'string', 'description' => 'File type, for example "csv". The supported types differ per entity.'],
					'background' => ['type' => 'boolean', 'default' => false, 'description' => 'Export after the response. The file is not returned.']
				], ['ids', 'extension']),
				'response' => self::object([
					'success' => ['type' => 'boolean'],
					'blob' => ['type' => 'object', 'description' => 'The exported file. Download it with the "id" and the download URL.']
				])
			],
			'import' => [
				'params' => self::object([
					'blobId' => ['type' => 'string', 'description' => 'ID of the uploaded file.'],
					'values' => ['type' => 'object', 'description' => 'Property values to set on every imported entity.']
				], ['blobId'], 'Other options depend on the file type, like the column mapping from importCSVMapping.'),
				'response' => self::object([
					'pid' => ['type' => 'integer', 'description' => 'ID of the background process that imports the file.'],
					'output' => ['type' => 'string']
				])
			],
			'exportColumns' => [
				'params' => self::object([
					'extension' => ['type' => 'string', 'description' => 'File type, for example "csv".']
				], ['extension']),
				'response' => ['type' => 'object', 'description' => 'The exportable properties by name.']
			],
			'importCSVMapping' => [
				'params' => self::object([
					'blobId' => ['type' => 'string', 'description' => 'ID of the uploaded CSV file.'],
					'id' => ['type' => 'string', 'description' => 'ID of a saved mapping, when no blobId is given.']
				]),
				'response' => self::object([
					'id' => ['type' => 'string', 'description' => 'ID of the saved mapping that matches the CSV headers.'],
					'goHeaders' => ['type' => 'object', 'description' => 'The properties that can be imported.'],
					'csvHeaders' => ['type' => 'array', 'items' => ['type' => 'string']],
					'columnMapping' => ['type' => ['object', 'null']],
					'updateBy' => ['type' => ['string', 'null']],
					'checksum' => ['type' => ['string', 'null']]
				])
			],
			'merge' => [
				'params' => self::object([
					'ids' => ['type' => 'array', 'items' => $id, 'minItems' => 2, 'description' => 'The first entity is kept. The others are merged into it and destroyed.']
				], ['ids']),
				'response' => self::object([
					'id' => $id,
					'updated' => ['type' => 'object', 'additionalProperties' => $entity],
					'destroyed' => $ids,
					'oldState' => $state,
					'newState' => $state
				])
			]
		];
	}

	/**
	 * Read the parameters and response of a custom method from its doc block
	 *
	 * Tags:
	 *
	 * - "@apiParam <type> <name> <description>": a parameter. Start the description with "Required." for required parameters.
	 * - "@apiResponse <type> <description>": the response.
	 * - "@apiNoParams": the method has no parameters.
	 * - "@apiPermission <text>": the permission the method requires. See {@see permission()}.
	 * - "@apiReadOnly": the method only reads. See {@see isReadOnly()}.
	 *
	 * The types are described in {@see ApiType}. A tag continues on the next lines until the next tag.
	 *
	 * @example
	 * ```
	 * /**
	 *  * Decrypt the content of encrypted notes
	 *  *
	 *  * @apiParam string[] ids Required. IDs of the notes
	 *  * @apiParam string password Required. The encryption password
	 *  * @apiResponse {list: Note[]}
	 *  *\/
	 * ```
	 *
	 * @param string|false $docComment The doc block of the method
	 * @param string $entityName The entity of the method. See {@see ApiType::toJsonSchema()}
	 * @return array{params?: array, response?: array, permission?: string, readOnly?: bool}|null Null when the doc block has none of these
	 *   tags. "params" is only set when the parameters are documented.
	 * @throws \InvalidArgumentException When a tag is invalid
	 */
	public static function fromDocComment(string|false $docComment, string $entityName): ?array
	{
		if (!$docComment) {
			return null;
		}

		$properties = [];
		$required = [];
		$response = null;
		$found = false;

		foreach (self::docTags($docComment) as [$tag, $text]) {
			switch ($tag) {
				case 'apiParam':
					$found = true;
					$typeLength = ApiType::typeLength($text);
					$schema = ApiType::toJsonSchema(substr($text, 0, $typeLength), $entityName);
					$rest = ltrim(substr($text, $typeLength));
					if (!preg_match('/^\$?([A-Za-z_][A-Za-z0-9_]*)\s*(.*)$/s', $rest, $matches)) {
						throw new \InvalidArgumentException("@apiParam needs a type and a name: '" . $text . "'");
					}
					[, $name, $description] = $matches;
					if (str_starts_with($description, 'Required.')) {
						$required[] = $name;
						$description = ltrim(substr($description, strlen('Required.')));
					}
					if ($description !== '') {
						$schema['description'] = $description;
					}
					$properties[$name] = $schema;
					break;

				case 'apiResponse':
					$found = true;
					$typeLength = ApiType::typeLength($text);
					$response = ApiType::toJsonSchema(substr($text, 0, $typeLength), $entityName);
					$description = ltrim(substr($text, $typeLength));
					if ($description !== '') {
						$response['description'] = $description;
					}
					break;

				case 'apiNoParams':
					$found = true;
					break;

				case 'apiPermission':
					$permission = $text;
					break;

				case 'apiReadOnly':
					$readOnly = true;
					break;
			}
		}

		$result = [];
		if ($found) {
			$result['params'] = self::object($properties, $required);
			if (isset($response)) {
				$result['response'] = $response;
			}
		}
		if (isset($permission)) {
			$result['permission'] = $permission;
		}
		if (isset($readOnly)) {
			$result['readOnly'] = true;
		}

		return empty($result) ? null : $result;
	}

	/**
	 * Whether a custom method only reads, by the "@apiReadOnly" tag of its doc block
	 *
	 * Clients that may only read, like an AI assistant with read access, may call such a method. A method without the
	 * tag counts as one that changes data.
	 *
	 * @param string|false $docComment
	 */
	public static function isReadOnly(string|false $docComment): bool
	{
		if (!$docComment) {
			return false;
		}
		foreach (self::docTags($docComment) as [$tag]) {
			if ($tag === 'apiReadOnly') {
				return true;
			}
		}
		return false;
	}

	/**
	 * Text of "@apiPermission" that says access to the module is enough. Follow it with the reason, for example
	 * "Module access: only counts the tasks of the user".
	 */
	const PERMISSION_MODULE_ACCESS = 'Module access:';

	/**
	 * Text of "@apiPermission" that says the permission is checked outside the method, for example
	 * "Checked in Board::getBacklogCards(): read permission on the board".
	 */
	const PERMISSION_CHECKED_IN = 'Checked in ';

	/**
	 * Get the "@apiPermission" text of a doc block
	 *
	 * It documents the permission a method requires. Every method of a controller must check permissions, unless the
	 * text starts with {@see PERMISSION_MODULE_ACCESS} or {@see PERMISSION_CHECKED_IN}.
	 *
	 * @param string|false $docComment
	 * @return string|null Null when the doc block has no "@apiPermission"
	 */
	public static function permission(string|false $docComment): ?string
	{
		if (!$docComment) {
			return null;
		}
		foreach (self::docTags($docComment) as [$tag, $text]) {
			if ($tag === 'apiPermission') {
				return $text;
			}
		}
		return null;
	}

	/**
	 * Split a doc block in tags
	 *
	 * @return array<int, array{string, string}> Tag name without "@" and its text
	 */
	private static function docTags(string $docComment): array
	{
		$lines = preg_split('/\R/', $docComment);
		$tags = [];
		foreach ($lines as $line) {
			$line = preg_replace('~^\s*(/\*\*|\*/|\*)?\s?~', '', $line);
			$line = preg_replace('~\s*\*/\s*$~', '', $line);
			if (preg_match('/^@([A-Za-z]+)\s*(.*)$/', $line, $matches)) {
				$tags[] = [$matches[1], trim($matches[2])];
			} else if (!empty($tags) && trim($line) !== '') {
				$tags[count($tags) - 1][1] .= ' ' . trim($line);
			}
		}
		return $tags;
	}

	/**
	 * Get the schemas of the methods of an entity with the entity, filter and error definitions in "$defs"
	 *
	 * Methods without a schema have only a description. Custom methods get their schema from {@see fromDocComment()}.
	 *
	 * @param EntityType $entityType
	 * @param bool $withCustomFields See {@see PropertySchema::build()}
	 * @return array{methods: array<string, array{description: string, params?: array, response?: array}>, '$defs': array}
	 */
	public static function forEntity(EntityType $entityType, bool $withCustomFields = false): array
	{
		$cls = $entityType->getClassName();

		$properties = PropertySchema::build($cls, $withCustomFields);
		$filter = $cls::getFilters()->toJsonSchema();
		$entityName = substr($properties['$ref'], strlen('#/$defs/'));

		$standard = array_change_key_case(self::standard($entityName));
		$controllerClass = Router::entityControllerClass($entityType);

		$methods = [];
		foreach (Router::getEntityMethods($entityType) as $name => $description) {
			$schema = $standard[strtolower($name)] ?? self::fromDocComment((new \ReflectionMethod($controllerClass, $name))->getDocComment(), $entityName);
			$methods[$name] = ['description' => $description] + ($schema ?? []);
		}

		return [
			'methods' => $methods,
			'$defs' => array_merge($filter['$defs'], $properties['$defs'], ['SetError' => self::setError()])
		];
	}

	/**
	 * Schema of an error in the notCreated, notUpdated and notDestroyed responses of Foo/set
	 */
	public static function setError(): array
	{
		return self::object([
			'type' => ['type' => 'string', 'description' => 'For example "invalidProperties", "forbidden" or "notFound".'],
			'description' => ['type' => ['string', 'null']],
			'properties' => ['type' => ['array', 'null'], 'items' => ['type' => 'string'], 'description' => 'The invalid properties with "invalidProperties".'],
			'validationErrors' => ['type' => ['object', 'null'], 'description' => 'Error per property with "invalidProperties".']
		], ['type']);
	}

	private static function object(array $properties, array $required = [], string $description = ''): array
	{
		// An empty object instead of an empty array in JSON
		$schema = ['type' => 'object', 'properties' => empty($properties) ? new \stdClass() : $properties];
		if (!empty($required)) {
			$schema['required'] = $required;
		}
		if ($description !== '') {
			$schema['description'] = $description;
		}
		return $schema;
	}
}
