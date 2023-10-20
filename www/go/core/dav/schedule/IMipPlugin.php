<?php

namespace go\core\dav\schedule;

use go\core\ErrorHandler;
use go\core\model\Module;

class IMipPlugin extends \Sabre\CalDAV\Schedule\IMipPlugin{

//	public function __construct($senderEmail=null) {
//		if($senderEmail === null)
//			$senderEmail = \GO::config()->webmaster_email;
//		parent::__construct($senderEmail);
//	}

	function schedule(\Sabre\VObject\ITip\Message $iTipMessage) {
		$this->itipMessage = $iTipMessage;
		parent::schedule($iTipMessage);
	}
	protected function mail($to, $subject, $body, array $headers) {

		go()->debug("CalDAV IMip mail: " . $to ." : " . $subject);

		if(empty($to)) {
			return;
		}

		try {
			$recipients = new \GO\Base\Mail\EmailRecipients($to);
			$to = $recipients->getAddress();

			$mailer = $this->getMailer();
			$mailer->compose()
				->setSubject($subject)
				->setFrom(\GO::user()->email, \GO::user()->name)
				->addReplyTo(\GO::user()->email)
				->addTo($to['email'], $to['personal'])
				->setSender($mailer->getSender())// Set sender to local address to avoid SPF issues. See also issue: Calendar event invite mail From address #924
				->setBody($body, "text/calendar; method=" . (string)$this->itipMessage->method, "utf-8")
				->send();

		} catch(\Throwable $e) {
			ErrorHandler::log("Error sending CalDAV IMip mail to " . implode("," , $to));
			ErrorHandler::logException($e);
		}
	}
	private function getMailer() {
		$mailer = go()->getMailer();
		if(Module::isInstalled('legacy', 'email')) {
			$account = \GO\Email\Model\Account::model()->findByEmail(go()->getAuthState()->getUser(['email'])->email);
			if($account) {
				$mailer->setEmailAccount($account);
			} else {
				go()->debug("Can't find e-mail account for " . go()->getAuthState()->getUser(['email'])->email . " so will fall back on main SMTP configuration");
			}
		} else {
			go()->debug("Using main SMTP configuration");
		}
		return $mailer;
	}

}
