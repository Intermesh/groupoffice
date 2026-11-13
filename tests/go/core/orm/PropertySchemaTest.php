<?php

namespace go\core\orm;

use go\modules\community\notes\model\Note;
use go\modules\community\test\model\A;
use go\modules\community\test\model\AHasMany;
use go\modules\community\test\model\AHasOne;
use go\modules\community\test\model\AMap;
use PHPUnit\Framework\TestCase;

class PropertySchemaTest extends TestCase
{
	private function properties(array $schema, string $def): array
	{
		return (array) $schema['$defs'][$def]['properties'];
	}

	/**
	 * Properties documented with "@var <type> <description>" get that description, so a client that reads the schema
	 * knows what they mean
	 */
	public function testVarDescriptionIsUsed(): void
	{
		$schema = PropertySchema::build(\go\modules\community\calendar\model\CalendarEvent::class);
		$props = $this->properties($schema, 'Participant');

		$this->assertStringContainsString('What kind of entity', $props['kind']['description']);
	}

	public function testRootReferencesEntityDefinition(): void
	{
		$schema = PropertySchema::build(A::class);

		$this->assertEquals('#/$defs/A', $schema['$ref']);
		$this->assertEquals('object', $schema['$defs']['A']['type']);
	}

	public function testRelations(): void
	{
		$schema = PropertySchema::build(A::class);
		$props = $this->properties($schema, 'A');

		$this->assertEquals('array', $props['hasMany']['type']);
		$this->assertEquals(['$ref' => '#/$defs/AHasMany'], $props['hasMany']['items']);
		// From "@var AHasMany[] The has many property models"
		$this->assertEquals('The has many property models', $props['hasMany']['description']);
		$this->assertEquals(['$ref' => '#/$defs/AMap'], $props['map']['additionalProperties']);
		$this->assertContains(['$ref' => '#/$defs/AHasOne'], $props['hasOne']['anyOf']);

		$this->assertArrayHasKey('AHasMany', $schema['$defs']);
		$this->assertArrayHasKey('AMap', $schema['$defs']);
		$this->assertArrayHasKey('AHasOne', $schema['$defs']);
	}

	public function testColumnsAndReadOnly(): void
	{
		$props = $this->properties(PropertySchema::build(A::class), 'A');

		$this->assertEquals('date-time', $props['createdAt']['format']);
		$this->assertTrue($props['createdAt']['readOnly']);
		$this->assertTrue($props['modifiedAt']['readOnly']);
		$this->assertTrue($props['id']['readOnly']);

		$this->assertEquals('string', $props['propA']['type']);
		$this->assertEquals(190, $props['propA']['maxLength']);
		$this->assertArrayNotHasKey('readOnly', $props['propA']);
	}

	public function testDeclaredPhpTypeWinsOverColumnType(): void
	{
		// The column is an integer, but the API returns the string property
		$props = $this->properties(PropertySchema::build(Note::class), 'Note');

		$this->assertEquals('string', $props['noteBookId']['type']);
	}

	public function testCustomFieldsAreOnlyListedWhenAsked(): void
	{
		$without = $this->properties(PropertySchema::build(Note::class), 'Note');
		$with = $this->properties(PropertySchema::build(Note::class, true), 'Note');

		$this->assertEquals('object', $without['customFields']['type']);
		$this->assertArrayNotHasKey('properties', $without['customFields']);
		$this->assertArrayHasKey('properties', $with['customFields']);
	}

	public function testPropertiesMatchApiOutput(): void
	{
		$a = new A();
		$a->propA = "schema test";
		$this->assertTrue($a->save());

		try {
			$output = A::findById($a->id)->toArray();
			$props = $this->properties(PropertySchema::build(A::class), 'A');

			$this->assertEqualsCanonicalizing(array_keys($output), array_keys($props));
		} finally {
			A::delete(['id' => $a->id]);
		}
	}

	public function testProtectedPropertyWithGetterIsReadOnly(): void
	{
		// AclOwnerEntity::$permissionLevel is protected with a public getter
		$props = $this->properties(PropertySchema::build(\go\modules\community\notes\model\NoteBook::class), 'NoteBook');

		$this->assertTrue($props['permissionLevel']['readOnly']);
		$this->assertEquals('integer', $props['permissionLevel']['type']);
	}

	/**
	 * A setter without a getter is only a property when it's tagged "@apiWriteOnly". Most are internal, like setValues().
	 */
	public function testOnlyTaggedSettersAreWriteOnlyProperties(): void
	{
		$props = $this->properties(PropertySchema::build(\go\modules\community\calendar\model\CalendarEvent::class), 'CalendarEvent');
		$this->assertTrue($props['onlineMeeting']['writeOnly']);
		$this->assertArrayNotHasKey('readOnly', $props['onlineMeeting']);

		$this->assertArrayNotHasKey('customFieldsJSON', $props);

		$userProps = $this->properties(PropertySchema::build(\go\core\model\User::class), 'User');
		$this->assertArrayNotHasKey('values', $userProps);
	}

	public function testAllEntitiesHaveValidSchemas(): void
	{
		foreach (EntityType::findAll() as $type) {
			// Another test may have uninstalled or removed the module, the entity types are still cached
			if (!\go\core\model\Module::findById($type->getModuleId())?->enabled) {
				continue;
			}
			$cls = $type->getClassName();
			if (!is_a($cls, \go\core\jmap\Entity::class, true)) {
				continue;
			}

			$schema = PropertySchema::build($cls);
			$json = json_encode($schema, JSON_UNESCAPED_SLASHES);
			$this->assertNotFalse($json, $type->getName());

			preg_match_all('~"#/\$defs/([^"]+)"~', $json, $matches);
			$this->assertNotEmpty($matches[1], $type->getName());
			foreach ($matches[1] as $ref) {
				$this->assertArrayHasKey($ref, $schema['$defs'], $type->getName() . ' refers to missing ' . $ref);
			}
		}
	}
}
