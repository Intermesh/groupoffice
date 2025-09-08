<?php
/**
 * Copyright Intermesh BV
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 */

/**
 * Reads OR writes CSV files.
 * 
 * @package GO.base.fs
 * @version $Id: XlsFile.php 18041 2014-08-28 08:49:13Z wilmar1980 $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @copyright Copyright Intermesh BV.
 */


namespace GO\Base\Fs;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class XlsFile extends File
{
		
	/**
	 * Full path to file.
	 * @var String
	 */
	protected $path;
	
	/**
	 * Type of XLS file.
	 * @var String
	 */
	protected $filetype;
	
	/**
	 * Spreadsheet object.
	 * @var Spreadsheet
	 */
	protected $phpExcelObj;

	/**
	 * @var boolean
	 */
	protected $readOnly;

	/**
	 * 2D-array of cell values. This is created by the constructor, and read from
	 * in getRecord()
	 * @var array
	 */
	protected $rowsBuffer;
	
	/**
	 * The maximum row number to read from.
	 * @var Int
	 */
	protected $maxRows;
	
	/**
	 * The maximum column number to read from.
	 * @var Int
	 */
	protected $maxColNr;
	
	/**
	 * Row number for the iterator in getRecord().
	 * @var Int
	 */
	protected $nextRowNr;
	
	/**
	 * The largest column size encountered in resetDimensions(). Used to store
	 * the bottom of the XLS file.
	 * @var Int
	 */
	protected $bottomOfFileRowNr;
	
	/**
	 * The maximum number of empty cells permitted to read from, before continuing
	 * to the next row/colum.
	 * @var Int
	 */
	protected $nAllowedEmptyCells;
	
	/**
	 * Nr of the sheet to use. Nr 0 = first sheet.
	 * @var Int $readSheetNr
	 */
	protected $sheetNr;
	
	/**
	 * Array of column width integers.
	 * @var array $columnWidths
	 */
	protected $columnWidths;

	/**
	 * @var int
	 */
	protected $rightEndOfFileColNr;
	
	/**
	 * Constructor.
	 * @param String $path The full path to the XLS file.
	 * @param Int $maxColNr The highest column number to read from.
	 * @param Int $maxRows The highest row number to read from.
	 * @param Int $readSheetNr The sheet number to read from
	 */
	public function __construct($path=false,$maxColNr=false,$maxRows=false) {
		
		$this->path=$path;
		$this->readOnly=true;		
		$this->sheetNr=0;
	
		$this->init();
				
		$this->maxRows = $maxRows!==false ? $maxRows : 1048576;
		$this->maxColNr = $maxColNr!==false ? $maxColNr : 26*26*2;
		$this->nAllowedEmptyCells = 1000;
		if (is_file($this->path))
			$this->resetDimensions();

		$this->nextRowNr = 1;
		
	}

	/**
	 * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
	 */
	protected function init($readSheetNr=0) {

		if (is_file($this->path)) {
			$this->filetype = IOFactory::identify($this->path);
			$xlsReader = IOFactory::createReader($this->filetype);
			$xlsReader->setReadDataOnly($this->readOnly);
			$this->phpExcelObj = $xlsReader->load($this->path);
		} else {
			$this->phpExcelObj = new Spreadsheet();
			$this->phpExcelObj->getProperties()->setCreator(\GO::config()->product_name);
			$this->phpExcelObj->getProperties()->setLastModifiedBy(\GO::config()->product_name);
			$this->phpExcelObj->getProperties()->setTitle("Office 2007 XLSX Document");
			$this->phpExcelObj->getProperties()->setSubject("Office 2007 XLSX Document");
		}
	}
	
	protected function resetDimensions() {
		
		$this->rowsBuffer = array();
		$HIGHEST_ROW_WITH_VALUE = -1;
		$HIGHEST_COLUMN_WITH_VALUE = -1;
										
		// First ascertain the dimensions of the worksheet.
		for ($row=1;$row<=$this->maxRows && $this->_maxEmptyCellsNotExceeded($row,$HIGHEST_ROW_WITH_VALUE);$row++) {		
			for ($col=0;$col<=$this->maxColNr && $this->_maxEmptyCellsNotExceeded($col,$HIGHEST_COLUMN_WITH_VALUE);$col++) {
								
				$value = $this->phpExcelObj->getSheet($this->sheetNr)->getCell([$col,$row])->getCalculatedValue();
				
				if ( isset($value) && $value!==NULL && $value!==false ) {
					
					// Keep track of the highest column nr that had a value in the cell.
					if ($col>$HIGHEST_COLUMN_WITH_VALUE)
						$HIGHEST_COLUMN_WITH_VALUE = $col;
					// Keep track of the highest row nr that had a value in the cell.
					if ($row>$HIGHEST_ROW_WITH_VALUE)
						$HIGHEST_ROW_WITH_VALUE = $row;
				}
								
			}
		}

		$this->rightEndOfFileColNr = $HIGHEST_COLUMN_WITH_VALUE;
		$this->bottomOfFileRowNr = $HIGHEST_ROW_WITH_VALUE;
		
	}
	
	protected function _maxEmptyCellsNotExceeded($cellNr,$highestCellWithValue): bool
	{
		return $cellNr-$highestCellWithValue<=$this->nAllowedEmptyCells;
	}
	
	public function getFileType() {
		return $this->filetype;
	}
		
	/**
	 * Retrieves the contents of the next row in the XLS file.
	 * @return array An array of elements read from the XLS line. Or false when there is no next record;
	 */
	public function getRecord(): array|false
	{

		if ($this->nextRowNr>$this->bottomOfFileRowNr)
			return false;
				
		$rowRecord = array();
		for ($col=0;$col<=$this->rightEndOfFileColNr;$col++) {
			$rowRecord[] = $this->phpExcelObj->getSheet($this->sheetNr)->getCell([$col,$this->nextRowNr])->getCalculatedValue();
		}
		$this->nextRowNr++;
		
		return $rowRecord;
	}

	/**
	 * TODO: Write record to XLS.
	 * @param array $fields The elements of this array will be written into a line
	 * of the current XLS file.
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 */
	public function putRecord(array $fields): void
	{
		
		foreach ($fields as $colNr=>$field) {
			if(array_is_list($fields)) {
				$colNr++;
			}
			if (empty($this->columnWidths[$colNr]) || $this->columnWidths[$colNr]<strlen($field)) {
				$this->columnWidths[$colNr] = strlen($field);
				$this->phpExcelObj->getSheet($this->sheetNr)->getColumnDimensionByColumn($colNr)->setWidth($this->columnWidths[$colNr]);
			}


			if(is_string($field) && isset($field[0]) && $field[0] == '=') {
				//prevent formula detection
				$this->phpExcelObj->getSheet($this->sheetNr)->setCellValueExplicit([$colNr, $this->nextRowNr], $field, DataType::TYPE_STRING);
			} else {
				$this->phpExcelObj->getSheet($this->sheetNr)->setCellValue([$colNr, $this->nextRowNr], $field);
			}
			
		}
		$this->nextRowNr++;
	}

	/**
	 * @throws Exception
	 */
	public function writeToFile() {
		
		$objWriter = new Xlsx($this->phpExcelObj);
		$objWriter->setPreCalculateFormulas(false);
		$objWriter->save($this->path);
				
	}

	/**
	 * @return Spreadsheet
	 */
	public function getExcelObject(): Spreadsheet
	{
		return $this->phpExcelObj;
	}

	/**
	 * @return Worksheet
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 */
	public function getCurrentSheet(): Worksheet
	{
		return $this->phpExcelObj->getSheet($this->sheetNr);
	}

	/**
	 * Switch sheets, to be chained with the getCurrentSheet() method
	 *
	 * @param int $i
	 * @return $this
	 * @throws Exception
	 */
	public function setSheetNumber(int $i): self
	{
		$numSheets = $this->phpExcelObj->getSheetCount();
		if( $i < 0 || $i > $numSheets) {
			throw new Exception(
				"Your requested sheet index: {$i} is out of bounds. The actual number of sheets is {$numSheets}."
			);
		}
		$this->sheetNr = $i;
		$this->nextRowNr = 1; // Back to the top
		return $this;
	}
}
