<?php
namespace GO\Base\Export;

use PHPExcel;
use PHPExcel_IOFactory;

class ExportXLS extends AbstractExport {

	public static $showInView = true;
	public static $name = "XLS (Excel)";
	public static $useOrientation = false;

	private $_lines = array();
	
	private function _sendHeaders() {
		header('Content-Disposition: attachment; filename="' . $this->title . '.xls"');
		header('Content-Type: text/x-msexcel; charset=UTF-8');
	}

	private function _setupExcel() {
		// Include PHPExcel
		//require_once \GO::config()->root_path.'go/vendor/PHPExcel/PHPExcel.php';
		// Create new PHPExcel object
		$this->phpExcel = new PHPExcel();

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
			$this->_sheet->setCellValueByColumnAndRow($col, $this->excel_row, $val);
			$col++;
		}
		$this->excel_row++;
		//fputcsv($this->_fp, $data, \GO::user()->list_separator, \GO::user()->text_separator);
	}

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
				
				//$this->_write(array_values($this->getLabels()));
			}else
				$this->_write(array_keys($this->getLabels()));
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
		$writer = PHPExcel_IOFactory::createWriter($this->phpExcel, 'Excel5');
		//$tmpFilename = tempnam('./temp', 'tmp');
		
		$file = \GO\Base\Fs\File::tempFile();
		$writer->save($file->path());
		
		$file->output();
		
		$file->delete();
		
		
	}

}
