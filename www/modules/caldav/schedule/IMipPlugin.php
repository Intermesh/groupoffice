<?php


namespace GO\Caldav\Schedule;


use go\core\ErrorHandler;
use go\core\model\Module;

class IMipPlugin extends \Sabre\CalDAV\Schedule\IMipPlugin{

	public function __construct($senderEmail=null) {
		if($senderEmail === null)
			$senderEmail = \GO::config()->webmaster_email;
		parent::__construct($senderEmail);
	}
	
	function schedule(\Sabre\VObject\ITip\Message $iTipMessage) {
		$this->itipMessage = $iTipMessage;
		parent::schedule($iTipMessage);
	}
	
	/**
     * This function is responsible for sending the actual email.
     *
     * @param string $to Recipient email address
     * @param string $subject Subject of the email
     * @param string $body iCalendar body
     * @param array $headers List of headers
     * @return void
     */
	protected function mail($to, $subject, $body, array $headers) {

		go()->debug("CalDAV IMip mail: " . $to ." : " . $subject);
		
		if(empty($to)) {
			return;
		}

		try {
			$recipients = new \GO\Base\Mail\EmailRecipients($to);
			$to = $recipients->getAddress();

			$message = \GO\Base\Mail\Message::newInstance($subject)
				->setFrom(\GO::user()->email, \GO::user()->name)
				->addReplyTo(\GO::user()->email)
				->addTo($to['email'], $to['personal']);

			$mailer = $this->getUserMailer();

			// Set sender to local address to avoid SPF issues. See also issue: Calendar event invite mail From address #924
			if($mailer->getTransport() instanceof \GO\Email\Transport) {
				$message->setSender($this->user->email);
			} else {
				$message->setSender(go()->getSettings()->systemEmail);
			}

			$message->setBody($body, "text/calendar; method=" . (string)$this->itipMessage->method, "utf-8");

			$mailer->send($message);
		} catch(\Throwable $e) {
			ErrorHandler::log("Error sending CalDAV IMip mail to " . implode("," , $to));
			ErrorHandler::logException($e);
		}
	}


	private function getUserMailer() {

		if(Module::isInstalled('legacy', 'email')) {
			$account = \GO\Email\Model\Account::model()->findByEmail(\GO::user()->email);
			if($account) {
				$transport = \GO\Email\Transport::newGoInstance($account);
				return \GO\Base\Mail\Mailer::newGoInstance($transport);
			}
			go()->debug("Can't find e-mail account for " . \GO::user()->email ." so will fall back on main SMTP configuration");

		}

		go()->debug("Using main SMTP configuration");

		return \GO\Base\Mail\Mailer::newGoInstance();
	}

}
