<?php
namespace go\core\fs;

use Exception;
use PHPUnit\Framework\TestCase;
use go\core\util\DateTime;

class FolderTest extends TestCase
{

	public function testFind() {

		$tmp = go()->getTmpFolder()->getFolder('find');
		$tmp->delete();
		$tmp->create();

		$oneDayAgo = new DateTime("-1 day");

		$tmp->getFile("folder/old.txt")
			->touch(true, $oneDayAgo->format("U") - 1);
		$newEnough = $tmp->getFile("justnewenough.txt")
			->touch(true, $oneDayAgo->format("U") + 1);
		$tmp->getFolder("empty")->create();

		$garbage = $tmp->find(
			[
				'older' => $oneDayAgo,
				'empty' => true
			]
		);

		sort($garbage);

		$this->assertEquals(2, count($garbage));

		$this->assertNotEquals($newEnough->getPath(), $garbage[0]->getPath());
		$this->assertNotEquals($newEnough->getPath(), $garbage[1]->getPath());

		$all = $tmp->find();

		$this->assertEquals(4, count($all));

		foreach($garbage as $i) {
			$success = $i->delete();
			$this->assertEquals(true, $success);
		}

		$all = $tmp->find();

		$this->assertEquals(2, count($all));


	}


	/**
	 * @return void
	 * @throws Exception
	 */
	public function testRootFolderProtection() {
		$this->expectException(Exception::class);

		$rootFolder = go()->getDataFolder()->getFolder('tmp');
		$rootFolder->delete();
	}

	/**
	 * @return void
	 * @throws Exception
	 */
	public function testCreateAndDelete() {

		$folder = go()->getDataFolder()->getFolder('tmp/test');

		//throws exception on failure
		$folder->create();

		$deleted = $folder->delete();
		$this->assertEquals(true, $deleted);

		$exists = $folder->exists();
		$this->assertEquals(false, $exists);
	}

}
