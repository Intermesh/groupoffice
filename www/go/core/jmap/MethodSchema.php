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
 * Other methods of a controller have no schema because their parameters are not declared.
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
	 * Get the schemas of the methods of an entity with the entity, filter and error definitions in "$defs"
	 *
	 * Methods without a schema have only a description.
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

		$methods = [];
		foreach (Router::getEntityMethods($entityType) as $name => $description) {
			$methods[$name] = ['description' => $description] + ($standard[strtolower($name)] ?? []);
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
		$schema = ['type' => 'object', 'properties' => $properties];
		if (!empty($required)) {
			$schema['required'] = $required;
		}
		if ($description !== '') {
			$schema['description'] = $description;
		}
		return $schema;
	}
}
