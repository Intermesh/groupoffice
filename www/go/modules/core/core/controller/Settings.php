<?php
namespace go\modules\core\core\controller;

use go\core\jmap\Controller;
use go\core\jmap\Response;


class Settings extends Controller {
	
	public function get() {
		Response::get()->addResponse((array) GO()->getSettings());
	}
	
	public function set($params) {
		foreach($params as $key => $value) {
			GO()->getSettings()->$key = $value;
		}
		
		$success = GO()->getSettings()->save();
		
		Response::get()->addResponse(['success' => $succces]);
	}
}