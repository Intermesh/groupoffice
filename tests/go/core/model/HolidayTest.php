<?php

namespace go\core\model;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HolidayTest extends TestCase
{
	public static function holidaySetProvider(): array
	{
		return [
			'locale' => ['en_gb', 'GB'],
			'upper case locale' => ['EN_GB', 'GB'],
			'locale with dash' => ['de-at', 'AT'],
			'legacy uk' => ['en_uk', 'GB'],
			'pt_br' => ['pt_br', 'BR'],
			'bn_bd' => ['bn_bd', 'BD'],
			'language same as country' => ['pl', 'PL'],
			'country code' => ['NL', 'NL'],
			'swedish is not El Salvador' => ['sv', 'SE'],
			'czech' => ['cs', 'CZ'],
			'norwegian' => ['nb', 'NO'],
			'japanese' => ['ja', 'JP'],
			'slovenian is not Sierra Leone' => ['sl', 'SI'],
			'estonian is not Ethiopia' => ['et', 'EE'],
			'english has no country' => ['en', null],
			'arabic is not Argentina' => ['ar', null],
			'unknown' => ['xx', null],
			'invalid' => ['../nl', null],
			'empty' => ['', null],
		];
	}

	#[DataProvider('holidaySetProvider')]
	public function testCountryForHolidaySet(string $set, ?string $expected): void
	{
		$this->assertSame($expected, Holiday::countryForHolidaySet($set));
	}

	/**
	 * Every holiday set offered to users and every language must resolve to a holiday file or to no holidays
	 */
	public function testAllSetsResolve(): void
	{
		$root = dirname(__DIR__, 4) . '/www/';
		$sets = array_merge(
			glob($root . 'language/holidays/*.php'),
			glob($root . 'go/core/language/*.php')
		);
		$this->assertNotEmpty($sets);

		foreach ($sets as $file) {
			$set = basename($file, '.php');
			$country = Holiday::countryForHolidaySet($set);
			if ($country !== null) {
				$this->assertFileExists($root . 'go/core/language/holidays/countries/' . strtolower($country) . '.json', $set);
			}
		}
	}

	public function testGenerate(): void
	{
		$holidays = iterator_to_array(Holiday::generate(Holiday::countryForHolidaySet('sv'), 'sv', new \DateTime('2026-06-01'), new \DateTime('2026-06-30')), false);
		$dates = array_map(fn(Holiday $h) => $h->start, $holidays);
		$this->assertContains('2026-06-06', $dates, 'Swedish national day');
	}
}
