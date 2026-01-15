<?php

namespace go\modules\business\wopi\filehandler;

use GO;
use GO\Files\Filehandler\FilehandlerInterface;
use GO\Files\Model\File;
use go\modules\business\wopi\model\Service ;

class Office365 implements FilehandlerInterface{

	public function isDefault(File $file){		
		return $this->fileIsSupported($file);
	}
	
	public function getName(){
		return go()->t("Office Online", 'business', 'wopi');
	}
	
	public function fileIsSupported(File $file) {    
    return Service::find()
      ->join('wopi_action', 'a', 'a.serviceId = s.id')
			->where(['a.ext' => $file->extension])
			->andWhere('type', '=', Service::TYPE_OFFICE_ONLINE)
			->single() != null;
	}
	
	public function getIconCls() {
		return 'ic-office-online';
	}
	
	public function getHandler(File $file) {
    $service = Service::find()
      ->join('wopi_action', 'a', 'a.serviceId = s.id')
			->where(['a.ext' => $file->extension])
			->andWhere('type', '=', Service::TYPE_OFFICE_ONLINE)
			->single();

		if(!$service) {
			return "";
		}

    $url = '/wopi/edit/' . $service->id . '/' . $file->id;

    return "window.open('".$url."');";
	}
	
}
