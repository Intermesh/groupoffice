<?php
namespace go\core\util;

use DateTime as PHPDateTime;
use go\core\data\ArrayableInterface;

class DateTime extends PHPDateTime implements ArrayableInterface, \JsonSerializable {

	public $hasTime = true;
	
	/**
	 * The date outputted to the clients. It's according to ISO 8601;	 
	 */
	const FORMAT_API = "c";

	const FORMAT_API_DATE_ONLY = "Y-m-d";

	public function toArray($properties = null) {
		return $this->format($this->hasTime ? self::FORMAT_API : self::FORMAT_API_DATE_ONLY);
	}

	public function jsonSerialize() {
		return $this->format($this->hasTime ? self::FORMAT_API : self::FORMAT_API_DATE_ONLY);
	}
	
	public function __toString() {
		return $this->format($this->hasTime ? self::FORMAT_API : self::FORMAT_API_DATE_ONLY);
	}

	private static $currentUser;

	private static function currentUser() {
		if(!isset(self::$currentUser)) {
			self::$currentUser = go()->getAuthState()->getUser(['dateFormat', 'timezone', 'timeFormat' ]);
		}
		return self::$currentUser;
	}

	public function toUserFormat($withTime = false) {

		$f = self::currentUser()->dateFormat;
		if($withTime) {
			$date = clone $this;
			$date->setTimezone(new \DateTimeZone(self::currentUser()->timezone));
			$f .= ' ' . self::currentUser()->timeFormat;
			return $date->format($f);
		}
		return $this->format($f);
	}

}
