<?php


namespace GO\Caldav\Schedule;


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
		
		if(empty($to)) {
			return;
		}
		
		$recipients = new \GO\Base\Mail\EmailRecipients($to);
		$to = $recipients->getAddress();
		
		$message = \GO\Base\Mail\Message::newInstance($subject)
						->setFrom(\GO::user()->email, \GO::user()->name)
						->addReplyTo(\GO::user()->email)
						->addTo($to['email'], $to['personal']);

		$message->setBody($body, "text/calendar; method=" . (string) $this->itipMessage->method, "utf-8");

		\GO\Base\Mail\Mailer::newGoInstance()->send($message);
	}

}
