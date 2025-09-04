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

		if(isset($data->type)) {
			$this->type = $data->type;
		}
		if(!$this->parseDateRule(explode(' ', $rule))){
			throw new \Exception('unparsable rule '.$rule);
		}
		if($this->substitutes) {
			$this->title ='(*)'.$this->title;
		}
	}

	static private function findTitle($data) {
		if(isset($data->_name)) {
			if(!isset(self::$names->{$data->_name}))
				return null;
			$lang = self::$names->{$data->_name}->name;
		} else if(isset($data->name)) {
			$lang = $data->name;
		} else {
			return null;
		}
		if(isset($lang->{self::$lang}))
			return $lang->{self::$lang};
		$la = substr(self::$lang,0,2);
		if(isset($lang->{$la}))
			return $lang->{$la};
		if(isset($lang->en))
			return $lang->en;

		return null;
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
				foreach($obj->days as $rule => $entry) {
					if($entry === false) {
						continue; // we do not remove substitutes for regions.
					}
					$holiday = new self($rule, $entry, $year);
					//$holiday->title .= ' ('.self::findRegionName($obj).')';
					$holiday->region = self::findRegionName($obj);
					if($holiday->start !== null && $holiday->start >= $from && $holiday->start <= $till) {
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
		if(preg_match('/\d{2}-\d{2}/', $part) || in_array($part, self::$months)) {
			$this->parseFixed($part);
		} else if($part === 'easter') {
			$this->parseEaster(array_shift($rule));
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
		if (@$rule[0] === 'since') {
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

	private function parseEaster($nb) {
		$ed = easter_days($this->year);
		$ed += intval($nb);
		$this->start = new \DateTime($this->year.'-03-21');
		$this->start->modify(($ed>=0?'+':'').$ed. ' days');
	}

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