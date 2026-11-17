<?php

namespace go\core\event;

use PHPUnit\Framework\TestCase;

class ListenersTest extends TestCase
{
	/**
	 * The cache rebuild at the end of an upgrade failed on installations without a SourceGuardian loader, because the
	 * e-mail module loaded an encoded class of the MCP module in defineListeners().
	 *
	 * Runs in another process, because the bootstrap of this one already loaded every class that the listeners use.
	 */
	public function testRebuildCacheDoesNotLoadClassesOfUnavailableBusinessModules(): void
	{
		$script = __DIR__ . '/fixtures/rebuildCacheWithoutBusiness.php';
		$output = (string)shell_exec(escapeshellarg(PHP_BINARY) . ' -d xdebug.mode=off ' . escapeshellarg($script) . ' 2>&1');

		$this->assertSame('OK', $output);
	}
}
