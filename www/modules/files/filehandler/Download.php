<?php

namespace GO\Files\Filehandler;


class Download implements FilehandlerInterface{

	public function isDefault(\GO\Files\Model\File $file) {
		return false;
	}
	
	public function getName(){
		return \GO::t("Download");
	}
	
	public function fileIsSupported(\GO\Files\Model\File $file){
		return true;
	}
	
	public function getIconCls(){
		return 'btn-download';
	}
	
	public function getHandler(\GO\Files\Model\File $file){
		return 'GO.files.downloadFile("'.$file->id.'");';
	}
}

