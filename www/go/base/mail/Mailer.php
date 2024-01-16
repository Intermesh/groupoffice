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


class Mailer extends \go\core\mail\Mailer
{

	/**
	 * Create a new Mailer instance.
	 *
	 * @return Mailer

	 * Optionally supply a transport class. If omitted a Transport
	 * object will be created that uses the smtp settings from config.php
	 *
	 */
	public static function newGoInstance()
	{
		return new self();
	}
	
	public function send(\go\core\mail\Message $message) : bool {
		
		
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
			\GO::debug("E-mail debugging is enabled in the Group-Office config.php file. All emails are sent to: ".\GO::config()->debug_email);
		}
		$count = parent::send($message);

		return $count;
	}
}
