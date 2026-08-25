<?php

namespace go\modules\community\otp\controller;
use go\core\Controller;
use go\modules\community\otp\model\OtpValidator;

class Secret extends Controller {

	public function verify($params) {
		$this->checkParams($params, ['code', 'secret']);
		$validator = new OtpValidator();
		return ['valid' => $validator->verify($params['code'], $params['secret'])];
	}

}