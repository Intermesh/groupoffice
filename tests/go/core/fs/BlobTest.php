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

		$contact= $admin->getProfile();

		//remove avatar again
		$admin->avatarId = $contact->photoBlobId  = null;

		$success = $admin->save() && $contact->save();
		$this->assertEquals(true, $success);

		Blob::delete($blobsQuery);
		$this->assertEquals(1, Blob::$lastDeleteStmt->rowCount());


		// count should be the same
		$this->assertEquals($blobCount, Blob::find()->selectSingleValue('count(*)')->single());


	}



	public function testParse() {
		$id1 = sha1("a");
		$id2 = sha1("b");
		$id3 = sha1("c");
		$id4 = sha1("d");

		$html = '<p><img src="https://example.com/api/download.php?blob=' . $id1 . '" alt="a"></p>' .
			'<img data-blob-id="' . $id2 . '" src="blob:https://example.com/123">' .
			'<a href="download.php?foo=bar&amp;blob=' . $id3 . '">c</a>' .
			'<img src="https://example.com/api/download.php?blob=' . $id1 . '">';

		$this->assertEquals([$id1, $id2, $id3], Blob::parse($html));

		$markdown = "![a.png](https://example.com/api/download.php?blob=$id1)\n" .
			"[d.pdf](../api/download.php?foo=bar&blob=$id4)\n" .
			"Again ![a](https://example.com/api/download.php?blob=$id1)\n" .
			"Not a blob: noblob=$id2 and blob=tooshort and blob={$id3}0";

		$this->assertEquals([$id1, $id4], Blob::parse($markdown));
		$this->assertEquals([], Blob::parse(null));
		$this->assertEquals([], Blob::parse("No images"));

		// deprecated alias
		$this->assertEquals([$id1, $id2, $id3], Blob::parseFromHtml($html));
	}
}