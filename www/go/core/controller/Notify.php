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

	public function mail($params) {		
		
		$settings = go()->getSettings();

		if(empty($params['to'])) {
			$params['to'] = new Address($settings->systemEmail, $settings->title);
		}
		
		$message = go()->getMailer()->compose()
						->setFrom($settings->systemEmail, $settings->title)
						->setTo($params['to'])
						->setSubject($params['subject'] ?? "")
						->setBody($params['body'] ?? "", $params['contentType'] ?? 'text/plain');

		if(isset($params['replyTo'])) {
			$message->setReplyTo($params['replyTo']);
		}

		$success = $message->send();
		
		return ['success' => $success];
	}
}

