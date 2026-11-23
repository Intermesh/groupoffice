<?php
namespace GO\Base\Fs;

use go\core\http\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * \GO\Base\Util\Http::outputDownloadHeaders() sends the content type of mimeType() when a file is shown inline, for
 * example for e-mail attachments and file versions. Files that can run scripts must not be shown inline.
 */
class FileTest extends TestCase
{
	public static function scriptExtensionProvider(): array
	{
		return [['html'], ['htm'], ['HTML'], ['xhtml'], ['xht'], ['svg'], ['xml'], ['xsl']];
	}

	#[DataProvider('scriptExtensionProvider')]
	public function testMimeTypeOfScriptFileIsNotShownInline(string $extension)
	{
		$file = new File('/tmp/file.' . $extension);
		$this->assertFalse(Response::isSafeInlineContentType($file->mimeType()), $extension . ': ' . $file->mimeType());
	}

	public function testMimeTypeOfPdfIsShownInline()
	{
		$file = new File('/tmp/file.pdf');
		$this->assertTrue(Response::isSafeInlineContentType($file->mimeType()));
	}
}
