<?php
namespace go\modules\community\jitsimeet\controller;

use go\core\Controller;
use go\modules\community\jitsimeet\Module;

class Auth extends Controller {
	/**
	 * Create a JWT to join a Jitsi Meet room
	 *
	 * @apiPermission Module access: creating online meetings is the purpose of the module. The token is only valid for the Jitsi server and no GroupOffice data is read.
	 */
	public function generateJWT(array $params) : array {
		$s = Module::get()->getSettings();
		return [
			'success' => true,
			'jwt' => $s->createJwtToken($params['room'])
		];
	}
}