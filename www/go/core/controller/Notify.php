<?php

namespace go\core\controller;

use Exception;
use GO;
use go\core\Controller;
use go\core\jmap\Response;
use go\core\model;

class Notify extends Controller {

	public function mail($params) {		
		
		$settings = GO()->getSettings();
		
		$message = GO()->getMailer()->compose()
						->setFrom($settings->systemEmail, $settings->title)
						->setTo($params['to'] ?? $settings->systemEmail)
						->setSubject($params['subject'] ?? "")
						->setBody($params['body'] ?? "");

		$success = $message->send();
		
		Response::get()->addResponse(['success' => $success]);
	}
}

