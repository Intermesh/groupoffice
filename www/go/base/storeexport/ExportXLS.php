<?php
/**
 * @depcreated or at least never used as per 6.8
 */
namespace GO\Base\Storeexport;

/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * XLS Output stream.
 * 
 */
class ExportXLS extends AbstractExport {

	public static $showInView = true;
	public static $name = "XLS (Excel)";
	public static $useOrientation = false;

	private $_sheet;

	/** @var int */
	private $excel_row;

	/**
	 * @var Spreadsheet
	 */
	private $phpExcel;

	private function _sendHeaders() {
		header('Content-Disposition: attachment; filename="' . $this->title . '.xls"');
		header('Content-Type: text/x-msexcel; charset=UTF-8');
	}

	/**
	 * @throws Exception
	 */
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
			$this->_sheet->setCellValueByColumnAndRow($col, $this->excel_row, $val);
			$col++;
		}
		$this->excel_row++;
	}

	/**
	 * @throws Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 * @throws \Exception
	 */
	public function output() {
		$this->_sendHeaders();

		$this->_setupExcel();


		if ($this->header) {
			if ($this->humanHeaders) {
				$this->_write(array_values($this->getLabels()));
			}else
				$this->_write(array_keys($this->getLabels()));
		}

		while ($record = $this->store->nextRecord()) {
			$record = $this->prepareRecord($record);
			$this->_write($record);
		}

		// Hack to write contents of file to string
		$writer = IOFactory::createWriter($this->phpExcel, 'Xls');

		$file = \GO\Base\Fs\File::tempFile();
		$writer->save($file->path());
		
		$file->output();
		
		$file->delete();
		
		
	}

}
