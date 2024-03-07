<?php
namespace GO\Base\Export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Exception;

class ExportXLS extends AbstractExport
{

	public static $showInView = true;
	public static $name = "XLS (Excel)";
	public static $useOrientation = false;

	private $_lines = array();
	private $_sheet;
	private $excel_row;

	private $phpExcel;
	
	private function _sendHeaders() {
		header('Content-Disposition: attachment; filename="' . $this->title . '.xls"');
		header('Content-Type: text/x-msexcel; charset=UTF-8');
	}

	private function _setupExcel() {
		$this->phpExcel = new Spreadsheet();

		// Set document properties
		$this->phpExcel->getProperties()->setCreator(\GO::config()->product_name)
						->setLastModifiedBy(\GO::config()->product_name)
						->setTitle($this->title)
						->setSubject("Export")
						->setDescription("An export from ".\GO::config()->product_name)
						->setKeywords("")
						->setCategory("export");

		// Set default font
		$this->phpExcel->getDefaultStyle()->getFont()->setName('Verdana')
						->setSize(10);

		$bold = array(
				"font" => array(
						"bold" => true,
				)
		);
		$this->_sheet = $this->phpExcel->getActiveSheet();
		$this->_sheet->getStyle("1:1")->applyFromArray($bold);
		$this->_sheet->setTitle("Export");
		$this->phpExcel->setActiveSheetIndex(0);
		$this->excel_row = 1;
	}

	private function _write($data) {
		$col = 0;
		foreach ($data as $key => $val) {
			$this->_sheet->setCellValue([$col, $this->excel_row], $val);
			$col++;
		}
		$this->excel_row++;
	}

	/**
	 * @throws Exception
	 * @throws \Exception
	 */
	public function output() {
		$this->_sendHeaders();

		$this->_setupExcel();


		if($this->header){
			if($this->humanHeaders){
				
				//workaround Libreoffice bug: https://bugs.freedesktop.org/show_bug.cgi?id=48347
				$headers = array_values($this->getLabels());
				
				for($i=0;$i<count($headers);$i++){
					if($headers[$i] == 'ID')
						$headers[$i] = 'Id';
				}
				
				$this->_write($headers);
				// End of workaround
			} else {
				$this->_write(array_keys($this->getLabels()));
			}
		}
		
		while($record = $this->store->nextRecord()){
			$record = $this->prepareRecord($record);
			$this->_write($record);
		}
		
		// If extra lines given, then add them to the .csv file
		if(is_array($this->_lines)){
			foreach($this->_lines as $record){
				$record = $this->prepareRecord($record);
				$this->_write($record);
			}
		}

		// Hack to write contents of file to string
		$writer = IOFactory::createWriter($this->phpExcel, 'Xls');

		$file = \GO\Base\Fs\File::tempFile();
		$writer->save($file->path());
		
		$file->output();
		
		$file->delete();
		
		
	}

}
