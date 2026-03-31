<?php

namespace go\core\controller;

use go\core\Controller;
use go\core\exception\Forbidden;
use go\core\mail\Address;

class Notify extends Controller {

	/**
	 * Notifies the system administrator via email
	 *
	 * @param $params
	 * @return true[]
	 * @throws \PHPMailer\PHPMailer\Exception
	 */
	public function mail($params) {

		if(!empty($params['to']) && !go()->getAuthState()->isAdmin()) {
			throw new Forbidden("You're not allowed to provide the 'to' recipients");
		}
		
		$settings = go()->getSettings();

		$message = go()->getMailer()->compose()
						->setFrom($settings->systemEmail, $settings->title)
						->setSubject($params['subject'] ?? "")
						->setBody($params['body'] ?? "", $params['contentType'] ?? 'text/plain');

		if(empty($params['to'])) {
			$message->setTo(new Address($settings->systemEmail, $settings->title));
		} else {
			$message->setTo($params['to']);
		}

		if(isset($params['replyTo'])) {
			$message->setReplyTo($params['replyTo']);
		}

		$message->send();
		
		return ['success' => true];
	}
}

