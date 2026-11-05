<?php

namespace go\core\model;

use go\core\orm\EntityType;
use go\core\util\DateTime;
use go\modules\community\test\model\B;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests the APCu state caching and debouncing of the SSE push dispatcher.
 *
 * APCu is disabled on the CLI by default. Run with:
 *
 * php -d apc.enable_cli=1 www/vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml --bootstrap=tests/bootstrap-none.php tests/go/core/model/PushDispatcherTest.php
 */
class PushDispatcherTest extends TestCase
{
	protected function setUp(): void
	{
		if (!function_exists('apcu_enabled') || !apcu_enabled()) {
			$this->markTestSkipped('APCu is not enabled. Use php -d apc.enable_cli=1');
		}
		EntityType::$sharedModSeqCache = true;
	}

	protected function tearDown(): void
	{
		EntityType::$sharedModSeqCache = false;
	}

	/**
	 * Simulates a new request/connection: the modseqs held in memory by the EntityType instance are dropped so the
	 * next call has to use the shared APCu entry or, when that is missing or outdated, the database.
	 *
	 * This wraps EntityType::clearCache() which does NOT touch APCu.
	 */
	private function forgetInstanceValues(EntityType $type): void
	{
		$type->clearCache();
	}

	private function saveB(): void
	{
		$b = new B();
		$b->propA = uniqid();
		$b->propB = uniqid();
		$b->createdAt = new DateTime();
		$this->assertTrue($b->save());
		EntityType::push();
	}

	public function testModSeqIsCachedUntilCounterChanges()
	{
		$type = EntityType::findByName('B');

		// new counter value, so there is no APCu entry for it yet
		PushDispatcher::incStateCounter('B');
		$this->forgetInstanceValues($type);

		$queries = EntityType::$modSeqQueries;
		$first = $type->getHighestModSeq();
		$this->assertEquals($queries + 1, EntityType::$modSeqQueries, 'No APCu entry yet, must query the DB');

		$this->forgetInstanceValues($type);
		$this->assertEquals($first, $type->getHighestModSeq());
		$this->assertEquals($queries + 1, EntityType::$modSeqQueries, 'APCu entry exists, must not query the DB');

		// A commit increments the counter so a new value must be read
		$this->saveB();
		$this->forgetInstanceValues($type);
		$second = $type->getHighestModSeq();
		$this->assertGreaterThan($first, $second);
		$this->assertEquals($queries + 2, EntityType::$modSeqQueries);
	}

	public function testUserModSeqIsInvalidatedByChange()
	{
		$type = EntityType::findByName('B');

		$this->forgetInstanceValues($type);
		$first = $type->getHighestUserModSeq();

		$queries = EntityType::$modSeqQueries;
		$this->forgetInstanceValues($type);
		$this->assertEquals($first, $type->getHighestUserModSeq());
		$this->assertEquals($queries, EntityType::$modSeqQueries, 'APCu entry exists, must not query the DB');

		$type->nextUserModSeq();
		// push() without changes must still invalidate after the commit of the user modseq
		EntityType::push();

		$this->forgetInstanceValues($type);
		$second = $type->getHighestUserModSeq();
		$this->assertEquals($first + 1, $second, 'Stale user modseq returned from cache');
		$this->assertEquals($queries + 1, EntityType::$modSeqQueries);
	}

	public function testUserModSeqNotInvalidatedForOtherEntityType()
	{
		$b = EntityType::findByName('B');
		$a = EntityType::findByName('A');

		$this->forgetInstanceValues($a);
		$a->getHighestUserModSeq();

		$b->nextUserModSeq();
		EntityType::push();

		$queries = EntityType::$modSeqQueries;
		$this->forgetInstanceValues($a);
		$a->getHighestUserModSeq();
		$this->assertEquals($queries, EntityType::$modSeqQueries, 'Unrelated entity type was invalidated');
	}

	private function dispatcher(float $delay): array
	{
		$d = new PushDispatcher([]);
		$ref = new ReflectionClass($d);

		$set = function (string $prop, mixed $value) use ($d, $ref) {
			$p = $ref->getProperty($prop);
			$p->setValue($d, $value);
		};
		$set('apcuEnabled', true);
		$set('recheckDelay', $delay);
		$set('counters', ['B' => 0]);

		$check = $ref->getMethod('shouldCheckDB');

		return [fn() => $check->invoke($d, 'B'), $set, $ref, $d];
	}

	public function testDebounce()
	{
		[$should, $set] = $this->dispatcher(0.3);

		// unknown counter value differs from 0, so first check passes immediately (leading edge)
		$this->assertTrue($should());
		// nothing changed
		$this->assertFalse($should());

		// change inside the debounce window is postponed
		PushDispatcher::incStateCounter('B');
		$this->assertFalse($should());
		PushDispatcher::incStateCounter('B');
		$this->assertFalse($should());

		// after the window the postponed change is picked up exactly once (trailing edge)
		usleep(350000);
		$this->assertTrue($should());
		$this->assertFalse($should());

		// and a change after a quiet period passes immediately
		usleep(350000);
		PushDispatcher::incStateCounter('B');
		$this->assertTrue($should());
	}

	public function testNoDebounceWithoutApcu()
	{
		[$should, $set] = $this->dispatcher(60);
		$set('apcuEnabled', false);

		$this->assertTrue($should());
		$this->assertTrue($should());
	}
}
