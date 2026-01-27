<?php
namespace go\modules\community\jitsimeet\controller;

use go\core\Controller;
use go\modules\community\jitsimeet\Module;

class Auth extends Controller {
	public function generateJWT(array $params) : array {
		$s = Module::get()->getSettings();
		return [
			'success' => true,
			'jwt' => $s->createJwtToken($params['room'])
		];
	}
}