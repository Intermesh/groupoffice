<?php
namespace go\modules\community\mailserverconnector\controller;

use go\core\jmap\Controller;
use go\core\jmap\Response;
use go\modules\community\mailserverconnector\model;


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
