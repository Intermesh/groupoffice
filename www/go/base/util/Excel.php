<?php
namespace GO\Base\Util;
class Excel extends \PHPExcel {
	
	private $_writer;
	
	private function _getWriter() {
		if (empty($this->_writer))
			$this->_writer = new \PHPExcel_Writer_Excel2007($this);
		
		return $this->_writer;
	}
	
	public function save($filePath) {
		
		$this->_getWriter()->save($filePath);
		
	}
	
	public function setDefaultStyle($fontFamily='Arial',$fontSize=10,$colorRGB='00000000',$bold=false,$italic=false,$underline=false) {
		$colorObj = new \PHPExcel_Style_Color();
		$colorObj->setRGB($colorRGB);
		$fontObj = new \PHPExcel_Style_Font();
		$fontObj->setName($fontFamily);
		$fontObj->setSize($fontSize);
		$fontObj->setColor($colorObj);
		$fontObj->setBold($bold);
		$fontObj->setItalic($italic);
		$fontObj->setUnderline($underline);
		
		$styleObj = new \PHPExcel_Style();
		$styleObj->setFont($fontObj);
		$this->getActiveSheet()->setDefaultStyle($styleObj);
	}
	
	public function setStyle($cellRange,$fontFamily='Arial',$fontSize=10,$colorRGB='00000000',$bold=false,$italic=false,$underline=false) {
		$colorObj = new \PHPExcel_Style_Color();
		$colorObj->setRGB($colorRGB);
		$fontObj = new \PHPExcel_Style_Font();
		$fontObj->setName($fontFamily);
		$fontObj->setSize($fontSize);
		$fontObj->setColor($colorObj);
		$fontObj->setBold($bold);
		$fontObj->setItalic($italic);
		$fontObj->setUnderline($underline);
		
		$styleObj = new \PHPExcel_Style();
		$styleObj->setFont($fontObj);
		$this->getActiveSheet()->setSharedStyle($styleObj, $cellRange);
		
	}
	
//	public function setFontByColIdAndRowNr($colId,$rowNr,$fontFamily='Arial',$fontSize=10,$colorRGB='00000000',$bold=false,$italic=false,$underline=false) {
//		
//		require_once(GO::config()->root_path.'go/vendor/PHPExcel/PHPExcel/Style.php');
//		require_once(GO::config()->root_path.'go/vendor/PHPExcel/PHPExcel/Style/Font.php');
//		require_once(GO::config()->root_path.'go/vendor/PHPExcel/PHPExcel/Style/Color.php');
//		$colorObj = new PHPExcel_Style_Color();
//		$colorObj->setRGB($colorRGB);
//		$fontObj = new PHPExcel_Style_Font();
//		$fontObj->setName($fontFamily);
//		$fontObj->setSize($fontSize);
//		$fontObj->setColor($colorObj);
//		$fontObj->setBold($bold);
//		$fontObj->setItalic($italic);
//		$fontObj->setUnderline($underline);
//		
//		$this->getActiveSheet()->getCellByColumnAndRow($colId, $rowNr)->getStyle()->setFont($fontObj);
//		
//	}
	
	public function setWrapContent($cellRange,$wrapContent=true) {
		$this->getActiveSheet()->getStyle($cellRange)->getAlignment()->setWrapText($wrapContent);
	}
	
	public function setTextHorizontalAlignment($cellRange,$alignment=PHPExcel_Style_Alignment::HORIZONTAL_LEFT) {
		$this->getActiveSheet()->getStyle($cellRange)->getAlignment()->setHorizontal($alignment);
	}
	
	public function setDefaultWidth($width=-1) {
		$this->getActiveSheet()->getDefaultColumnDimension()->setWidth($width);
	}
	
	public function setWidth($column='A',$width=-1) {
		
		$this->getActiveSheet()->getColumnDimension($column)->setWidth($width);
		
	}
	
	public function setHeight($row=1,$height=-1) {
		
		$this->getActiveSheet()->getRowDimension($row)->setRowHeight($height);
		
	}
	
	public function setCellValue($cellId,$value) {
		
		$this->getActiveSheet()->setCellValue($cellId,$value);
		
	}
	
	public function setCellValueByColumnAndRow($colId,$rowNr,$value) {
		
		$this->getActiveSheet()->setCellValueByColumnAndRow($colId,$rowNr,$value);
		
	}
	
}
