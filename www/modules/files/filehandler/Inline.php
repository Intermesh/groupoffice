<?php

namespace GO\Files\Filehandler;


class Inline implements FilehandlerInterface{

	private $defaultExtensions = array('pdf','html','htm','txt','xml','log', 'webm', 'mov', 'mp4', 'avi', 'ogv', 'wav','mp3', 'ogg');
	
	public function isDefault(\GO\Files\Model\File $file) {
		return in_array(strtolower($file->extension), $this->defaultExtensions);
	}
	
	public function getName(){
		return \GO::t("Open in browser", "files");
	}
	
	public function fileIsSupported(\GO\Files\Model\File $file){
		// don't support inline svg as they may contain scripts
		return ($file->isImage() && strtolower($file->extension) != 'svg') || in_array(strtolower($file->extension),$this->defaultExtensions);
	}
	
	public function getIconCls(){
		return 'fs-browser';
	}
	
	public function getHandler(\GO\Files\Model\File $file){
		return 'go.util.viewFile("'.$file->getDownloadUrl(false, true).'");';
	}
}

