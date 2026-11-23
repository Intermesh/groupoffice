<?php
namespace GO\Base\Model;

use PHPUnit\Framework\TestCase;

/**
 * The export columns are sent by the client, so they must never be evaluated as PHP code.
 */
class AbstractExportTest extends TestCase
{
	private const QUERY_KEY = 'abstract-export-test';

	protected function setUp(): void
	{
		\GO::session()->values[self::QUERY_KEY] = ['model' => User::class, 'name' => 'test'];
		unset($GLOBALS['abstractExportTestInjected']);
	}

	protected function tearDown(): void
	{
		unset(\GO::session()->values[self::QUERY_KEY], $GLOBALS['abstractExportTestInjected']);
	}

	private function createExport(array $columns): AbstractExport
	{
		return new class($columns) extends AbstractExport {
			public $queryKey = 'abstract-export-test';

			public function getSupportedViews()
			{
				return [self::VIEW_CSV];
			}
		};
	}

	public function testColumnIsNotEvaluated()
	{
		$payload = 'username",$GLOBALS["abstractExportTestInjected"]=1,"';

		$colModel = $this->createExport(['username', $payload])->getColumnModel();
		$user = new User();
		$user->username = '<b>test</b>';

		$record = $colModel->formatModel($user);

		$this->assertArrayNotHasKey('abstractExportTestInjected', $GLOBALS);
		// the html export must still encode the values
		$this->assertEquals('&lt;b&gt;test&lt;/b&gt;', $record['username']);
	}

	public function testNotExportableColumnIsRemoved()
	{
		$colModel = $this->createExport(['username', 'password'])->getColumnModel();

		$this->assertNotFalse($colModel->getColumn('username'));
		$this->assertFalse($colModel->getColumn('password'));
	}
}
