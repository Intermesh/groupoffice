<?php
namespace go\modules\community\calendar\model;
use go\core\ErrorHandler;

class Holiday {

	const Public = 'public'; // public holiday
	const Bank='bank'; // banks and offices are closed
	const School='school'; // school holiday, schools are closed
	const Optional = 'optional'; // majority people take a day off
	const Observance = 'observance'; // optional festivity, no paid day off

	private static $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
	private static $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

	static $lang;
	static $names;

	private $year;

	public $title;

	public $start;

	private $substitutes;

	public $type = 'public';

	public $duration = 'P1D';

	public function __construct(string $rule, $data,$year) {
		$this->year = $year;

		if(isset($data->_name)) {
			$lang = self::$names->{$data->_name}->name;
			if(isset($lang->{self::$lang}))
				$this->title = $lang->{self::$lang};
			else if(isset($lang->en))
				$this->title = $lang->en;
			else
				$this->title = var_export($data->_name,true);
		} else if($data->name) {
			if(!isset($data->name->{self::$lang})) {
				self::$lang = substr(self::$lang,0,2);
				if(!isset($data->name->{self::$lang})) {
					self::$lang = 'en';
				}
			}
			$this->title = $data->name->{self::$lang};

		}
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

		foreach(self::generateYear($set,$data,$from, $till, $from->format('Y')) as $item) yield $item;
		if($from->format('Y') !== $till->format('Y')) {
			foreach(self::generateYear($set,$data,$from, $till, $till->format('Y')) as $item) yield $item;
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

	}

	/**
	 * https://github.com/commenthol/date-holidays-parser/blob/21fd7e2eca3df9ee99c0a221ae739a9c99b34e59/docs/specification.md
	 * @param string[] $str
	 * @param int $year
	 * @return bool
	 */
	private function parseDateRule($rule) {

		$date = array_shift($rule);
		if($date === 'substitutes') {
			$this->substitutes = true;
			$date = array_shift($rule);
		}
		if(preg_match('/\d{2}-\d{2}/', $date) || in_array($date, self::$months)) {
			$this->parseFixed($date);
		} else if($date === 'easter') {
			$this->parseEaster(array_shift($rule));
		} else if(preg_match('/\d+(st|nd|rd|th)/', $date)) {
			$this->parseCount($date, $rule);
		} else if(in_array($date, self::$days)) {
			array_unshift($rule, $date);
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
			$year = max($this->year, intval(substr($monthDay,0,4)));
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
		$this->start->modify('+'.$ed. ' days');
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
		$this->parseFixed(array_shift($rule));
		for($i=0;$i<intval($count); $i++) {
			$this->start->modify(($before?'previous':'next'). ' '.$weekday);
		}
		if(!empty($rule)) {
			$this->parseDateRule($rule); // endlessly chainable
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