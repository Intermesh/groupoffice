<?php

namespace go\core\controller;

use Exception;
use GO;
use go\core\Controller;
use go\core\jmap\Response;
use go\core\model;

class Settings extends Controller {

	public function sendTestMessage($params) {
		
		$settings = GO()->getSettings()->setValues($params);
	
		$message = GO()->getMailer()->compose()
						->setFrom($settings->systemEmail, $settings->title)
						->setTo($settings->systemEmail)
						->setSubject(GO()->t('Test message'))
						->setBody(GO()->t("You're settings are correct.\n\nBest regards,\n\nGroup-Office"));

		$success = $message->send();
		
		Response::get()->addResponse(['success' => $success]);
	}
}
