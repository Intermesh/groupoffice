<?php

namespace go\core\controller;

use Exception;
use GO;
use go\core\Controller;
use go\core\jmap\Response;
use go\core\mail\Address;
use go\core\model;

class Notify extends Controller {

	public function mail($params) {		
		
		$settings = go()->getSettings();

		if(!empty($params['to'])) {
			$to = [];
			foreach($params['to'] as $email=>$name) {
				$to[] = new Address($email, $name);
			}
		} else {
			$to = [new Address($settings->systemEmail, $settings->title)];
		}
		
		$message = go()->getMailer()->compose()
						->setFrom($settings->systemEmail, $settings->title)
						->setTo(...$to)
						->setSubject($params['subject'] ?? "")
						->setBody($params['body'] ?? "", $params['contentType'] ?? null);

		if(isset($params['replyTo'])) {
			$message->setReplyTo($params['replyTo']);
		}

		$success = $message->send();
		
		Response::get()->addResponse(['success' => $success]);
	}
}

