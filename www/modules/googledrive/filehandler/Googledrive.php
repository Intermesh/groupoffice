<?php


namespace GO\Googledrive\Filehandler;


class Googledrive implements \GO\Files\Filehandler\FilehandlerInterface {

	private $supportedExtensions=array("odt","docx","ods","xlsx","pptx","pdf","PAGES",'doc','xls','ppt','odt','txt');
	
	
	public function isDefault(\GO\Files\Model\File $file) {
		return in_array(strtolower($file->extension), $this->supportedExtensions);
	}

	public function getName() {
		return 'Google Drive';
	}

	public function fileIsSupported(\GO\Files\Model\File $file) {
		return in_array(strtolower($file->extension), $this->supportedExtensions);
	}
	
	public function getIconCls(){
		return 'gd-googledrive-icon';
	}

	public function getHandler(\GO\Files\Model\File $file) {
		return 'GO.googledrive.edit('.$file->id.');';			
	}

}
?>
