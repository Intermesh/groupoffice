<?php


namespace GO\Caldav\Schedule;


use go\core\ErrorHandler;
use go\core\mail\AddressList;
use go\core\model\Module;

class IMipPlugin extends \Sabre\CalDAV\Schedule\IMipPlugin{

	private \Sabre\VObject\ITip\Message $itipMessage;

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
			go()->getLanguage()->setLanguage(go()->getAuthState()->getUser(['language'])->language);

			$summary = $this->itipMessage->message->VEVENT->SUMMARY;
			switch (strtoupper($this->itipMessage->method)) {
				case 'REPLY':
					$subject = 'Re: '.$summary;
					break;
				case 'REQUEST':
					$subject = go()->t('Invitation', 'legacy','calendar') .': '.$summary;
					break;
				case 'CANCEL':
					$subject = go()->t('Cancelled', 'legacy','calendar').': '.$summary;
					break;
			}

			$recipients = new AddressList($to);
			$to = $recipients[0];

			$message = \GO\Base\Mail\Message::newInstance($subject)
				->setFrom(\GO::user()->email, \GO::user()->name)
				->setReplyTo(\GO::user()->email)
				->addTo($to);

			$mailer = $this->getUserMailer();

			// Set sender to local address to avoid SPF issues. See also issue: Calendar event invite mail From address #924
			if($mailer->hasAccount()) {
				$message->setSender(\GO::user()->email);
			} else {
				$message->setSender(go()->getSettings()->systemEmail);
			}

			$message->setBody($body, "text/calendar; method=" . (string)$this->itipMessage->method, "utf-8");

			$mailer->send($message);
		} catch(\Throwable $e) {
			ErrorHandler::log("Error sending CalDAV IMip mail to " . $to);
			ErrorHandler::logException($e);
		}
	}


	private function getUserMailer() {

		if(Module::isInstalled('legacy', 'email')) {
			$account = \GO\Email\Model\Account::model()->findByEmail(\GO::user()->email);
			if($account) {
				$mailer = \GO\Base\Mail\Mailer::newGoInstance();
				$mailer->setEmailAccount($account);
				return $mailer;
			}
			go()->debug("Can't find e-mail account for " . \GO::user()->email ." so will fall back on main SMTP configuration");

		}

		go()->debug("Using main SMTP configuration");

		return \GO\Base\Mail\Mailer::newGoInstance();
	}

}
