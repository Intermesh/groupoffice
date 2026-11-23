<?php

namespace go\core\db;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use go\core\model\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * A "datetime" column is in UTC, so a date in another time zone must be converted when saved. A "localdatetime" column
 * keeps the time as given.
 */
class ColumnTest extends TestCase
{
	private function column(string $dbType): Column
	{
		$column = clone Table::getInstance('core_user')->getColumn('lastLogin');
		$column->dbType = $dbType;
		return $column;
	}

	public static function datetimeProvider(): array
	{
		return [
			'winter' => [new DateTime('2026-01-15 10:00:00', new DateTimeZone('Europe/Amsterdam')), '2026-01-15 09:00:00'],
			'summer' => [new DateTime('2026-07-15 10:00:00', new DateTimeZone('Europe/Amsterdam')), '2026-07-15 08:00:00'],
			'immutable' => [new DateTimeImmutable('2026-01-15 10:00:00', new DateTimeZone('America/New_York')), '2026-01-15 15:00:00'],
			'go DateTime' => [new \go\core\util\DateTime('2026-01-15 10:00:00', new DateTimeZone('Asia/Tokyo')), '2026-01-15 01:00:00'],
			'already UTC' => [new DateTime('2026-01-15 10:00:00', new DateTimeZone('UTC')), '2026-01-15 10:00:00'],
			'string' => ['2026-01-15 10:00:00', '2026-01-15 10:00:00'],
		];
	}

	#[DataProvider('datetimeProvider')]
	public function testDatetimeIsSavedInUtc($value, string $expected): void
	{
		$timezone = $value instanceof \DateTimeInterface ? $value->getTimezone()->getName() : null;

		$this->assertSame($expected, $this->column('datetime')->castToDb($value));

		if (isset($timezone)) {
			$this->assertSame($timezone, $value->getTimezone()->getName(), 'The object of the caller must not change');
		}
	}

	public function testLocalDatetimeKeepsTime(): void
	{
		$value = new DateTime('2026-01-15 10:00:00', new DateTimeZone('Europe/Amsterdam'));

		$this->assertSame('2026-01-15 10:00:00', $this->column('localdatetime')->castToDb($value));
	}

	/**
	 * Saving a date of another time zone and reading it back gives the same moment
	 */
	public function testEntitySavesSameMoment(): void
	{
		$loginAt = new \go\core\util\DateTime('2026-01-15 10:00:00', new DateTimeZone('Europe/Amsterdam'));

		$user = User::findById(1, ['id', 'lastLogin']);
		$original = $user->lastLogin;

		try {
			$user->lastLogin = $loginAt;
			$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));

			$stored = go()->getDbConnection()->selectSingleValue('lastLogin')->from('core_user')->where('id', '=', 1)->single();
			$this->assertSame('2026-01-15 09:00:00', $stored);

			$reloaded = User::findById(1, ['id', 'lastLogin'], true);
			$this->assertSame($loginAt->getTimestamp(), $reloaded->lastLogin->getTimestamp());
		} finally {
			go()->getDbConnection()->update('core_user', ['lastLogin' => $original], ['id' => 1])->execute();
		}
	}
}
