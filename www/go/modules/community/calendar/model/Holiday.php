<?php
namespace go\modules\community\calendar\model;
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
			$this->title = $data->name->{self::$lang};
		}
		if(isset($data->type)) {
			$this->type = $data->type;
		}
		if(!$this->parseDateRule(explode(' ', $rule))){
			throw new \Exception('unparsable rule '.$rule);
		}
	}


	static function generate($set, $lang, $from, $till) {
		$dir = __DIR__.'/../holidays/';
		$year = $from->format('Y');
		self::$lang = $lang;
		self::$names = \json_decode(file_get_contents($dir.'names.json'))->names;
		$file = $dir.'countries/'.strtolower($set).'.json';
		$data = \json_decode(file_get_contents($file));

		if(!$data || !$data->holidays || !$data->holidays->$set || !is_object($data->holidays->$set->days))
			throw new \Exception('error processing file '.$file);

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
		if($month = array_search($monthDay, self::$months)) {
			$monthDay = str_pad($month, 2, '0', STR_PAD_LEFT).'-01';
		}
		$this->start = new \DateTime($this->year.'-'.$monthDay);
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
		if($this->start->format('l') == $day) {
			$this->start->modify($nextPrev. ' '.$weekday);
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