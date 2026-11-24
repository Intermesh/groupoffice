<?php

namespace go\core\util;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Changelog reads the releases and their changes from CHANGELOG.md for the "What's new" window
 */
class ChangelogTest extends TestCase
{
	private const TEXT = <<<'MD'
- Core: Unreleased change

23-11-2026: 26.0.62
- Core: First change
- Calendar: A change that continues
  on the next line.

<<<<<<< HEAD
>>>>>>> f1810d362 (Ticket)
 16-11-2026: 26.0.61
- Tasks: Change in 61

16-11-2026: 26.0.60
- Notes: Change in 60
MD;

	public function testReleasesNewestFirst()
	{
		$releases = (new Changelog(self::TEXT))->getReleases();

		$this->assertSame(['26.0.62', '26.0.61', '26.0.60'], array_column($releases, 'version'));
		$this->assertSame('23-11-2026', $releases[0]['date']);
	}

	public function testLeavesOutUnreleasedChanges()
	{
		$changes = array_merge(...array_column((new Changelog(self::TEXT))->getReleases(), 'changes'));

		$this->assertNotContains('Core: Unreleased change', $changes);
	}

	public function testJoinsContinuedLines()
	{
		$releases = (new Changelog(self::TEXT))->getReleases();

		$this->assertSame(['Core: First change', 'Calendar: A change that continues on the next line.'], $releases[0]['changes']);
	}

	public function testSkipsMergeMarkersAndReadsIndentedHeader()
	{
		$releases = (new Changelog(self::TEXT))->getReleases();

		$this->assertSame(['Tasks: Change in 61'], $releases[1]['changes']);
	}

	public static function betweenProvider(): array
	{
		return [
			'one release' => ['26.0.61', '26.0.62', ['26.0.62']],
			'two releases' => ['26.0.60', '26.0.62', ['26.0.62', '26.0.61']],
			'not newer than the current version' => ['26.0.60', '26.0.61', ['26.0.61']],
			'nothing new' => ['26.0.62', '26.0.62', []],
			'older major version' => ['6.8.100', '26.0.62', ['26.0.62', '26.0.61', '26.0.60']],
		];
	}

	#[DataProvider('betweenProvider')]
	public function testBetween(string $from, string $to, array $versions)
	{
		$releases = (new Changelog(self::TEXT))->between($from, $to);

		$this->assertSame($versions, array_column($releases, 'version'));
	}

	public function testLoadsChangelogOfTheCheckout()
	{
		$changelog = Changelog::load();

		$this->assertNotNull($changelog);
		$this->assertNotEmpty($changelog->getReleases());
	}
}
