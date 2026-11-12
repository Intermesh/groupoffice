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

	public function testForEntityHasSchemasForStandardMethodsOnly(): void
	{
		$schema = MethodSchema::forEntity(EntityType::findByName('Note'));

		$this->assertArrayHasKey('params', $schema['methods']['get']);
		$this->assertArrayHasKey('response', $schema['methods']['query']);

		// Note/decrypt is a custom method without declared parameters
		$this->assertArrayHasKey('decrypt', $schema['methods']);
		$this->assertArrayNotHasKey('params', $schema['methods']['decrypt']);
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
}
