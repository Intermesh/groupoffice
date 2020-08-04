<?php
/*
 * Copyright Intermesh BV
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

/**
 * This class is used to parse and write RFC822 compliant recipient lists
 * 
 * @package GO.base.mail
 * @version $Id: RFC822.class.inc 7536 2011-05-31 08:37:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @copyright Copyright Intermesh BV.
 */


namespace GO\Base\Mail;


class Mailer extends \Swift_Mailer{
	
	/**
   * Create a new Mailer instance.
   * 
	 * @var \Swift_SmtpTransport $transport. 
	 * Optionally supply a transport class. If omitted a Transport 
	 * object will be created that uses the smtp settings from config.php
	 * 
   * @return Mailer
   */
  public static function newGoInstance($transport=false)
  {
		if(!$transport)
			$transport=Transport::newGoInstance();
		
    $mailer = new self($transport);		
		return $mailer;
  }
	
	public function send(\Swift_Mime_SimpleMessage $message, &$failedRecipients = null) {
		
		
		if(!empty(\GO::config()->disable_mail)){
			throw new \Exception("E-mail sending is disabled!");
		}
		
		
		if(\GO::config()->debug){
			$getTo = $message->getTo();

			if(!empty($getTo)){
				$getTo = implode(",",array_keys($getTo));
			} else {
				$getTo = '';
			}
			
			\GO::debug("Sending e-mail to ".$getTo);
		}
		
		if(!empty(\GO::config()->debug_email)){
			$message->setTo(\GO::config()->debug_email);
			$message->setBcc(array());
			$message->setCc(array());
			\GO::debug("E-mail debugging is enabled in the Group-Office config.php file. All emails are send to: ".\GO::config()->debug_email);
		}
		
//		if(\GO::modules()->isInstalled("log")){
//
//			$str = "";
//
//			$from = $message->getFrom ();
//			if(!empty($from))
//				$str .= implode(",",array_keys($from));
//			else
//				$str .= "unknown";
//
//			$str .= " -> ";
//
//			$to = $message->getTo ();
//			if(!empty($to))
//				$str .= implode(",",array_keys($to));
//
//			$to = $message->getCc ();
//			if(!empty($to))
//				$str .= implode(",",array_keys($to));
//
//			$to = $message->getBcc ();
//			if(!empty($to))
//				$str .= implode(",",array_keys($to));
//
//			\GO\Log\Model\Log::create ("email", $str);
//		}
		
//		debug_print_backtrace();
//		exit("NO MAIL");
		
		//workaround https://github.com/swiftmailer/swiftmailer/issues/335
		$messageId = $message->getId();
		
		if(!empty(\GO::config()->force_swift_header_base64_encoding)){
			foreach($message->getHeaders()->getAll() as $header) {
				if($header->getFieldName() != 'Subject')
					$header->setEncoder(new \Swift_Mime_HeaderEncoder_Base64HeaderEncoder());
			}
		}
		
		$count = parent::send($message, $failedRecipients);
		
		$message->setId($messageId);
		
		// Check if a tmp dir is created to store attachments.
		// If so, then remove the tmp dir if the mail is send successfully.
		$tmpDir = $message->getTmpDir();
		if(!empty($tmpDir)){
			$folder = new \GO\Base\Fs\Folder($tmpDir);
			// Check if folder is deleted successfully
			if($folder->delete())
				\GO::debug('Clear attachments tmp directory: '.$tmpDir);
			else
				\GO::debug('Failed to clear attachments tmp directory: '.$tmpDir);
		}
		
		return $count;
	}
}
