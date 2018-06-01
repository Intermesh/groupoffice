<?php

namespace GO\Base\Mail;


class AdminNotifier {
	
	/**
	 * Can be used to notify the administrator by email
	 * 
	 * @param StringHelper $subject
	 * @param StringHelper $message 
	 */
	public static function sendMail($subject, $body){

		$message = Message::newInstance();
		$message->setSubject($subject);

		$message->setBody($body,'text/plain');
		$message->setFrom(\GO::config()->webmaster_email,\GO::config()->title);
		$message->addTo(\GO::config()->webmaster_email,'Webmaster');

		Mailer::newGoInstance()->send($message);
	}
}
