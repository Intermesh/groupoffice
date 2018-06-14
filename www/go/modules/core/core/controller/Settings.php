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

	public function sendTestMessage($params) {
		
		$settings = GO()->getSettings()->setValues($params);
	
		$message = \GO()->getMailer()->compose()
						->setFrom($settings->systemEmail, $settings->title)
						->setTo($settings->systemEmail)
						->setSubject(\GO()->t('Test message'))
						->setBody(\GO()->t("You're settings are correct.\n\nBest regards,\n\nGroup-Office"));

		$success = $message->send();
		
		Response::get()->addResponse(['success' => $success]);
	}

}
