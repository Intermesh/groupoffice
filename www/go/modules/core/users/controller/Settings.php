<?php
namespace go\modules\core\users\controller;

use go\core\jmap\Controller;
use go\core\jmap\Response;


class Settings extends Controller {
	
	public function get() {
		$settings = \go\modules\core\users\model\Settings::get();
		Response::get()->addResponse($settings->toArray());
	}
	
	public function set($params) {
		
		$settings = \go\modules\core\users\model\Settings::get()->setValues($params);

		
		$success = $settings->save();
		
		Response::get()->addResponse(['success' => $success]);
	}
}