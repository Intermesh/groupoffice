<?php
namespace go\core\util;

use DateTime as PHPDateTime;
use DateTimeZone;
use Exception;
use go\core\model\User;
use JsonSerializable;

class DateTime extends PHPDateTime implements JsonSerializable {

	/**
	 * Indicates if the date should format with or without time.
	 *
	 * @var bool
	 */
	public bool $hasTime = true;
	/**
	 * When true this is a date-time string with no time zone/offset information.
	 * The timezone information is saved in a different property
	 * @var bool
	 */
	public $isLocal = false;
	
	/**
	 * The date outputted to the clients. It's according to ISO 8601;	 
	 */
	const FORMAT_API = "c";

	const FORMAT_API_LOCAL = "Y-m-d\TH:i:s";

	/**
	 * API format for a date without time
	 */
	const FORMAT_API_DATE_ONLY = "Y-m-d";

	public function jsonSerialize(): mixed
	{
		return $this->format($this->hasTime ? ($this->isLocal ? self::FORMAT_API_LOCAL : self::FORMAT_API) : self::FORMAT_API_DATE_ONLY);
	}
	
	public function __toString() {
		return $this->jsonSerialize();
	}

	static function intervalToISO(\DateInterval $interval, $default = 'PT0S') {
		static $f = ['M0S', 'H0M', 'DT0H', 'M0D', 'P0Y', 'Y0M', 'P0M'];
		static $r = ['M', 'H', 'DT', 'M', 'P', 'Y', 'P'];

		return rtrim(str_replace($f, $r, $interval->format('P%yY%mM%dDT%hH%iM%sS')), 'PT') ?: $default;
	}

//	public function toLocal() {
//		$this->isLocal = true;
//		$this->setTimezone(new \DateTimeZone(self::currentUser()->timezone)); // prevent GO from converting to UTC
//	}

	private static ?User $currentUser;

	private static function currentUser() : User {
		if(!isset(self::$currentUser)) {
			self::$currentUser = go()->getAuthState()->getUser(['dateFormat', 'timezone', 'timeFormat' ]);
			if(!self::$currentUser) {
				self::$currentUser = User::findById(1, ['dateFormat', 'timezone', 'timeFormat'], true);
			}
		}
		return self::$currentUser;
	}

	public function toUserFormat(bool $withTime = false, User $user = null): string
	{
		if(!isset($user)) {
			$user = self::currentUser();
		}
		// In case a user is not logged in
		if( empty($user) || empty($user->dateFormat)) {
			return $withTime ? $this->format(self::FORMAT_API) : $this->format(self::FORMAT_API_DATE_ONLY);
		}
		$f = $user->dateFormat;
		if($withTime) {
			$date = clone $this;
			$date->setTimezone(new DateTimeZone($user->timezone));
			$f .= ' ' . $user->timeFormat;
			return $date->format($f);
		}
		return $this->format($f);
	}

	/**
	 * Overridden because it should return static. Apparently this has been fixed in PHP 8
	 * https://bugs.php.net/bug.php?id=79975
	 *
	 * @param string $format
	 * @param string $datetime
	 * @param DateTimeZone|null $timezone
	 * @return static
	 * @throws Exception
	 */
	public static function createFromFormat(string $format, string $datetime, DateTimeZone $timezone = null): DateTime
	{
		return new static("@" . parent::createFromFormat($format, $datetime, $timezone)->format("U"));
	}

	/**
	 * Get the number of days in a given year
	 *
	 * @param int $year
	 * @return int
	 */
	public static function daysInYear(int $year) : int {
		return date("L", mktime(0, 0, 0, 1, 1, $year)) ? 366 : 365;
	}

	/**
	 * Get the number of weeks for a given year
	 *
	 * @param int $year
	 * @return int
	 * @throws Exception
	 */
	public static function weeksInYear(int $year): int
	{
		$dt = new DateTime($year . '-12-28');
		$n = intval($dt->format('W'));
		if ($dt->format('w') > 4) {
			$n++;
		}
		return $n;
	}

}
