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
 * @package GO.module.email
 * @version $Id: RFC822.class.inc 7536 2011-05-31 08:37:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @copyright Copyright Intermesh BV.
 */


namespace GO\Email;


class Transport extends \Swift_SmtpTransport
{

	/**
	 * @param Model\Account $account
	 * @return Transport
	 */
	public static function newGoInstance(Model\Account $account)
	{
		$encryption = empty($account->smtp_encryption) ? null : $account->smtp_encryption;
		$o = new self($account->smtp_host, $account->smtp_port, $encryption);
		
		if (stripos($account->authenticationMethod, 'oauth') !== false ) {
			$o->setAuthMode('XOAUTH2')
				->setUsername($account->username)
				->setPassword($account->getXOauth2Token());
		} else if (!empty($account->smtp_username)){
			$o->setUsername($account->smtp_username)
				->setPassword($account->decryptSmtpPassword());
		}

		// TODO: Build in support for OAUUTH2 SMTP
		
		// Allow SSL/TLS and STARTTLS connection with self signed certificates. Enabling this will not check the identity of the server
		if ($account->smtp_allow_self_signed) {
			$o->setStreamOptions(array('ssl' => array('allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name'  => false)));
		}

		return $o;
	}	
}
