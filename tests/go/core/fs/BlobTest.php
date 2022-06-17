<?php
namespace go\core\fs;

use go\core\db\Query;
use go\core\model\User;
use go\core\util\DateTime;
use PHPUnit\Framework\TestCase;

class BlobTest extends TestCase
{
	public function testGarbageCollection() {

		$blobsQuery = Blob::findStale();
		$blobsStmt = $blobsQuery->execute();

		//count may vary depending on fresh install or upgrade in bootstrap.php
		$staleCountAtStart = $blobsStmt->rowCount();

		//create unused blob
		$blob = Blob::fromString("test blob text " . uniqid());
		$blob->type = 'text/plain';
		$blob->name = 'test.txt';
		$blob->staleAt = new DateTime("-1 min");
		$success = $blob->save();
		$this->assertEquals(true, $success);

		$this->assertEquals(true, $blob->getFile()->exists());

		$blobsStmt = $blobsQuery->execute();
//		echo $blobsQuery;
//		var_dump($blobsStmt->fetchAll());
		$this->assertEquals($staleCountAtStart + 1, $blobsStmt->rowCount());


		Blob::delete($blobsQuery);

		//expect file to be cleaned up
		$this->assertEquals(false, $blob->getFile()->exists());

		$this->assertEquals($staleCountAtStart + 1, Blob::$lastDeleteStmt->rowCount());

		$blobsStmt = $blobsQuery->execute();

		$this->assertEquals(0, $blobsStmt->rowCount());

		$blobCount = Blob::find()->selectSingleValue('count(*)')->single();


		$blackPixel = "R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=";

		$blob = Blob::fromString(base64_decode($blackPixel));
		$blob->type = 'image/gif';
		$blob->name = 'black-pixel.gif';
		$blob->staleAt = new DateTime("-1 min");
		$success = $blob->save();
		$this->assertEquals(true, $success);


//		echo "\n\n\n" . $blobsQuery . "\n\n\n";

		$blobsStmt = $blobsQuery->execute();

		// an unused blob again
		$this->assertEquals(1, $blobsStmt->rowCount());


		$admin = User::findById(1);
		$admin->avatarId = $blob->id;
		$success = $admin->save();

		$this->assertEquals(true, $success);

		$blobsStmt = $blobsQuery->execute();

		// all blobs used?
		$this->assertEquals(0, $blobsStmt->rowCount());

		//remove avatar again
		$admin->avatarId = null;
		$success = $admin->save();
		$this->assertEquals(true, $success);

		Blob::delete($blobsQuery);
		$this->assertEquals(1, Blob::$lastDeleteStmt->rowCount());


		// count should be the same
		$this->assertEquals($blobCount, Blob::find()->selectSingleValue('count(*)')->single());


	}



}