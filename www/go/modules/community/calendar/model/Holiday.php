<?php
namespace go\modules\community\calendar\model;
use go\core\ErrorHandler;

class Holiday {

	const Public = 'public'; // public holiday
	const Bank='bank'; // banks and offices are closed
	const School='school'; // school holiday, schools are closed
	const Optional = 'optional'; // majority people take a day off
	const Observance = 'observance'; // optional festivity, no paid day off

	private static $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
	private static $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

	static $lang;
	static $names;

	private $year;

	public string $title;

	public $start;

	public ?string $region;

	private $substitutes;

	public string $type = 'public';

	public string  $duration = 'P1D';

	public function __construct(string $rule, $data,$year) {
		$this->year = $year;
		if(!isset($data->_name) && !isset($data->name)) {
			$data->_name = $rule;
		}
		$this->title = self::findTitle($data);

		if($this->title === 'Día de Castilla-La Mancha'){
			$this->title .= '1';
		}
		if(isset($data->type)) {
			$this->type = $data->type;
		}
		if(!$this->parseDateRule(explode(' ', $rule))){
			if(go()->getConfig()['debug']) {
				throw new \Exception('unparsable rule ' . $rule);
			} else {
				go()->error('unparsable rule ' . $rule);
			}
		}
		if($this->substitutes) {
			$this->title ='(*)'.$this->title;
		}
	}

	static private function findTitle($data): string {
		if(isset($data->_name)) {
			if(!isset(self::$names->{$data->_name}))
				return $data->_name ?? $data->name;
			$lang = self::$names->{$data->_name}->name;
		} else if(isset($data->name)) {
			$lang = $data->name;
		} else {
			return $data->_name ?? $data->name;
		}
		if(isset($lang->{self::$lang}))
			return $lang->{self::$lang};
		$la = substr(self::$lang,0,2);
		if(isset($lang->{$la}))
			return $lang->{$la};
		if(isset($lang->en))
			return $lang->en;

		return $data->_name ?? $data->name;
	}

	static private function findRegionName($obj) {
		if(isset($obj->name))
			return $obj->name;

		if(isset($obj->names->{self::$lang}))
			return $obj->names->{self::$lang};

		$la = substr(self::$lang,0,2);
		if(isset($obj->names->{$la}))
			return $obj->names->{$la};
		if(isset($obj->names->en))
			return $obj->names->en;
		return '';
	}


	static function generate($set, $lang, $from, $till) {
		$dir = __DIR__.'/../holidays/';
		self::$lang = $lang;
		self::$names = \json_decode(file_get_contents($dir.'names.json'))->names;
		$file = $dir.'countries/'.strtolower($set).'.json';

		if(!file_exists($file)) {
			ErrorHandler::log("Could not find holidays for '$set'");
			return;
		}
		$data = \json_decode(file_get_contents($file));

		if(!$data || !$data->holidays || !$data->holidays->$set || !is_object($data->holidays->$set->days))
			throw new \Exception('error processing file '.$file);

		foreach(self::generateYear($set,$data,$from, $till, $from->format('Y')) as $item)
			yield $item;
		if($from->format('Y') !== $till->format('Y')) {
			foreach(self::generateYear($set,$data,$from, $till, $till->format('Y')) as $item)
				yield $item;
		}

	}

	private static function generateYear($set, $data, $from, $till, $year) {
		foreach((array)$data->holidays->{$set}->days as $rule => $entry) {
			$holiday = new self($rule, $entry, $year);
			if($holiday->start !== null && $holiday->start >= $from && $holiday->start <= $till) {
				$holiday->start = $holiday->start->format('Y-m-d');
				yield $holiday;
			}
		}
		if(isset($data->holidays->{$set}->states)) {
			foreach((array)$data->holidays->{$set}->states as $obj) {
				if(!isset($obj->days))
					continue;
				foreach($obj->days as $rule => $entry) {
					if($entry === false) {
						continue; // we do not remove substitutes for regions.
					}
					$holiday = new self($rule, $entry, $year);
					//$holiday->title .= ' ('.self::findRegionName($obj).')';

					if($holiday->start !== null && $holiday->start >= $from && $holiday->start <= $till) {
						$holiday->region = self::findRegionName($obj);
						$holiday->start = $holiday->start->format('Y-m-d');
						yield $holiday;
					}
				}
			}
		}
	}

	/**
	 * https://github.com/commenthol/date-holidays-parser/blob/21fd7e2eca3df9ee99c0a221ae739a9c99b34e59/docs/specification.md
	 * @param string[] $str
	 * @param int $year
	 * @return bool
	 */
	private function parseDateRule($rule) {

		$part = array_shift($rule);
		if($part === 'substitutes') {
			$this->substitutes = true;
			$part = array_shift($rule);
		}
		if(in_array($part, ['chinese', 'bengali-revised'])) {
			return false;
		}

		if(preg_match('/\d{2}-\d{2}/', $part) || in_array($part, self::$months)) {
			$this->parseFixed($part);
		} else if($this->isMovable($part, $rule)) {
			$this->parseMovable($part, $rule);
		} else if(preg_match('/\d+(st|nd|rd|th)/', $part)) {
			$this->parseCount($part, $rule);
		} else if(in_array(strtolower($part), self::$days)) {
			array_unshift($rule, $part);
			$this->parseCount(1, $rule);
		} else {
			return false;
		}

		if(@$rule[0] === 'if') {
			array_shift($rule);
			$this->parseCondition($rule);
		}
		if ($this->start !== null && @$rule[0] === 'since') {
			array_shift($rule);
			$this->parseSince($rule);
		}
		return true;
	}

	private function parseFixed($monthDay) {
		$year = $this->year;
		if(strlen($monthDay) === 10) { // has year
			$year = intval(substr($monthDay,0,4));
			$monthDay = substr($monthDay, 5);
		}
		if($month = array_search($monthDay, self::$months)) {
			$monthDay = str_pad($month+1, 2, '0', STR_PAD_LEFT).'-00';
		}
		$this->start = new \DateTime($year.'-'.$monthDay);
	}

	private function isMovable($part, $rule) {
		if(in_array($part, ['easter', 'persian'])) {
			return true;
		}
		// $part would be the dayOfMonth number if we are here
		if(empty($rule)) {
			return false;
		}
		return preg_match('/^(Muharram|Safar|Rabi al-awwal|Rabi al-thani|Jumada al-awwal|Jumada al-thani|Rajab|Shaban|Ramadan|Shawwal|Dhu al-Qidah|Dhu al-Hijjah)/', $rule[0]. (isset($rule[1]) ? ' '.$rule[1] : '')) ||
			preg_match('/^(Nisan|Iyyar|Sivan|Tammuz|Av|Elul|Tishrei|Cheshvan|Kislev|Tevet|Shevat|Adar)$/', $rule[0]) ||
			preg_match('/^(Farvardin|Ordibehesht|Khordad|Tir|Mordad|Shahrivar|Mehr|Aban|Azar|Dey|Bahman|Esfand)$/', $rule[0]);
	}

	/**
	 * @param string $type either easter | orthodox or the month number for other movable dates
	 * @param string[] $rule the rest of the rule or there might be spaces in islamic month names
	 */
	private function parseMovable($type, $rule): void {
		if ($type === 'easter' || $type === 'orthodox') {
			$ed = easter_days($this->year);
			$days = array_shift($rule);
			$ed += intval($days);
			$this->start = new \DateTime($this->year . '-03-21');
			$this->start->modify(($ed >= 0 ? '+' : '') . $ed . ' days');
			return;
		}
		if(!is_numeric($type)){
			return; // we expected numeric at this point because of the isMovable() check
		}
		$dayOfMonth = (int)$type;
		// Islamic (Hijri)
		if (preg_match('/^(Muharram|Safar|Rabi al-awwal|Rabi al-thani|Jumada al-awwal|Jumada al-thani|Rajab|Shaban|Ramadan|Shawwal|Dhu al-Qidah|Dhu al-Hijjah)/', $rule[0]. (isset($rule[1]) ? ' '.$rule[1] : ''), $m)) {
			$this->parseIntl('islamic', self::islamicMonths[$m[1]], $dayOfMonth);
		}
		// Hebrew
		elseif (preg_match('/^(Nisan|Iyyar|Sivan|Tammuz|Av|Elul|Tishrei|Cheshvan|Kislev|Tevet|Shevat|Adar)$/', $rule[0], $m)) {
			$month = self::hebrewMonths[$m[0]];
			$jd = jewishtojd($month, $dayOfMonth, $this->year + ($month >= 7 ? 3761 : 3762));
			$this->start = new \DateTime(jdtogregorian($jd));
		}
		// Persian
		elseif (preg_match('/^(Farvardin|Ordibehesht|Khordad|Tir|Mordad|Shahrivar|Mehr|Aban|Azar|Dey|Bahman|Esfand)$/', $rule[0], $m)) {
			$this->parseIntl('persian', self::persianMonths[$m[1]], $dayOfMonth);
		}
	}

	private function parseIntl($calendar, $month, $dayOfMonth) {
		$gregorianCalendar = \IntlCalendar::createInstance(null, '@calendar=gregorian');
		if (version_compare(PHP_VERSION, '8.3.0') >= 0) { // 8.3 or up
			$gregorianCalendar->setDate($this->year, 0, 1);
		} else { // calling set with more then 2 parameters became deprecated in php 8.4
			$gregorianCalendar->set($this->year, 0, 1); // January 1, current year
		}

		$otherCalendar = \IntlCalendar::createInstance(null, '@calendar='.$calendar);
		$otherCalendar->setTime($gregorianCalendar->getTime());

		$otherYear = $otherCalendar->get(\IntlCalendar::FIELD_YEAR);

		$otherCalendar->set(\IntlCalendar::FIELD_YEAR, $otherYear);
		$otherCalendar->set(\IntlCalendar::FIELD_MONTH, $month); // Months are 0-indexed
		$otherCalendar->set(\IntlCalendar::FIELD_DAY_OF_MONTH, $dayOfMonth);

		$timestamp = (int)($otherCalendar->getTime() / 1000);
		// Check if this date falls in the target Gregorian year
		$resultYear = date('Y', $timestamp);
		if ($resultYear != $this->year) {
			// Try the next Hijri year
			$otherCalendar->set(\IntlCalendar::FIELD_YEAR, $otherYear + 1);
			$timestamp = $otherCalendar->getTime() / 1000;
		}
		$this->start = (new \DateTime())->setTimestamp($timestamp);
	}



	private function yearConvert($gregorian, $type) {
		// get timestamp (ms) for Jan 1 of the Gregorian year
		$gCal = \IntlCalendar::createInstance(null, 'gregorian');
		if (version_compare(PHP_VERSION, '8.3.0') >= 0) {
			$gCal->setDateTime($gregorian, 0, 1, 0, 0, 0);
		} else {
			$gCal->set($gregorian, 0, 1, 0, 0, 0);
		}
		$gCal->set(\IntlCalendar::FIELD_MILLISECOND, 0);
		$ts_ms = $gCal->getTime();

		// create an Islamic calendar and read which Islamic year corresponds to that timestamp
		$hCal = \IntlCalendar::createInstance(null, $type);
		$hCal->setTime($ts_ms);
		return $hCal->get(\IntlCalendar::FIELD_YEAR); // Hijri
	}

	const persianMonths = [
		'Farvardin'   => 0,
		'Ordibehesht' => 1,
		'Khordad'     => 2,
		'Tir'         => 3,
		'Mordad'      => 4,
		'Shahrivar'   => 5,
		'Mehr'        => 6,
		'Aban'        => 7,
		'Azar'        => 8,
		'Dey'         => 9,
		'Bahman'      => 10,
		'Esfand'      => 11,
	];
	const hebrewMonths = [
		 'Nisan'   => 1,
		 'Iyyar'   => 2,
		 'Sivan'   => 3,
		 'Tamuz'   => 4,
		 'Av'      => 5,
		 'Elul'    => 6,
		 'Tishrei' => 7,
		 'Cheshvan'=> 8,
		 'Kislev'  => 9,
		 'Tevet'   => 10,
		 'Shvat'   => 11,
		 'Adar'    => 12, // Adar I/II complication ignored
	];
	const islamicMonths = [
		'Muharram'      => 0,
		'Safar'         => 1,
		'Rabi al-awwal' => 2,
		'Rabi al-thani' => 3,
		'Jumada al-awwal'=> 4,
		'Jumada al-thani'=> 5,
		'Rajab'         => 6,
		'Shaban'        => 7,
		'Ramadan'       => 8,
		'Shawwal'       => 9,
		'Dhu al-Qidah'  => 10,
		'Dhu al-Hijjah' => 11,
	];

	private function parseCondition(&$rule) {
		$day = array_shift($rule);
		array_shift($rule); // shift the word 'then' from the rule
		$nextPrev = array_shift($rule); // next|previous
		$weekday = array_shift($rule);
		if(strtolower($this->start->format('l')) == strtolower($day)) {
			$this->start->modify($nextPrev. ' '.$weekday);
		} else if ($this->substitutes) {
			$this->start = null; // only show when IF matches
		}
	}

	private function parseCount($count, $rule) {
		$weekday = array_shift($rule);
		$before = array_shift($rule)=='before'; // before|after|in
		if(!empty($rule)) {
			$this->parseDateRule($rule); // endlessly chainable
		}
		for($i=0;$i<intval($count); $i++) {
			$this->start->modify(($before?'previous':'next'). ' '.$weekday);
		}
	}

	private function parseSince(&$rule) {
		$yearSince = array_shift($rule);
		if($this->start->format('Y') < $yearSince) {
			$this->start = null; // dont yield
		}

		if(@$rule[0]==='and') {
			array_shift($rule);
			if(array_shift($rule) == 'prior'){
				if(array_shift($rule) == 'to') {
					$yearPrior = array_shift($rule);
					if($this->start->format('Y') > $yearPrior) {
						$this->start = null; // dont yield
					}
				}
			}
		}
	}
}