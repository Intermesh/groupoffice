<?php

namespace go\core\jmap;

use go\core\orm\EntityType;
use go\modules\community\test\controller\B as BController;
use go\modules\community\test\model\B;
use PHPUnit\Framework\TestCase;

class MethodSchemaTest extends TestCase
{
	/**
	 * Only returned when debugging is enabled
	 */
	private const DEBUG_KEYS = ['query', 'totalQuery'];

	private function assertDocumented(string $method, array $response): void
	{
		$documented = array_keys(MethodSchema::standard('B')[$method]['response']['properties']);
		$returned = array_diff(array_keys($response), self::DEBUG_KEYS);

		$this->assertEquals([], array_values(array_diff($returned, $documented)), "Undocumented keys in the response of B/$method");
	}

	public function testResponsesOfStandardMethodsAreDocumented(): void
	{
		$controller = new BController();

		$set = $controller->set([
			'create' => [
				'c1' => ['propA' => 'schemaA', 'propB' => 'schemaB', 'propE' => 'schemaE']
			]
		])->getArray();
		$this->assertDocumented('set', $set);
		$id = $set['created']['c1']['id'];

		try {
			$this->assertDocumented('get', $controller->get(['ids' => [$id]])->getArray());
			$this->assertDocumented('query', $controller->query(['limit' => 1, 'calculateTotal' => true, 'calculateHasMore' => true])->getArray());
			$this->assertDocumented('changes', $controller->changes(['sinceState' => $set['oldState']])->getArray());
		} finally {
			$this->assertDocumented('set', $controller->set(['destroy' => [$id]])->getArray());
		}

		$this->assertNull(B::findById($id));
	}

	public function testForEntityHasStandardAndCustomMethods(): void
	{
		$schema = MethodSchema::forEntity(EntityType::findByName('Note'));

		$this->assertArrayHasKey('params', $schema['methods']['get']);
		$this->assertArrayHasKey('response', $schema['methods']['query']);

		// Note/decrypt is a custom method documented with @apiParam
		$this->assertEquals(['data', 'password'], $schema['methods']['decrypt']['params']['required']);
	}

	public function testForEntityReferencesExistingDefinitions(): void
	{
		foreach (EntityType::findAll() as $type) {
			if (!is_a($type->getClassName(), Entity::class, true)) {
				continue;
			}

			$schema = MethodSchema::forEntity($type);
			$json = json_encode($schema, JSON_UNESCAPED_SLASHES);

			preg_match_all('~"#/\$defs/([^"]+)"~', $json, $matches);
			foreach (array_unique($matches[1]) as $ref) {
				$this->assertArrayHasKey($ref, $schema['$defs'], $type->getName() . ' refers to missing ' . $ref);
			}
		}
	}

	public function testFromDocComment(): void
	{
		$doc = <<<'DOC'
		/**
		 * Do something
		 *
		 * @apiParam string[] ids Required. The IDs
		 * @apiParam ?int limit Maximum number of
		 *   results
		 * @apiResponse {list: Note[]} The notes
		 * @param array $params
		 */
		DOC;

		$schema = MethodSchema::fromDocComment($doc, 'Note');

		$this->assertEquals(['ids'], $schema['params']['required']);
		$this->assertEquals('The IDs', $schema['params']['properties']['ids']['description']);
		$this->assertEquals(['integer', 'null'], $schema['params']['properties']['limit']['type']);
		$this->assertEquals('Maximum number of results', $schema['params']['properties']['limit']['description']);
		$this->assertEquals(['$ref' => '#/$defs/Note'], $schema['response']['properties']['list']['items']);
		$this->assertEquals('The notes', $schema['response']['description']);
	}

	public function testPermission(): void
	{
		$doc = "/**\n * @apiParam string id Required. The ID\n * @apiPermission Write permission on the\n *   book.\n */";

		$this->assertEquals('Write permission on the book.', MethodSchema::permission($doc));
		$this->assertEquals('Write permission on the book.', MethodSchema::fromDocComment($doc, 'Note')['permission']);
		$this->assertNull(MethodSchema::permission("/**\n * @apiNoParams\n */"));
		$this->assertNull(MethodSchema::permission(false));
	}

	public function testPermissionWithoutParams(): void
	{
		// Only the permission is documented, so the parameters are not
		$schema = MethodSchema::fromDocComment("/**\n * @apiPermission Module access: counts only the own tasks.\n */", 'Note');

		$this->assertEquals(['permission' => 'Module access: counts only the own tasks.'], $schema);
	}

	public function testFromDocCommentWithoutTags(): void
	{
		$this->assertNull(MethodSchema::fromDocComment("/**\n * @param array \$params\n */", 'Note'));
		$this->assertNull(MethodSchema::fromDocComment(false, 'Note'));
	}

	public function testNoParams(): void
	{
		$schema = MethodSchema::fromDocComment("/**\n * @apiNoParams\n * @apiResponse int\n */", 'Note');

		$this->assertEquals('{}', json_encode($schema['params']['properties']));
		$this->assertEquals(['type' => 'integer'], $schema['response']);
	}

	/**
	 * Every method that can be called through JMAP must be documented. Use "@noapi" for methods that are not for API
	 * clients.
	 */
	public function testAllCustomMethodsAreDocumented(): void
	{
		$undocumented = [];
		foreach (EntityType::findAll() as $type) {
			if (!is_a($type->getClassName(), Entity::class, true)) {
				continue;
			}

			foreach (MethodSchema::forEntity($type)['methods'] as $name => $method) {
				if (!isset($method['params'])) {
					$undocumented[] = $type->getName() . '/' . $name;
				}
			}
		}

		$this->assertEquals([], $undocumented, "Document these methods with @apiParam or @apiNoParams, or hide them with @noapi");
	}
}
