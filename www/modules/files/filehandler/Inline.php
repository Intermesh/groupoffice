<?php

namespace GO\Files\Filehandler;


class Inline implements FilehandlerInterface{

	private $defaultExtensions = array('pdf','html','htm','txt','xml','log');
	
	public function isDefault(\GO\Files\Model\File $file) {
		return in_array(strtolower($file->extension), $this->defaultExtensions);
	}
	
	public function getName(){
		return \GO::t('openInBrowser','files');
	}
	
	public function fileIsSupported(\GO\Files\Model\File $file){
		return $file->isImage() || in_array(strtolower($file->extension),$this->defaultExtensions);
	}
	
	public function getIconCls(){
		return 'fs-browser';
	}
	
	public function getHandler(\GO\Files\Model\File $file){
		return 'window.open("'.$file->getDownloadUrl(false, true).'");';
	}
}
?>