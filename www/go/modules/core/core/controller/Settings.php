<?php
namespace go\modules\core\core\controller;

use go\core\jmap\Controller;
use go\core\jmap\Response;


class Settings extends Controller {
	
	public function get() {
		Response::get()->addResponse(GO()->getSettings()->toArray());
	}
	
	public function set($params) {
		GO()->getSettings()->setValues($params);
		$success = GO()->getSettings()->save();
		
		Response::get()->addResponse(['success' => $success]);
	}
}