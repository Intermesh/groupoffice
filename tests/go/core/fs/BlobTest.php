<?php
namespace go\core\fs;

use DateTimeZone;
use go\core\db\Query;
use go\core\model\User;
use go\core\util\DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BlobTest extends TestCase
{
	public function testGarbageCollection() {

		date_default_timezone_set("UTC");

		$blobsQuery = Blob::findStale();
		$blobsStmt = $blobsQuery->execute();

		//count may vary depending on fresh install or upgrade in bootstrap.php
		$staleCountAtStart = $blobsStmt->rowCount();

		//create unused blob

		$blob = Blob::fromString("test blob text " . uniqid());
		$blob->type = 'text/plain';
		$blob->name = 'test.txt';
		$blob->staleAt = new DateTime("-10 min", new DateTimeZone('UTC'));
//		go()->getDbConnection()->debug = true;
		$success = $blob->save();


//		echo $blob->staleAt->format('Y-m-d H:i:s')."\n\n";
//
//		echo go()->getDbConnection()->query("SELECT NOW()")->fetchColumn() . "\n\n";


		$this->assertEquals(true, $success);

		$this->assertEquals(true, $blob->getFile()->exists());

		$blobsStmt = $blobsQuery->execute();

//		echo $blobsQuery ."\n\n";
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
		$blob->staleAt = new DateTime("-1 min", new DateTimeZone('UTC'));
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

	public static function invalidIdProvider(): array
	{
		return [
			'path traversal' => ['../../../../../../etc/passwd'],
			'traversal with hash length' => ['../../../../../../../../../../etc/passwd'],
			'absolute path' => ['/etc/passwd'],
			'too short' => ['abc123'],
			'too long' => [str_repeat('a', 41)],
			'uppercase' => [strtoupper(sha1('blob'))],
			'not hex' => [str_repeat('g', 40)],
			'empty' => [''],
			'trailing newline' => [sha1('blob') . "\n"],
		];
	}

	/**
	 * Blob IDs may come from the client, so buildPath() must not return a path outside the blob folder
	 */
	#[DataProvider('invalidIdProvider')]
	public function testBuildPathRefusesInvalidId(string $id)
	{
		$this->assertFalse(Blob::isValidId($id));

		$this->expectException(InvalidArgumentException::class);
		Blob::buildPath($id);
	}

	public function testBuildPathOfValidId()
	{
		$id = sha1('blob');
		$this->assertTrue(Blob::isValidId($id));

		$path = Blob::buildPath($id);
		$this->assertStringEndsWith(DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . substr($id, 0, 2) . DIRECTORY_SEPARATOR . substr($id, 2, 2) . DIRECTORY_SEPARATOR . $id, $path);
	}

	public function testSavedBlobHasValidId()
	{
		$blob = Blob::fromString("valid id test " . uniqid());
		$blob->type = 'text/plain';
		$blob->name = 'test.txt';
		$blob->staleAt = new DateTime("-10 min", new DateTimeZone('UTC'));
		$this->assertTrue($blob->save());

		$this->assertTrue(Blob::isValidId($blob->id));
		$this->assertFileExists($blob->path());

		Blob::delete(['id' => $blob->id]);
	}

	/**
	 * Uploading the same content as an existing blob, like someone else's attachment, must not rename that blob
	 */
	public function testUploadDoesNotRenameExistingBlob()
	{
		$content = "upload test " . uniqid();
		$existing = Blob::fromString($content);
		$existing->type = 'text/plain';
		$existing->name = 'original.txt';
		$this->assertTrue($existing->save());

		try {
			$tmpFile = File::tempFile('renamed.txt');
			$tmpFile->putContents($content);

			$blob = Blob::fromUpload($tmpFile, 'renamed.txt', new DateTime('2020-01-01'));
			$this->assertSame($existing->id, $blob->id);
			// The client gets the name it uploaded
			$this->assertSame('renamed.txt', $blob->name);
			$this->assertFalse($tmpFile->exists());

			$stored = go()->getDbConnection()->selectSingleValue('name')->from('core_blob')->where('id', '=', $existing->id)->single();
			$this->assertSame('original.txt', $stored);
		} finally {
			Blob::delete(['id' => $existing->id]);
		}
	}

	public function testUploadOfNewContentIsSaved()
	{
		$tmpFile = File::tempFile('new.txt');
		$tmpFile->putContents("new upload " . uniqid());

		$blob = Blob::fromUpload($tmpFile, 'new.txt', new DateTime());
		try {
			$this->assertFalse($blob->isNew());
			$this->assertFileExists($blob->path());
			$stored = go()->getDbConnection()->selectSingleValue('name')->from('core_blob')->where('id', '=', $blob->id)->single();
			$this->assertSame('new.txt', $stored);
		} finally {
			Blob::delete(['id' => $blob->id]);
		}
	}
	/**
	 * Like File::getBlob() for a file in an item's folder: the blob of the same content keeps its stored name
	 */
	public function testFromFileDoesNotRenameExistingBlob()
	{
		$content = "from file test " . uniqid();
		$existing = Blob::fromString($content);
		$existing->type = 'text/plain';
		$existing->name = 'original.txt';
		$this->assertTrue($existing->save());

		$file = File::tempFile('other.txt');
		try {
			$file->putContents($content);
			$blob = Blob::fromFile($file);
			$this->assertTrue($blob->save());
			// The caller still sees the name of its own file
			$this->assertSame($file->getName(), $blob->name);
			$this->assertSame('original.txt', $this->storedName($existing->id));

			$again = Blob::fromString($content);
			$again->name = 'renamed.txt';
			$this->assertTrue($again->save());
			$this->assertSame('original.txt', $this->storedName($existing->id));

			// Renaming a blob that was loaded on purpose still works
			$loaded = Blob::findById($existing->id);
			$loaded->name = 'renamed.txt';
			$this->assertTrue($loaded->save());
			$this->assertSame('renamed.txt', $this->storedName($existing->id));
		} finally {
			$file->delete();
			Blob::delete(['id' => $existing->id]);
		}
	}

	private function storedName(string $id): string
	{
		return go()->getDbConnection()->selectSingleValue('name')->from('core_blob')->where('id', '=', $id)->single();
	}
}
