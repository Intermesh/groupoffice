<?php

namespace go\core\controller;

use Exception;
use GO;
use go\core\Controller;
use go\core\jmap\Response;
use go\core\mail\Address;
use go\core\mail\AddressList;
use go\core\model;

class Notify extends Controller {

	/**
	 * Notifies the system administrator via email
	 *
	 * @param $params
	 * @return true[]
	 * @throws \PHPMailer\PHPMailer\Exception
	 */
	public function mail($params) {		
		
		$settings = go()->getSettings();
		
		$message = go()->getMailer()->compose()
						->setFrom($settings->systemEmail, $settings->title)
						->setTo(new Address($settings->systemEmail, $settings->title))
						->setSubject($params['subject'] ?? "")
						->setBody($params['body'] ?? "", $params['contentType'] ?? 'text/plain');

		if(isset($params['replyTo'])) {
			$message->setReplyTo($params['replyTo']);
		}

		$message->send();
		
		return ['success' => true];
	}
}

