<?php

namespace go\modules\community\otp\controller;
use go\core\Controller;
use go\modules\community\otp\model\OtpValidator;

class Secret extends Controller {

	/**
	 * Check a code against a secret, while enabling two-factor authentication
	 *
	 * @apiPermission Module access: checks the code against the secret the client sends. No stored data is read.
	 * @apiReadOnly
	 */
	public function verify($params) {
		$this->checkParams($params, ['code', 'secret']);
		$validator = new OtpValidator();
		return ['valid' => $validator->verify($params['code'], $params['secret'])];
	}

}