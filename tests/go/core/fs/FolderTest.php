<?php
namespace go\core\fs;

use PHPUnit\Framework\TestCase;
use go\core\util\DateTime;

class FolderTest extends TestCase
{

	public function testFind() {

		$tmp = go()->getTmpFolder()->getFolder('find');
		$tmp->delete();
		$tmp->create();

		$oneDayAgo = new DateTime("-1 day");

		$oldFile = $tmp->getFile("folder/old.txt")
			->touch(true, $oneDayAgo->format("U") - 1);
		$newEnough = $tmp->getFile("justnewenough.txt")
			->touch(true, $oneDayAgo->format("U") + 1);
		$emptyFolder = $tmp->getFolder("empty")->create();

		$garbage = $tmp->find(
			[
				'older' => $oneDayAgo,
				'empty' => true
			]
		);

		$this->assertEquals(2, count($garbage));

		$this->assertEquals($emptyFolder->getPath(), $garbage[0]->getPath());
		$this->assertEquals($oldFile->getPath(), $garbage[1]->getPath());

		$all = $tmp->find();

		$this->assertEquals(4, count($all));

		foreach($garbage as $i) {
			$success = $i->delete();
			$this->assertEquals(true, $success);
		}

		$all = $tmp->find();

		$this->assertEquals(2, count($all));


	}

}
