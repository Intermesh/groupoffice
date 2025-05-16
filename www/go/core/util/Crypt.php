<?php
namespace go\core\util;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key;
use Exception;
use GO;
use go\core\ErrorHandler;
use go\core\fs\File;

class Crypt {

	/**
	 * Encrypt text
	 *
	 * @param string $plaintext
	 * @param string|null $password
	 * @return string
	 * @throws EnvironmentIsBrokenException
	 */
	public static function encrypt(string $plaintext, string $password = null): string
	{
		if (!isset($password)) {
			$key = self::getKey();
			return "{GOCRYPT2}" . Crypto::encrypt($plaintext, $key);
		} else {
			return "{GOCRYPT2}" . Crypto::encryptWithPassword($plaintext, $password);
		}
	}

	/** Decryption Procedure
	 *
	 * @param string   $ciphertext      output from encrypt()
	 * @param string   $password        encryption key
	 *
	 * @return  string   original message/data or
	 *           boolean  false on error
	 * @throws Exception
	 */
	public static function decrypt(string $ciphertext, $password = null) : string {

		if (empty($ciphertext)) {
			return "";
		}

		if (substr($ciphertext, 0, 9) == '{GOCRYPT}') {
			return self::decrypt1($ciphertext, $password);
		} else if (substr($ciphertext, 0, 10) == '{GOCRYPT2}') {
			try {
				if (empty($password)) {
					$k = self::getKey();
					$plaintext = Crypto::decrypt(substr($ciphertext, 10), $k);
				} else {
					$plaintext = Crypto::decryptWithPassword(substr($ciphertext, 10), $password);
				}
			} catch (Exception $ex) {
				ErrorHandler::log("Failed to decrypt!");
				ErrorHandler::logException($ex);
				$plaintext = '';
			}
			return $plaintext;
		} else {
			return $ciphertext;
		}
	}

	private static $key;

	/**
	 * Get the private server key
	 *
	 * @return Key
	 * @throws BadFormatException
	 * @throws EnvironmentIsBrokenException
	 */
	private static function getKey(): Key
	{
		if (!isset(self::$key)) {
			$file = go()->getDataFolder()->getFile('defuse-crypto.txt');

			if (!$file->exists()) {
				self::$key = Key::createNewRandomKey();
				$file->putContents(self::$key->saveToAsciiSafeString());
				$file->chmod(0600);
			} else {
				self::$key = Key::loadFromAsciiSafeString($file->getContents());
			}
		}


		return self::$key;
	}

	/**
	 * Old deprecated mcrypt base decrypt.
	 * 
	 * @param string $msg
	 * @param ?string $k
	 * @param bool $base64
	 * @return string|bool
	 * @throws Exception
	 */
	private static function decrypt1(string $msg, ?string $k = null, bool $base64 = true)
	{

		//mbstring.func_overload will mess up substring with this function
		
		if(ini_get('mbstring.func_overload') > 0) {
			throw new Exception("Can't decrypt because mbstring.func_overload is enabled");
		}

		$msg = str_replace("{GOCRYPT}", "", $msg, $count);

		if ($count != 1)
			return false;

		if (empty($k)) {
			$k = self::getKey1();
			if (empty($k)) {
				throw new Exception('Could not generate private key');
			}
		}

		if ($base64)
			$msg = base64_decode($msg);# base64 decode?
		# open cipher module (do not change cipher/mode)
		if (!$td = phpseclib_mcrypt_module_open('rijndael-256', '', 'ctr', ''))
			return false;

		$iv = substr($msg, 0, 32);		# extract iv
		$mo = strlen($msg) - 32;		# mac offset
		$em = substr($msg, $mo);		# extract mac
		$msg = substr($msg, 32, strlen($msg) - 64);	# extract ciphertext
		$mac = self::pbkdf2($iv . $msg, $k, 1000, 32); # create mac

		if ($em !== $mac)		 # authenticate mac
			return false;

		if (phpseclib_mcrypt_generic_init($td, $k, $iv) !== 0) # initialize buffers
			return false;

		$msg = phpseclib_mdecrypt_generic($td, $msg);	 # decrypt
		$msg = unserialize($msg);		# unserialize

		phpseclib_mcrypt_generic_deinit($td);		# clear buffers
		phpseclib_mcrypt_module_close($td);		# close cipher module

		return $msg;		 # return original msg
	}

	/** PBKDF2 Implementation (as described in RFC 2898);
	 *
	 * 	@param   string  $p   password
	 * 	@param   string  $s   salt
	 * 	@param   int     $c   iteration count (use 1000 or higher)
	 * 	@param   int     $kl  derived key length
	 * 	@param   string  $a   hash algorithm
	 *
	 * 	@return  string  derived key
	 */
	private static function pbkdf2($p, $s, $c, $kl, $a = 'sha256') {

		$hl = strlen(hash($a, null, true)); # Hash length
		$kb = ceil($kl / $hl);	# Key blocks to compute
		$dk = '';		# Derived key
		# Create key
		for ($block = 1; $block <= $kb; $block++) {

			# Initial hash for this block
			$ib = $b = hash_hmac($a, $s . pack('N', $block), $p, true);

			# Perform block iterations
			for ($i = 1; $i < $c; $i++)

			# XOR each iterate
				$ib ^= ($b = hash_hmac($a, $b, $p, true));

			$dk .= $ib; # Append iterated block
		}

		# Return derived key of correct length
		return substr($dk, 0, $kl);
	}

	private static function getKey1() {

		$key_file = go()->getDataFolder()->getFile('key.txt');

		if ($key_file->exists()) {
			$key = $key_file->getContents();
		} else {
			throw new Exception("Encryoption key for old method not found!");
		}
		return $key;
	}

}
