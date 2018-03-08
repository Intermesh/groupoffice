<?php

namespace go\core\util;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use IFW;

class Crypt {
	
	private static $tag = '{ifw1}';
	
	private static $key;

	/**
	 * Get the private server key
	 * @return Key
	 */
	private static function getKey() {
		
		if(!isset(self::$key)) {
			$file = \go\core\App::get()->getConfig()->getDataFolder()->getFile('crypt/key.txt');

			if (!$file->exists()) {				
				$file->getFolder()->create();
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
	 * Encrypt data reversible using the server key
	 * 
	 * @param string $secretData
	 * @return string Cypher text
	 */
	public function encrypt($secretData) {
		if(empty($secretData)) {
			return "";
		}
		return self::$tag.Crypto::encrypt($secretData, $this->getKey());
	}
	
	/**
	 * Decrypt data
	 * 
	 * @param string $ciphertext
	 * @return string Decrypted text
	 */
	public function decrypt($ciphertext) {
		
		if(empty($ciphertext)) {
			return "";
		}
		
		if(!$this->isEncrypted($ciphertext)) {
			throw new \Exception("Not encrypted with this utility");
		}
		
		$ciphertext = substr($ciphertext, strlen(self::$tag));
		
		return Crypto::decrypt($ciphertext, $this->getKey());		
	}
	
	public function isEncrypted($ciphertext) {
		return strpos($ciphertext, self::$tag) === 0;
	}

}
