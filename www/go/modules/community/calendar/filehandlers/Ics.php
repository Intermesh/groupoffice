<?php

namespace go\modules\community\calendar\filehandlers;


use GO\Files\Filehandler\FilehandlerInterface;

class Ics implements FilehandlerInterface {

	public function isDefault(\GO\Files\Model\File $file) {
		return strtolower($file->extension) == 'ics';
	}

	public function getName(){
		return \GO::t("iCalendar importer", "calendar", 'community');
	}

	public function fileIsSupported(\GO\Files\Model\File $file){
		return strtolower($file->extension) == 'ics';
	}

	public function getIconCls(){
		return 'entity Event';
	}

	public function getHandler(\GO\Files\Model\File $file){
		return 'go.openIcs({id:'.$file->id.'});';
	}
}
