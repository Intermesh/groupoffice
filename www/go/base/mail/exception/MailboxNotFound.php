<?php
/**
 * Exception that will be throwed when a mailbox cannot be found on the mail server.
 */

namespace GO\Base\Mail\Exception;


class MailboxNotFound extends \Exception{
	
	public function __construct($mailbox,$imap) {
		
	//	$imap->last_error(); // Get the last error

		$message = sprintf(\GO::t('MailboxNotFoundException'),$mailbox);
		$imap->clear_errors(); // Needed to clear the imap errors
		
		parent::__construct($message);
	}
	
}