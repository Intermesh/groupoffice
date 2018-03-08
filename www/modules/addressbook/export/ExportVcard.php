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
 * VCard Output stream.
 * 
 * @copyright Copyright Intermesh BV.
 * @package GO.base.export
 */

namespace GO\Addressbook\Export;


class ExportVCard extends \GO\Base\Export\AbstractExport {
	
	public static $showInView = true;
	public static $name = "VCard";
	public static $useOrientation=false;
	
	private function _sendHeaders(){		
		$file = new \GO\Base\Fs\File($this->title.'.vcf');
		\GO\Base\Util\Http::outputDownloadHeaders($file);
	}

	private function _write($data){
		if(!isset($this->_fp))
			$this->_fp=fopen('php://output','w+');		

		fwrite($this->_fp,$data);
	}	
		
	public function output(){
		$this->_sendHeaders();
		
		
		
//		if($this->header){
//			if($this->humanHeaders){
//				
//				//workaround Libreoffice bug: https://bugs.freedesktop.org/show_bug.cgi?id=48347
//				$headers = array_values($this->getLabels());
//				
//				for($i=0;$i<count($headers);$i++){
//					if($headers[$i] == 'ID')
//						$headers[$i] = 'Id';
//				}
//				
//				$this->_write($headers);
//				// End of workaround
//				
//				//$this->_write(array_values($this->getLabels()));
//			}else
//				$this->_write(array_keys($this->getLabels()));
//		}
		
		while($record = $this->store->nextRecord()){
			$model = \GO\Addressbook\Model\Contact::model()->findByPk($record['id']);
			$this->_write($model->toVObject()->serialize());
		}
	}

}