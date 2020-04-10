<?php
/**
 * Exception that will be throwed when a mailbox cannot be found on the mail server.
 */

namespace GO\Base\Mail\Exception;


use GO\Base\Mail\Imap;

class MailboxNotFound extends \Exception{
	
	public function __construct($mailbox, Imap $imap) {
		
		$last = $imap->last_error(); // Get the last error

		$message = sprintf(\GO::t("Cannot open the folder \"%s\". Please check your email account settings." . $last ),$mailbox);
		$imap->clear_errors(); // Needed to clear the imap errors
		
		parent::__construct($message);
	}
	
}
