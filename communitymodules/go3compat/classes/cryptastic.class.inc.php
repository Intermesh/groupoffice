<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: cryptastic.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/*
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

-------------------------------------------------------------------------*/

class cryptastic {

	/** Encryption Procedure
	 *
	 *	@param   mixed    msg      message/data
	 *	@param   StringHelper   k        encryption key
	 *	@param   boolean  base64   base64 encode result
	 *
	 *	@return  StringHelper   iv+ciphertext+mac or
	 *           boolean  false on error
	 */
	public function encrypt( $msg, $k='', $base64 = true ) {

		if ($msg="")
			return "";
		
		//Check if mcrypt is supported. mbstring.func_overload will mess up substring with this function
		if(!function_exists('mcrypt_module_open') || ini_get('mbstring.func_overload')>0)
			return false;

		if(empty($k)){
			$k=$this->get_key();
			if(empty($k)){
				throw new Exception('Could not generate private key');
			}
		}
		
		# open cipher module (do not change cipher/mode)
		if ( ! $td = mcrypt_module_open('rijndael-256', '', 'ctr', '') )
			return false;

		$msg = serialize($msg);							# serialize
		$iv  = mcrypt_create_iv(32, MCRYPT_RAND);		# create iv

		if ( mcrypt_generic_init($td, $k, $iv) !== 0 )	# initialize buffers
			return false;

		$msg  = mcrypt_generic($td, $msg);				# encrypt
		$msg  = $iv . $msg;								# prepend iv
		$mac  = $this->pbkdf2($msg, $k, 1000, 32);		# create mac
		$msg .= $mac;									# append mac

		mcrypt_generic_deinit($td);						# clear buffers
		mcrypt_module_close($td);						# close cipher module

		if ( $base64 ) $msg = base64_encode($msg);		# base64 encode?
		
		return "{GOCRYPT}".$msg;									# return iv+ciphertext+mac
	}

	/** Decryption Procedure
	 *
	 *	@param   StringHelper   msg      output from encrypt()
	 *	@param   StringHelper   k        encryption key
	 *	@param   boolean  base64   base64 decode msg
	 *
	 *	@return  StringHelper   original message/data or
	 *           boolean  false on error
	 */
	public function decrypt( $msg, $k='', $base64 = true ) {

		//Check if mcrypt is supported. mbstring.func_overload will mess up substring with this function
		if(!function_exists('mcrypt_module_open') || ini_get('mbstring.func_overload')>0)
			return false;

		$msg = str_replace("{GOCRYPT}", "", $msg, $count);

		if($count!=1)
			return false;
		
		
		if(empty($k)){
			$k=$this->get_key();
			if(empty($k)){
				throw new Exception('Could not generate private key');
			}
		}

		if ( $base64 ) $msg = base64_decode($msg);			# base64 decode?

		# open cipher module (do not change cipher/mode)
		if ( ! $td = mcrypt_module_open('rijndael-256', '', 'ctr', '') )
			return false;

		$iv  = substr($msg, 0, 32);							# extract iv
		$mo  = strlen($msg) - 32;							# mac offset
		$em  = substr($msg, $mo);							# extract mac
		$msg = substr($msg, 32, strlen($msg)-64);			# extract ciphertext
		$mac = $this->pbkdf2($iv . $msg, $k, 1000, 32);		# create mac

		if ( $em !== $mac )									# authenticate mac
			return false;

		if ( mcrypt_generic_init($td, $k, $iv) !== 0 )		# initialize buffers
			return false;

		$msg = mdecrypt_generic($td, $msg);					# decrypt
		$msg = unserialize($msg);							# unserialize

		mcrypt_generic_deinit($td);							# clear buffers
		mcrypt_module_close($td);							# close cipher module

		return $msg;										# return original msg
	}

	/** PBKDF2 Implementation (as described in RFC 2898);
	 *
	 *	@param   StringHelper  p   password
	 *	@param   StringHelper  s   salt
	 *	@param   int     c   iteration count (use 1000 or higher)
	 *	@param   int     kl  derived key length
	 *	@param   StringHelper  a   hash algorithm
	 *
	 *	@return  StringHelper  derived key
	 */
	public function pbkdf2( $p, $s, $c, $kl, $a = 'sha256' ) {

		$hl = strlen(hash($a, null, true));	# Hash length
		$kb = ceil($kl / $hl);				# Key blocks to compute
		$dk = '';							# Derived key

		# Create key
		for ( $block = 1; $block <= $kb; $block ++ ) {

			# Initial hash for this block
			$ib = $b = hash_hmac($a, $s . pack('N', $block), $p, true);

			# Perform block iterations
			for ( $i = 1; $i < $c; $i ++ )

			# XOR each iterate
				$ib ^= ($b = hash_hmac($a, $b, $p, true));

			$dk .= $ib; # Append iterated block
		}

		# Return derived key of correct length
		return substr($dk, 0, $kl);
	}

	private function get_key(){
		global $GO_CONFIG;

		if(isset($_SESSION['GO_SESSION']['global_key'])){
			return $_SESSION['GO_SESSION']['global_key'];
		}

		$key_file=$GLOBALS['GO_CONFIG']->file_storage_path.'key.txt';
		
		if(file_exists($key_file)){
			$key = file_get_contents($key_file);
		}else
		{
			require_once($GLOBALS['GO_CONFIG']->class_path.'base/users.class.inc.php');
			$GO_USERS = new GO_USERS();

			$key = $GO_USERS->random_password('a-z,A-Z,1-9', 'i,o', 20);
			if(file_put_contents($key_file, $key)){
				chmod($key_file,0400);				
			}else
			{
				return false;
			}
		}

		$_SESSION['GO_SESSION']['global_key']=$key;
		return $key;

	}
}
