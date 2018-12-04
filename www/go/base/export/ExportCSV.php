<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

/**
 * CSV Output stream.
 * 
 * @version $Id: ExportCSV.php 7607 2011-08-04 13:41:42Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @package GO.base.export
 */

namespace GO\Base\Export;


class ExportCSV extends AbstractExport {
	
	public static $showInView = true;
	public static $name = "CSV";
	public static $useOrientation=false;
	private $_fp;
	
	public function setFilePointer($value){
		$this->_fp = $value;
	}
	
	private function _sendHeaders(){	
		if(!isset($this->_fp)){
			$file = new \GO\Base\Fs\File($this->title.'.csv');
			\GO\Base\Util\Http::outputDownloadHeaders($file);
		}
	}

	private function _write($data){
		if(!isset($this->_fp)){
			$this->_fp=fopen('php://output','w+');		
		}		
		fputcsv($this->_fp, $data, \GO::user()->list_separator, \GO::user()->text_separator);
	}	
	
	public function output(){
		$this->_sendHeaders();
		
		//override default HTML output
		$this->store->getColumnModel()->setModelFormatType('formatted');
		
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
	}

}
