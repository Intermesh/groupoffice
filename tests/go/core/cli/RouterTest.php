<?php
namespace go\core\cli;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
	public static function arguments(): array
	{
		return [
			'nothing' => [[], []],
			'route only' => [['core/System/upgrade'], ['core/System/upgrade']],
			'long option with value' => [
				['community/dev/Language/export', '--language=nl'],
				['community/dev/Language/export', 'language' => 'nl']
			],
			'long option without value is true' => [
				['core/System/checkBlobs', '--delete'],
				['core/System/checkBlobs', 'delete' => true]
			],
			'empty value' => [['--name='], ['name' => '']],
			'value with equals sign' => [['--filter=a=b'], ['filter' => 'a=b']],
			'last value wins' => [['--path=x', '--path=y'], ['path' => 'y']],
			'flag does not overwrite value' => [['--debug=output', '--debug'], ['debug' => 'output']],
			'short option with value' => [['-c=/etc/groupoffice/config.php'], ['c' => '/etc/groupoffice/config.php']],
			'short flags combined' => [['-qv'], ['q' => true, 'v' => true]],
			'options before route' => [
				['-c=/etc/go.php', 'core/cron/run', '-q'],
				['c' => '/etc/go.php', 'core/cron/run', 'q' => true]
			],
			'mixed' => [
				['core/System/runCron', '--module=calendar', '--package=community', '--name=ScanEmailForInvites', '--debug'],
				['core/System/runCron', 'module' => 'calendar', 'package' => 'community', 'name' => 'ScanEmailForInvites', 'debug' => true]
			],
			'long option with space' => [
				['community/dev/Language/export', '--language', 'nl', '--missingOnly'],
				['community/dev/Language/export', 'language' => 'nl', 'missingOnly' => true]
			],
			'short option with space' => [
				['-c', '/etc/go.php', 'core/cron/run', '-q'],
				['c' => '/etc/go.php', 'core/cron/run', 'q' => true]
			],
			'option followed by option is a flag' => [['--debug', '--delete'], ['debug' => true, 'delete' => true]],
			'negative number needs equals sign' => [['--offset', '-1'], ['offset' => true, '1' => true]],
			'negative number with equals sign' => [['--offset=-1'], ['offset' => '-1']],
			'empty string after space' => [['--name', ''], ['name' => '']],
			'combined short flags take no value' => [['-qv', 'core/cron/run'], ['q' => true, 'v' => true, 'core/cron/run']],
			'space and equals mixed' => [
				['--path', 'x', '--path=y', '--name', 'z'],
				['path' => 'y', 'name' => 'z']
			],
		];
	}

	#[DataProvider('arguments')]
	public function testParse(array $argv, array $expected)
	{
		$this->assertSame($expected, Router::parse($argv));
	}
}
