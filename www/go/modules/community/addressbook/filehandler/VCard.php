<?php

namespace go\modules\community\addressbook\filehandler;


use GO\Files\Filehandler\FilehandlerInterface;

class VCard implements FilehandlerInterface {

	public function isDefault(\GO\Files\Model\File $file) {
		return strtolower($file->extension) == 'vcf';
	}
	
	public function getName(){
		return \GO::t("vCard importer", "addressbook", "community");
	}
	
	public function fileIsSupported(\GO\Files\Model\File $file){
		return strtolower($file->extension) == 'vcf';
	}
	
	public function getIconCls(){
		return 'entity Contact';
	}
	
	public function getHandler(\GO\Files\Model\File $file){
		return 'go.modules.community.addressbook.importVcf({id:'.$file->id.'});';
	}
}
