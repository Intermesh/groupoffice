<?php
namespace go\modules\community\test\model {

	/**
	 * An entity type that is only registered by EntityTypeCacheTest
	 */
	class EntityTypeCacheProbe extends \go\core\orm\Entity
	{
		public static function getClientName(): string
		{
			return 'EntityTypeCacheProbe';
		}
	}
}

namespace go\core\orm {

	use go\modules\community\notes\model\Note;
	use go\modules\community\test\model\EntityTypeCacheProbe;
	use PHPUnit\Framework\TestCase;

	/**
	 * Registering a new entity type must keep the entity types that are already in use. When the cache was rebuilt, the
	 * entities kept their old entity type objects with another mod sequence than the cache, so a JMAP set could return a
	 * state that didn't change.
	 */
	class EntityTypeCacheTest extends TestCase
	{
		protected function tearDown(): void
		{
			go()->getDbConnection()->delete('core_entity', ['clientName' => 'EntityTypeCacheProbe'])->execute();
			go()->getCache()->delete('entity-types');
			Entity::clearCache();
		}

		public function testRegisteringAnEntityTypeKeepsTheCachedTypes(): void
		{
			$noteType = Note::entityType();

			$probeType = EntityType::findByClassName(EntityTypeCacheProbe::class);
			$this->assertSame('EntityTypeCacheProbe', $probeType->getName());

			$this->assertSame($noteType, EntityType::findByClassName(Note::class));
			$this->assertSame($noteType, EntityType::findById($noteType->getId()));
			$this->assertSame($probeType->getId(), EntityType::findByName('EntityTypeCacheProbe')->getId());
		}
	}
}
