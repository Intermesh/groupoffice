<?php
namespace GO\Base\Util;

use PHPUnit\Framework\TestCase;

/**
 * The header of a PDF may not render HTML from the request, because TCPDF fetches the images in it from any address,
 * also internal ones.
 */
class PdfTest extends TestCase
{
	private static $server;
	private static int $port;
	private static string $docRoot;

	public static function setUpBeforeClass(): void
	{
		// Records every request, so the test can see if the internal address was requested
		self::$docRoot = sys_get_temp_dir() . '/go-pdf-test-' . uniqid();
		mkdir(self::$docRoot);
		file_put_contents(self::$docRoot . '/router.php', <<<'PHP'
<?php
file_put_contents(__DIR__ . '/requests.log', $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
echo 'internal';
PHP
		);

		$socket = stream_socket_server('tcp://127.0.0.1:0');
		self::$port = (int) substr(strrchr(stream_socket_get_name($socket, false), ':'), 1);
		fclose($socket);

		self::$server = proc_open(
			[PHP_BINARY, '-S', '127.0.0.1:' . self::$port, self::$docRoot . '/router.php'],
			[['pipe', 'r'], ['file', '/dev/null', 'w'], ['file', '/dev/null', 'w']],
			$pipes
		);

		for ($i = 0; $i < 50; $i++) {
			$c = @fsockopen('127.0.0.1', self::$port);
			if ($c) {
				fclose($c);
				return;
			}
			usleep(100000);
		}
		self::fail('Test HTTP server did not start');
	}

	public static function tearDownAfterClass(): void
	{
		proc_terminate(self::$server);
		proc_close(self::$server);
		@unlink(self::$docRoot . '/requests.log');
		unlink(self::$docRoot . '/router.php');
		rmdir(self::$docRoot);
	}

	public function testHtmlFromRequestIsNotRendered()
	{
		$_REQUEST['html'] = '<img src="http://127.0.0.1:' . self::$port . '/image.png" width="10" height="10">';
		try {
			$pdf = new Pdf();
			$pdf->AddPage();
			$pdf->Output('test.pdf', 'S');
		} finally {
			unset($_REQUEST['html']);
		}

		$this->assertFileDoesNotExist(self::$docRoot . '/requests.log', 'The PDF header requested an address from the request');
	}
}
