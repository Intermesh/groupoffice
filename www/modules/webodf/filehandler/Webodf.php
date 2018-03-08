<?php

namespace GO\Webodf\Filehandler;


class Webodf implements \GO\Files\Filehandler\FilehandlerInterface{

	public function isDefault(\GO\Files\Model\File $file){		
		return in_array(strtolower($file->extension), array('odt'));
	}
	
	public function getName(){
		return "WebODF";
	}
	
	public function fileIsSupported(\GO\Files\Model\File $file){
		return $this->isDefault($file);
	}
	
	public function getIconCls(){
		return 'btn-download-webodf';
	}
	
	public function getHandler(\GO\Files\Model\File $file){
		return 'window.open("'.\GO::url("webodf/file/edit",array('id'=>$file->id)).'");';
	}
}