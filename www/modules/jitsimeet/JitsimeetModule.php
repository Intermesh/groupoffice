<?php


namespace GO\Jitsimeet;

use go\core\util\StringUtil;
use GO\Jitsimeet\model\Settings;
use GO\Calendar\Controller\EventController;

class JitsimeetModule extends \GO\Base\Module{

	public function package() {
		return self::PACKAGE_IN_DEVELOPMENT;
	}
	
	public function depends(){
		return ['calendar','email'];
	}

	public static function initListeners() {
		// Add trigger
		$c = new EventController();
		//$c->addListener('submit', '\GO\Jitsimeet\JitsimeetModule', 'addJitsiLink');
		$c->addListener('load', '\GO\Jitsimeet\JitsimeetModule', 'checkIfHasLink');
	}

	static function hasLink($event) {
		return !$event->isNew() && strpos($event->description, Settings::get()->jitsiUri) !== false;
	}

//	public static function addJitsiLink(&$self,&$response,&$model,&$params,$modifiedAttributes){
//		//generate unique link and concat to end of description.
//		$uri = Settings::get()->jitsiUri;
//		if(!empty($params['jitsiMeet']) && !self::hasLink($model) && !empty($uri)) {
//			$model->description .= "\n".trim(Settings::get()->jitsiUri, "\n/") . '/' . StringUtil::random(16);
//			$model->save(); // model is already saved
//		}
//	}

	public static function checkIfHasLink(&$self, &$response,&$model,&$params){
		//find link in description
		$response['data']['jitsiMeet'] = self::hasLink($model);
	}

	public function getSettings() {
		return Settings::get();
	}
}
