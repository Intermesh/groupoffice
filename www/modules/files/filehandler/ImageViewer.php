<?php

namespace GO\Files\Filehandler;


class ImageViewer implements FilehandlerInterface{

	public function isDefault(\GO\Files\Model\File $file) {
		return $file->isImage();
	}
	
	public function getName(){
		return \GO::t("Image viewer", "files");
	}
	
	public function fileIsSupported(\GO\Files\Model\File $file){
		return $file->isImage();
	}
	
	public function getIconCls(){
		return 'fs-imageviewer';
	}
	
	public function getHandler(\GO\Files\Model\File $file){
		return 'GO.files.showImageViewer({id:'.$file->id.'});';
	}
}
