<?php


namespace GO\Jitsimeet;

use go\core\util\StringUtil;
use GO\Jitsimeet\model\Settings;
use GO\Calendar\Controller\EventController;

class JitsimeetModule extends \GO\Base\Module{

	public function autoInstall()
	{
		return true;
	}

	public function package() {
		return self::PACKAGE_COMMUNITY;
	}
	
	public function depends(){
		return ['calendar'];
	}

	public static function initListeners() {
		// Add trigger
		$c = new EventController();
		//$c->addListener('submit', '\GO\Jitsimeet\JitsimeetModule', 'addJitsiLink');
		$c->addListener('load', '\GO\Jitsimeet\JitsimeetModule', 'checkIfHasLink');
	}

	static function hasLink($event) {
		return !$event->isNew() && !empty($event->description) && strpos($event->description, Settings::get()->jitsiUri) !== false;
	}

//	public static function addJitsiLink(&$self,&$response,&$model,&$params,$modifiedAttributes){
//		//generate unique link and concat to end of description.
//		$uri = Settings::get()->jitsiUri;
//		if(!empty($params['jitsiMeet']) && !self::hasLink($model) && !empty($uri)) {
//			$model->description .= "\n".trim(Settings::get()->jitsiUri, "\n/") . '/' . StringUtil::random(16);
//			$model->save(); // model is already saved
//		}
//	}

	private static function customBase64($string): string {
		return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
	}
	private static function createJwtToken($appIs, $room, $secret): string {
		$header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
		$payload = json_encode(['aud' => $appIs, 'iss' => $appIs, 'room' => $room]);
		
		$base64UrlHeader = self::customBase64($header);
		$base64UrlPayload = self::customBase64($payload);
		
		$signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
		$base64UrlSignature = self::customBase64($signature);
		
		return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
	}
	
	static function generateRoom(): string {
		$settings = Settings::get();
		$appId = $settings->jitsiJwtAppId;
		$room =  StringUtil::random(5);
		return $settings->jitsiJwtEnabled ? $room .'?jwt=' .self::createJwtToken($appId, $room, $settings->jitsiJwtSecret) : $room;
	}
	public static function checkIfHasLink($self, &$response,$model,&$params){
		//find link in description
		$hasLink = self::hasLink($model);
		$response['data']['jitsiMeet'] = $hasLink;
		if(!$hasLink)
			$response['data']['jitsiRoom'] = self::generateRoom();
	}

	public function getSettings() {
		return Settings::get();
	}

}
