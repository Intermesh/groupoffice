<?php
namespace go\modules\core\users\controller;

use go\core\jmap\Controller;
use go\core\jmap\Response;
use go\modules\core\users\model;


class Settings extends Controller {
	
	public function get() {
		$settings = model\Settings::get();
		Response::get()->addResponse($settings->toArray());
	}
	
	public function set($params) {
		
		$settings = model\Settings::get()->setValues($params);

		
		$success = $settings->save();
		
		Response::get()->addResponse(['success' => $success]);
	}
}
