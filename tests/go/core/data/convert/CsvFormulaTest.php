<?php

namespace go\core\data\convert;

use GO\Base\Export\ExportCSV;
use GO\Base\Export\ExportXLS;
use go\core\auth\TemporaryState;
use go\core\model\User;
use go\modules\community\notes\model\Note;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as PhpSpreadsheet;
use PHPUnit\Framework\TestCase;

/**
 * Exported text that starts with "=", "+", "-" or "@" must not become a formula when the file is opened in a
 * spreadsheet application (CSV injection).
 */
class CsvFormulaTest extends TestCase
{
	private $authState;

	private const ROW = ['=HYPERLINK("https://example.com","x")', '@SUM(A1)', '-12.5', 'text'];
	private const ESCAPED = ['\'=HYPERLINK("https://example.com","x")', "'@SUM(A1)", '-12.5', 'text'];

	private function csv(string $delimiter, string $enclosure): string
	{
		$fp = fopen('php://memory', 'w+');
		fputcsv($fp, self::ESCAPED, $delimiter, $enclosure, "");
		return $this->read($fp);
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
	}

	private function read($fp): string
	{
		rewind($fp);
		$data = stream_get_contents($fp);
		fclose($fp);
		return $data;
	}

	public function testSpreadsheetCsv(): void
	{
		$converter = new Spreadsheet('csv', Note::class);
		(new \ReflectionProperty(Spreadsheet::class, 'delimiter'))->setValue($converter, ',');
		$fp = fopen('php://memory', 'w+');
		(new \ReflectionProperty(Spreadsheet::class, 'fp'))->setValue($converter, $fp);
		(new \ReflectionMethod(Spreadsheet::class, 'writeRecord'))->invoke($converter, self::ROW);

		$this->assertEquals($this->csv(',', '"'), $this->read($fp));
	}

	public function testSpreadsheetCsvImportRemovesQuote(): void
	{
		$converter = new Spreadsheet('csv', Note::class);
		(new \ReflectionProperty(Spreadsheet::class, 'delimiter'))->setValue($converter, ',');
		$fp = fopen('php://memory', 'w+');
		fwrite($fp, $this->csv(',', '"'));
		rewind($fp);
		(new \ReflectionProperty(Spreadsheet::class, 'fp'))->setValue($converter, $fp);

		$this->assertEquals(self::ROW, (new \ReflectionMethod(Spreadsheet::class, 'readRecord'))->invoke($converter));
		fclose($fp);
	}

	public function testSpreadsheetXlsx(): void
	{
		$converter = new Spreadsheet('xlsx', Note::class);
		$spreadsheet = new PhpSpreadsheet();
		(new \ReflectionProperty(Spreadsheet::class, 'spreadsheet'))->setValue($converter, $spreadsheet);
		(new \ReflectionMethod(Spreadsheet::class, 'arrayToSpreadSheet'))->invoke($converter, 1, [self::ROW[0], -12.5]);

		$this->assertEquals(DataType::TYPE_STRING, $spreadsheet->getActiveSheet()->getCell('A1')->getDataType());
		$this->assertEquals(DataType::TYPE_NUMERIC, $spreadsheet->getActiveSheet()->getCell('B1')->getDataType());
	}

	public function testLegacyExportCsv(): void
	{
		$export = (new \ReflectionClass(ExportCSV::class))->newInstanceWithoutConstructor();
		$fp = fopen('php://memory', 'w+');
		$export->setFilePointer($fp);
		(new \ReflectionMethod(ExportCSV::class, '_write'))->invoke($export, self::ROW);

		$this->assertEquals($this->csv(\GO::user()->list_separator, \GO::user()->text_separator), $this->read($fp));
	}

	public function testLegacyCsvWriter(): void
	{
		$file = go()->getTmpFolder()->getFile(uniqid('csvformula') . '.csv');
		$writer = new \GO\Base\Csv\Writer($file->getPath());
		(new \ReflectionProperty(\GO\Base\Csv\Writer::class, 'delimiter'))->setValue($writer, ',');
		(new \ReflectionProperty(\GO\Base\Csv\Writer::class, 'enclosure'))->setValue($writer, '"');
		$writer->putRecord(self::ROW);
		unset($writer);

		$data = $file->getContents();
		$file->delete();
		// the writer starts with a UTF-8 byte order mark
		$this->assertEquals("\xEF\xBB\xBF" . $this->csv(',', '"'), $data);
	}

	public function testLegacyExportXls(): void
	{
		$export = (new \ReflectionClass(ExportXLS::class))->newInstanceWithoutConstructor();
		$spreadsheet = new PhpSpreadsheet();
		(new \ReflectionProperty(ExportXLS::class, '_sheet'))->setValue($export, $spreadsheet->getActiveSheet());
		(new \ReflectionProperty(ExportXLS::class, 'excel_row'))->setValue($export, 1);
		(new \ReflectionMethod(ExportXLS::class, '_write'))->invoke($export, [self::ROW[0], -12.5]);

		$this->assertEquals(DataType::TYPE_STRING, $spreadsheet->getActiveSheet()->getCell('A1')->getDataType());
		$this->assertEquals(DataType::TYPE_NUMERIC, $spreadsheet->getActiveSheet()->getCell('B1')->getDataType());
	}
}
