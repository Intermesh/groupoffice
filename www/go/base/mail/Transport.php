<?php
/*
 * Copyright Intermesh
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


class Transport extends \Swift_SmtpTransport{
	
	public static function newGoInstance(){
		
		$o = new static (\GO::config()->smtp_server, \GO::config()->smtp_port, strtolower(\GO::config()->smtp_encryption));
		
		if(!empty(\GO::config()->smtp_username)){
			$o->setUsername(\GO::config()->smtp_username)
				->setPassword(\GO::config()->smtp_password);
		}
		
		if(!go()->getSettings()->smtpEncryptionVerifyCertificate) {
			$o->setStreamOptions(array('ssl' => array('allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name'  => false)));
		}
		return $o;
	}	
}
