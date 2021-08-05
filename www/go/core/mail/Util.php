<?php
namespace go\core\mail;

class Util {
	
	/**
	 * Allow any char @ any char
	 */
	const EMAIL_REGEX = "/^[^@\s]*@[^@\s]*$/";
	
	/**
	 * Check if given email address is formatted correctly.
	 * @param string $email
	 * @return boolean
	 */
	public static function validateEmail($email) {
		return preg_match(self::EMAIL_REGEX, $email);
	}
}
