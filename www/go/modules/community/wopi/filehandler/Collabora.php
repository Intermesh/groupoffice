<?php

namespace go\modules\business\wopi\filehandler;

use GO;
use GO\Files\Filehandler\FilehandlerInterface;
use GO\Files\Model\File;
use go\modules\business\wopi\model\Service ;

class Collabora implements FilehandlerInterface{

	public function isDefault(File $file){		
		return $this->fileIsSupported($file);
	}
	
	public function getName(){
		$name = Service::find()
			->selectSingleValue('name')
			->andWhere('type', '=', Service::TYPE_COLLABORA)
			->single();

		if($name) {
			return $name;
		}

		return go()->t("Libre Office Online", 'business', 'wopi');
	}
	
	public function fileIsSupported(File $file) {    
    return Service::find()
      ->join('wopi_action', 'a', 'a.serviceId = s.id')
			->where(['a.ext' => $file->extension])
			->andWhere('type', '=', Service::TYPE_COLLABORA)
			->single() != null;
	}
	
	public function getIconCls() {
		return 'ic-lo';
	}
	
	public function getHandler(File $file) {
    $service = Service::find()
      ->join('wopi_action', 'a', 'a.serviceId = s.id')
			->where(['a.ext' => $file->extension])
			->andWhere('type', '=', Service::TYPE_COLLABORA)
			->single();

		if(!$service) {
			return "";
		}

    $url = '/wopi/edit/' . $service->id . '/' . $file->id;

    return "window.open('".$url."');";
	}
	
}
