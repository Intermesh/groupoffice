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
class Address extends Model {

	private $email;
	private $name;

	public function __construct(string $email, ?string $name = null) {
		$this->email = $email;
		$this->name = $name;
	}

	/**
	 *  Get e-mail address
	 * 
	 * @param string
	 */
	public function getEmail(): string
	{
		return $this->email;
	}

	/**
	 * Get personal name
	 *
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	public function __toString() {
		if (!empty($this->name)) {
			return '"' . $this->name . '" <' . $this->email . '>';
		} else {
			return $this->email;
		}
	}

}
