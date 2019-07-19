<?php

namespace go\core\mail;

use go\core\data\Model;

/**
 * An e-mail recipient
 * 
 * The recipient has an email address and peronsal part:
 * 
 * "Personal" <email@address.com>
 * 
 */
class Recipient extends Model {

	private $email;
	private $personal;

	public function __construct($email, $personal = null) {
		$this->email = $email;
		$this->personal = $personal;
	}

	/**
	 *  Get e-mail address
	 * 
	 * @param string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Get personal name
	 * 
	 * @param string
	 */
	public function getPersonal() {
		return $this->personal;
	}

	public function __toString() {
		if (!empty($this->personal)) {
			return '"' . $this->personal . '" <' . $this->email . '>';
		} else {
			return $this->email;
		}
	}

}
