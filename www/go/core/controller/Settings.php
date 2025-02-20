<?php

namespace go\core\controller;

use Exception;
use GO;
use go\core\Controller;
use go\core\exception\Forbidden;
use go\core\jmap\Response;
use go\core\model;

class Settings extends Controller {

	protected function authenticate()
	{
		parent::authenticate();

		if(!go()->getAuthState()->isAdmin()) {
			throw new Forbidden();
		}
	}

	public function sendTestMessage($params) {
		
		$settings = go()->getSettings()->setValues($params);
	
		$message = go()->getMailer()->compose()
						->setFrom($settings->systemEmail, $settings->title)
						->setTo($settings->systemEmail)
						->setSubject(go()->t('Test message'))
						->setBody(go()->t("Your settings are correct.\n\nBest regards,\n\nGroup-Office"));

		try {
			$message->send();
			Response::get()->addResponse(['success' => true]);
		} catch(Exception $e) {
			Response::get()->addError(['message' => $e->getMessage()]);
		}
	}
}
