<?php
/*
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * 
 * Encrypt data
 * 
 * Original code is from:
 * -------------------------------------------------------------------------

  Cryptastic, by Andrew Johnson (2009).
  http://www.itnewb.com/user/Andrew

  You are free to use this code for personal/business use,
  without attribution, although it would be appreciated.

  -----------------------------------------------------------------------

  CAUTION, CAUTION, CAUTION! USE AT YOUR OWN RISK!

  It's your duty to use good passwords, salts and keys; and come up
  with an adequately safe techinque to store and access them.

  ------------------------------------------------------------------------- 
 
 * Common utilities
 * 
 * @author Andrew Johnson  http://www.itnewb.com/user/Andrew
 * @version $Id: config.class.inc.php 7687 2011-06-23 12:00:34Z mschering $
 * @copyright Cryptastic, by Andrew Johnson (2009).
 * @package GO.base.util 
 */



namespace GO\Base\Util;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Exception;
use GO;
use GO\Base\Fs\File;

/**
 * @deprecated
 */
class Crypt {

	/** Encryption Procedure
	 *
	 * 	@param   mixed    msg      message/data
	 * 	@param   string   k        encryption key
	 * 	@param   boolean  base64   base64 encode result
	 *
	 * 	@return  string   ciphertext or
	 *           boolean  false on error
	 */
	public static function encrypt($plaintext, $password = null) {		
		return \go\core\util\Crypt::encrypt($plaintext, $password);
	}

	/** Decryption Procedure
	 *
	 * 	@param   string   msg      output from encrypt()
	 * 	@param   string   k        encryption key
	 * 	@param   boolean  base64   base64 decode msg
	 *
	 * 	@return  string   original message/data or
	 *           boolean  false on error
	 */
	public static function decrypt($ciphertext, $password = null) {
		return \go\core\util\Crypt::decrypt($ciphertext, $password);
	}

	/**
	 * Util functio for encrypting passwords
	 * @param string $password password to be encrypted
	 * @return string the encrypted password
	 */
	public static function encryptPassword($password) {
		return password_hash($password,PASSWORD_DEFAULT);		
	}
	
	/**
	 * Check an encrypted password for validity
	 * @param string $password the password to check
	 * @param string $encrypted_password the hash that was saved
	 * @param string $type type of encryption (can be crypt or anything else)
	 * @return boolean true if the password is valid
	 */
	public static function checkPassword($password, $encrypted_password, $type='crypt'){
		
		if ($type == 'crypt') {
			
			if(function_exists('password_verify')) {
				if(!password_verify($password, $encrypted_password)){
					return false;
				}
			} else if (crypt($password, $encrypted_password) != $encrypted_password) {
				return false;
			}
			
		} else if (md5($password) != $encrypted_password) {
			return false;
		}
		
		return true;
	}

}
