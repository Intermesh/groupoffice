<?php

namespace go\core\dav\schedule;

use go\core\ErrorHandler;
use go\core\mail\Address;
use go\core\mail\AddressList;
use go\core\model\Module;
use Sabre\VObject\ITip\Message;
use Throwable;

class IMipPlugin extends \Sabre\CalDAV\Schedule\IMipPlugin{

	private $itipMessage;

	function schedule(Message $iTipMessage) {
		$this->itipMessage = $iTipMessage;
		parent::schedule($iTipMessage);
	}
	protected function mail($to, $subject, $body, array $headers) {

		go()->debug("CalDAV IMip mail: " . $to ." : " . $subject . ". ". var_export($headers, true));

		$user = go()->getAuthState()->getUser(['email', 'displayName']);

		if(empty($to)) {
			return;
		}

		// Sabredav does not format the address correctly. When a , inside the name the parsing will go wrong. They shoukd
		// use quotes: https://www.rfc-editor.org/rfc/rfc5322.html#section-3.2
		// For example : John, Doe <johndoe@foo.com> should be  "John, Doe" <johndoe@foo.com>

		if(preg_match("/(.*)\s<(.*)>/", $to, $toParts)) {
			$to = new Address($toParts[2], $toParts[1]);
		}

		try {
			$mailer = $this->getMailer();
			$mailer->compose()
				->setSubject($subject)
				->setFrom($user->email, $user->displayName)
				->setReplyTo($user->email)
				->addTo($to)
				->setSender($mailer->getSender())// Set sender to local address to avoid SPF issues. See also issue: Calendar event invite mail From address #924
				->setBody($body, "text/calendar;method=" . $this->itipMessage->method)
				->send();

		} catch(Throwable $e) {
			ErrorHandler::log("Error sending CalDAV IMip mail to " . var_export($to, true));
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
