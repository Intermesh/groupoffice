<?php

namespace GO\Base\Mail;


use go\core\ErrorHandler;
use go\core\mail\Address;
use Throwable;

class AdminNotifier {
	
	/**
	 * Can be used to notify the administrator by email
	 * 
	 * @param string $subject
	 * @param string $message
	 */
	public static function sendMail($subject, $body){

		try {
			$message = Message::newInstance();
			$message->setSubject($subject);

			$message->setBody($body, 'text/plain');
			$message->setFrom(\GO::config()->webmaster_email, \GO::config()->title);
			$message->addTo(new Address(\GO::config()->webmaster_email, 'Webmaster'));

			Mailer::newGoInstance()->send($message);
		} catch(Throwable $e) {
			ErrorHandler::log("Failed to send admin notification e-mail: " . $subject);
			ErrorHandler::logException($e);
		}
	}
}
