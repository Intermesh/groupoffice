<?php

namespace go\core;

use Exception;
use go\core\data\Model;
use go\core\orm\EntityType;
use go\core\db\Query;
use go\core\db\Statement;
use go\core\orm\Entity;
use go\core\util\DateTime;
use stdClass;
use Traversable;
use function GO;

/**
 * Template parser
 * 
 * By default two variable are already present:
 * 
 * {{now|date:Y-m-d}} The current date time object. In this example a date filter is used.
 * 
 * @example
 * 
 * ``````````````````````````````````````````````````````````````````````
 * 
 * $body = new \go\core\TemplateParser();
 * $body->addModel('user', go()->getAuthState()->getUser());
 * 		
 * echo $body->parse("Hello {{user.username}}, It's {{now | date:l}} today");
 * 
 * ````````````````````````````````````````````````````````````````````````
 * Outputs: Hello admin, It's Tuesday today.
 * 
 * 
 * More complex example with if and each
 * 
 * `````````````````````````````````````````````````````````````````````````````
 * $tpl = 'Hi {{user.username}},'
 *						. '[if {{test.foo}}]'."\n"
 *						. 'Your e-mail [if {{now|date:Y}} == 2018] is [/if] {{user.email}}'."\n"
 *						. '[else]'
 *            . 'Hello'
 *						. '[/if]'
 *						. ''
 *						. '[each emailAddress in user.contact.emailAddresses]'
 *						. '{{emailAddress.email}} type: {{emailAddress.type}}'."\n"
 *						. "[/each]";
 *		
 *		$tplParser = new \go\core\TemplateParser();
 *		$tplParser->addModel('test', ['foo' => 'bar'])
 *						->addModel('user', go()->getAuth()->user());
 *		
 *		echo $tplParser->parse($tpl);
 *``````````````````````````````````````````````````````````````````````````````		
 * 
 * 
 * @example More complex if statement
 * `````````````````````````````````````````````````````````````````````````````
 * {{contact.name}}
 * 
 * [each address in contact.addresses]
 *   [if {{address.type}} == "billing"]
 *     {{address.formatted}}
 *   [/if]
 * [/each]
 * ````````````````````````````````````````````````````````````````````````````
 * 
 * @example iterate through filtered array and only write first match using "eachIndex"
 * ````````````````````````````````````````````````````````````````````````````
 * [each emailAddress in contact.emailAddresses | filter:type:"billing"]
 *   [if eachIndex == 1]
 *     {{emailAddress.email}}
 *   [/if]
 * [/each]
 *
 * @example Implode all e-mail
 *
 * {{contact.emailAddresses | column:email | implode}}
 *
 * @example Print billing address if available, else print first.
 * [if {{contact.emailAddresses | filter:type:"billing" | count}} > 0]
 *  {{contact.emailAddresses | filter:type:"billing"}}
 * [else]
 *  {{contact.emailAddresses | first}}
 * [/if]
 * 
 * @example or by index
 * {{contact.emailAddresses[0].email}} 
 * 
 * @example Using [assign] to create a new variable.
 * `````````````````````````````````````````````````````````````````````
 * {{contact.name}}
 * [assign address = contact.addresses | filter:type:"postal" | first]
 * [if !{{address}}]
 * [assign address = contact.addresses | first]
 * [/if]
 * {{address.formatted}}
 * `````````````````````````````````````````````````````````````````````
 *
 * @example Using [assign] to lookup a Contact entity with id = 1
 *
 * ```

 * [assign contact = 1 | entity:Contact]
 * ```
 *
 * @example Using [assign] to lookup a Contact entity with id = 1
 *
 * ```
 * [assign contact = 1 | entity:Contact]
 * ```
 *
 * @example Using [assign] to lookup a linked Contact entity with id = 1
 *
 * ```
 * [assign firstContactLink = someEntityVar | links:Contact | first]
 *
 * {{firstContactLink.name}}
 * ```
 *
 * @example Using [assign] to do some basic math
 *
 * Note that inside the [each] block we access total with parent.total
 *
 * ```
 * [assign total = 0]
 *
 * [each invoice in invoices]
 *  <tr>
 *    <td>{{invoice.number}}</td>
 *    <td>{{invoice.date|date:d-m-Y}}</td>
 *    <td>{{invoice.expiresAt|date:d-m-Y}}</td>
 *    <td align="right">{{business.finance.currency}} {{invoice.totalPrice|number}}</td>
 *    <td align="right">{{business.finance.currency}} {{invoice.paidAmount|number}}</td>
 *    [assign balance = {{invoice.totalPrice}} - {{invoice.paidAmount}} ]
 *    [assign parent.total = {{parent.total}} + {{balance}}]
 *    <td align="right">{{business.finance.currency}} {{balance|number}}</td>
 *  </tr>
 * [/each]
 *
 * {{business.finance.currency}} {{total|number}}
 * </tr>
 * ````
 *
 */
class TemplateParser {	

	private $models = [];

	/**
	 * Values in IF expressions will be enlosed with quotes.
	 *
	 * @var bool
	 */
	public $encloseVars = false;

	public $enableBlocks = true;
	
	public function __construct() {
		$this->addFilter('date', [$this, "filterDate"]);		
		$this->addFilter('number', [$this, "filterNumber"]);
		$this->addFilter('filter', [$this, "filterFilter"]);
		$this->addFilter('count', [$this, "filterCount"]);
		$this->addFilter('first', [$this, "filterFirst"]);
		$this->addFilter('column', [$this, "filterColumn"]);
		$this->addFilter('implode', [$this, "filterImplode"]);
		$this->addFilter('entity', [$this, "filterEntity"]);
		$this->addFilter('links', [$this, "filterLinks"]);
		$this->addFilter('nl2br', "nl2br");
		$this->addFilter('t', [$this, "filterTranslate"]);

		$this->addModel('now', new DateTime());	
	}

	private $_currentUser;

	protected function _currentUser() {
		if(!isset($this->_currentUser)) {
			$this->_currentUser = go()->getAuthState()->getUser(['dateFormat', 'timezone' ]);
		}
		return $this->_currentUser;
	}

	private function filterTranslate($text, $package = 'core', $module = 'core') {
		return go()->t($text, $package, $module);
	}
	
	private function filterDate(DateTime $date = null, $format = null) {

		if(!isset($date)) {
			return "";
		}

		if(!isset($format)) {
			$format = $this->_currentUser()->dateFormat;
		}

		$date->setTimezone(new \DateTimeZone($this->_currentUser()->timezone));

		return $date->format($format);
	}

	private function filterEntity($id, $entityName) {
		$et = EntityType::findByName($entityName);
		if(!$et) {
			return null;
		}
		$cls = $et->getClassName();

		$e = $cls::findById($id);

		return $e;
	}

	private function filterLinks(Entity $entity, $entityName) {

		$entityType = EntityType::findByName($entityName);
		$entityCls = $entityType->getClassName();
		$entities = $entityCls::findByLink($entity,[], true);

		return $entities;
	}

	private function filterNumber($number,$decimals=2, $decimalSeparator='.', $thousandsSeparator=',') {
		return number_format($number,$decimals, $decimalSeparator, $thousandsSeparator);
	}
	
	private function filterFilter($array, $propName, $propValue) {

		if(!isset($array)) {
			return null;
		}

		$filtered = array_filter($array, function($i) use($propValue, $propName){
			return $i->$propName == $propValue;
		});

		return $filtered;
	}

	private function filterColumn($array, $propName) {

		if(!isset($array)) {
			return null;
		}

		$c = [];

		foreach($array as $item) {
			$c[] = $this->getVar($propName, $item);
		}

		return $c;
	}

	private function filterImplode($array, $glue = ', ') {

		if(!isset($array)) {
			return "";
		}

		return implode($glue, $array);
	}



	private function filterCount($countable) {
		if(!isset($countable)) {
			return 0;
		}
		return count($countable);
	}

	private function filterFirst($items) {

		if(!isset($items)) {
			return null;
		}

		if(is_array($items)) {
			return reset($items);
		}

		if($items instanceof Query) {
			return $items->single();
		}

		throw new \Exception("Unsupported type for filter 'first'");


	}
	
	/**
	 * Add a filter function
	 * 
	 * @param string $name
	 * @param callable $function
	 */
	public function addFilter($name, callable $function) {
		$this->filters[$name] = $function;
	}
	
	private function findBlocks($str) {
			
		preg_match_all('/\[(each|if)/s', $str, $openMatches, PREG_OFFSET_CAPTURE|PREG_SET_ORDER);
		preg_match_all('/\[\/(each|if)\]/s', $str, $closeMatches, PREG_OFFSET_CAPTURE|PREG_SET_ORDER);
		preg_match_all('/\[else\]/s', $str, $elseMatches, PREG_OFFSET_CAPTURE|PREG_SET_ORDER);
		preg_match_all('/\\[assign\s+([a-z0-9A-Z-_\.]+)\s*=\s*(.*?)(?<!\\\\)\\]/', $str, $assignMatches, PREG_OFFSET_CAPTURE|PREG_SET_ORDER);
		
		$count = count($openMatches);
		if($count != count($closeMatches)) {
			throw new Exception("Invalid template open and close tags of [if] and/or [each] don't match");
		}
		
		$tags = [];		
		
		for($i = 0; $i < $count; $i++) {
			$offset = $openMatches[$i][0][1];
			$expression = $this->findExpression($offset + strlen($openMatches[$i][0][0]), $str);

			$tags[$offset] = [
				'tagName' => $openMatches[$i][1][0], 
				'type' => 'open', 
				'offset' => $offset, 
				'expression' => $expression, 
				'tagLength' => strlen($openMatches[$i][0][0]) + strlen($expression) + 1];
		}
		
		
		for($i = 0; $i < $count; $i++) {			
			$offset = $closeMatches[$i][0][1];
			$tags[$offset] = ['tagName' => $closeMatches[$i][1][0], 'type' => 'close', 'offset' => $offset, 'tagLength' => strlen($closeMatches[$i][0][0])];			
		}

		foreach($assignMatches as $a) {
			$offset = $a[0][1];
			$tag = ['tagName' => 'assign', 'type'=> null, 'offset' => $offset, 'tagLength' => strlen($a[0][0]), 'expression' => $a[2][0], 'varName' => $a[1][0]];
			$tag['close'] = $tag;
			$tags[$offset] = $tag;
		}

		//sort by offset
		ksort($tags);

		//reindex
		$tags = array_values($tags);

		$tags = $this->findCloseTags($tags);				
	
		$tags = array_values(array_filter($tags, function($tag) {
			return $tag['type'] == null || ($tag['type'] == 'open' && isset($tag['close']));
		}));

		foreach($elseMatches as $elseMatch) {
			$tags = $this->matchElseTag($tags, $elseMatch);
		}

		//only parse top level blocks because other tags will be parsed separately.
		$tags = array_values(array_filter($tags, function($tag) {
			return $tag['nesting'] == 0 && $tag['type'] != 'close';
		}));

		for($i = 0, $c = count($tags); $i < $c; $i++) {

			if($tags[$i]['tagName'] != 'if' && $tags[$i]['tagName'] != 'each') {
				continue;
			}

			$start = $tags[$i]['offset'] + $tags[$i]['tagLength'];
			
			if(!isset($tags[$i]['elseOffset']) ) {
				$length = $tags[$i]['close']['offset'] - $start;
				$tags[$i]['tpl'] = substr($str, $start, $length); 
				$tags[$i]['else'] = null;
			} else{
				$length = $tags[$i]['elseOffset'] - $start;
		
				$elseStart = $start + $length + 6;
				$elseLength = $tags[$i]['close']['offset'] - $elseStart;
				 
				$tags[$i]['tpl'] = substr($str, $start, $length); 
				$tags[$i]['else'] = substr($str, $elseStart, $elseLength);
			}		
		}
		
		return $tags;
	}

	private function matchElseTag($tags, $elseMatch) {
		for($i = count($tags) - 1; $i >= 0; $i--) {

			$offset = $elseMatch[0][1];

			if($offset > $tags[$i]['offset'] && $offset < $tags[$i]['close']['offset']) {
				$tags[$i]['elseOffset'] = $offset;
				return $tags;
			}
		}

		return $tags;
	}

	private function findExpression($startPos, $str) {
		$openBrackets = 1;

		$expression = '';
		$max = strlen($str);
		for($i = $startPos; $i < $max; $i++) {
			$char = $str[$i];

			switch($char) {
				case '[':
					$openBrackets++;
					$expression .= $char;
				break;

				case ']':
					$openBrackets--;
					if($openBrackets == 0) {
						return $expression;
					} else {
						$expression .= $char;
					}
					break;

				default:
					$expression .= $char;
			}
			
		}

		throw new \Exception("Invalid block");

	}
	
	private function findCloseTags($tags) {
		
		$open = 0;
		$opened = [];
		
		for($i = 0, $count = count($tags); $i < $count; $i++) {
			$tags[$i]['nesting'] = $open;

			if($tags[$i]['type'] == 'open') {
				$opened[$open] = &$tags[$i];
				$open++;										
			} elseif($tags[$i]['type'] == 'close') {
				$open--;	
				$opened[$open]['close'] = $tags[$i];
			}
		}
		
		return $tags;
	}
	/**
	 * Parse a template
	 * 
	 * @see __construct();
	 * 
	 * @param string $str
	 * 
	 * return string
	 */
	public function parse($str) {		
		if($this->enableBlocks) {
			$str = $this->parseBlocks($str);
		}
//		$str = preg_replace_callback('/\n?\\[assign\s+([a-z0-9A-Z-_]+)\s*=\s*(.*)(?<!\\\\)\\]\n?/', [$this, 'replaceAssign'], $str);
		$str = preg_replace_callback('/{{.*?}}/', [$this, 'replaceVar'], $str);	
		return $str;
	}



	private function parseBlocks($str) {
		$tags = $this->findBlocks($str);		
			
		for($i = 0;$i < count($tags); $i++) {
			switch($tags[$i]['tagName']){
				case  'if':
				$tags[$i] = $this->replaceIf($tags[$i], $str);
				break;

				case 'each':
					$tags[$i] = $this->replaceEach($tags[$i], $str);
					break;

				case 'assign':
					$tags[$i] = $this->replaceAssign($tags[$i], $str);
					break;
			}
		}
		
		$replaced = "";
		$offset = 0;
		foreach($tags as $tag) {
			
			if($tag['offset'] > 0) {
				$cut = substr($str, $offset, $tag['offset'] - $offset);

				//trim single new lines
				$cut = preg_replace("/(.*)\r?\n?$/", "$1", $cut);

				$replaced .= $cut;
			}

			$replaced .=  $tag['replacement'];
			$offset = $tag['close']['offset'] + $tag['close']['tagLength'];
		}
		
		$replaced .= substr($str, $offset);

		$replaced = preg_replace("/(.*)\r?\n?$/", "$1", $replaced);
		return $replaced;
	}

	private function replaceAssign($tag, $str) {

		//assign won't output
		$tag['replacement'] = "";

		if(is_numeric($tag['expression'])) {
			//allow assigning a new numeric value for math operations
			$value = $tag['expression'];
		} else 	if(preg_match('/{{.*?}}/',$tag['expression'])) {
			$sum = $this->parse($tag['expression']);

			try{
				$sum = $this->validateExpression($sum);
				$value = eval($sum);
			} catch(\Throwable $e) {
				$value = $e->getMessage();
			}

		} else {
			$value = $this->getVarFiltered($tag['expression']);
		}

		$o = &$this->models;

		$path = explode(".", $tag['varName']);

		$lastPart = array_pop($path);

		foreach($path as $part) {
			if(is_array($o)) {
				if (!isset($o[$part])) {
					//ignore invalid assign
					return $tag;
				}
				$o = &$o[$part];
			} else{
				if (!isset($o->$part)) {
					//ignore invalid assign
					return $tag;
				}
				$o = &$o->$part;
			}
		}

		if(is_array($o)) {
			$o[$lastPart] = $value;
		} else if(is_object($o)) {
			$o->$lastPart = $value;
		}



		return $tag;
	}



	private function replaceEach($tag, $str) {
		
		//example emailAddress in contact.emailAddresses
		$expressionParts = array_map('trim', explode(' in ', $tag['expression']));

		if(!isset($expressionParts[1])) {
			throw new \Exception("Invalid expression: ". $tag['expression']);
		}

		$array = $this->getVarFiltered($expressionParts[1]);
		
		if(!is_array($array) && !($array instanceof Traversable)) {
			$tag['replacement'] = "";
			return $tag;
		}
		
		$varName = trim($expressionParts[0]);		
		
		$replacement = '';
		$eachIndex = 0;
		$parser = clone $this;
		$parser->addModel("parent", $this);
		foreach($array as $model) {
			
			$parser->addModel($varName, $model);		
			$parser->addModel("eachIndex", $eachIndex++);		
			
			$add = $parser->parse($tag['tpl']);
			$replacement .= $add;
		}
		
		$tag['replacement'] = $replacement;		
		return $tag;		
	}

	private static $tokens = ['==','!=','>','<', '(', ')', '&&', '||', '*', '/', '%', '-', '+', '!'];

	private function replaceIf($tag, $str) {
		
		$this->encloseVars = true;
		$this->enableBlocks = false;
		$parsed = $this->parse($tag['expression']);		
		$this->encloseVars = false;
		$this->enableBlocks = true;
		
		$expression = $this->validateExpression($parsed);	
		try {
			$ret = eval($expression);	
		} catch(\Throwable $e) {
			go()->warn('eval() failed '. $e->getMessage());
			go()->warn($tag['expression']);
			go()->warn($expression);
			$ret = false;
		}
		if($ret){
			$tag['replacement'] = $this->parse($tag['tpl']);
		}else
		{
			$tag['replacement'] = isset($tag['else']) ? $this->parse($tag['else']) : "";
		}
		
		return $tag;

		
//		return substr($str, 0, $block['offset']) . $replacement . substr($str, $block['close']['offset'] + $block['close']['tagLength']);
	}
	
	private function validateExpression($expression) {
		
		$expression = html_entity_decode($expression);

		//split string into tokens. See http://stackoverflow.com/questions/5475312/explode-string-into-tokens-keeping-quoted-substr-intact		
		foreach(self::$tokens as $token) {			
			$expression = str_replace($token, ' '.$token.' ', $expression);
		}
		$expression = str_replace(';', ' ; ', $expression);
		
		$parts = preg_split('#\s*((?<!\\\\)"[^"]*")\s*|\s+#', $expression, -1 , PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);		
		$parts = array_map('trim', $parts);
		
		$str = '';
		
		foreach($parts as $part) {
			
			if($part == ';') {
				throw new Exception('; not allowed in expression: ' . $expression);
			}
			
			if(
							empty($part) ||
							is_numeric($part) ||
							$part == 'true' ||
							$part == 'false' ||
							$part == 'null' ||
							in_array($part, self::$tokens) ||
							$this->isString($part)											
				) {
				$str .= $part.' ';
			}else
			{
				$str .= '"'. str_replace('"', '\"', $part) . '" ';
			}			
		}		
		return empty($str) ? 'return false;' : 'return ('.$str.');';
	} 
	
	private function isString ($str) {
		return preg_match('/"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"/s', $str) || preg_match("/'[^'\\\\]*(?:\\\\.[^'\\\\]*)*'/s", $str);
	}

	private function replaceVar($matches) {
		//take off {{ .. }}
		$str = substr($matches[0], 2, -2);

		$value =  $this->getVarFiltered($str);

		//If replace value is array use first value for convenience
		if(is_array($value)) {
			return array_shift($value);
		}

		if($this->encloseVars) {		
			$value = is_scalar($value) || 
				!isset($value) || 
				(is_object($value) && method_exists($value, '__toString')) ? '"' . str_replace('"', '\\"', $value) . '"' : !empty($value);
		}

		return $value;
	}


//	private function replaceVar($matches) {
//
//		//take off {{ .. }}
//		$str = substr($matches[0], 2, -2);
//
//		//split for allowing simple calculations
//		$parts = preg_split('/([+\-\/\*])/', $str, -1, PREG_SPLIT_DELIM_CAPTURE);
//
//		$math = "";
//
//		$mathExpression = count($parts) > 1;
//
//		while($part = array_shift($parts)) {
//
//			$varName = trim($part);
//			$operator = array_shift($parts);
//
//			$value = $this->getVarFiltered($varName);
//
//			//If replace value is array use first value for convenience
//			if (is_array($value)) {
//				$value = array_shift($value);
//			}
//
//			if ($this->encloseVars) {
//				$value = is_scalar($value) ||
//				!isset($value) ||
//				(is_object($value) && method_exists($value, '__toString')) ? '"' . str_replace('"', '\\"', $value) . '"' : !empty($value);
//			}
//
//			if(!$mathExpression) {
//				return $value;
//			}
//
//			$math .= $value . $operator;
//		}
//
//
//		try {
//			return eval($math);
//		}catch(\Throwable $e) {
//			return $e->getMessage();
//		}
//
//	}

	private function getVarFiltered($expression) {
		$filters = explode('|', $expression);
		
		$varPath = trim(array_shift($filters)); //eg "contact.name";		
		
		$value = $this->getVar($varPath);		
		foreach($filters as $filter) {
			
			$args = array_map('trim', str_getcsv($filter, ':', '"'));
			$filterName = array_shift($args);
			array_unshift($args, $value);

			if(!isset($this->filters[$filterName])) {
				throw new \Exception("Filter '" . $filterName . "' is undefined");
			}
			$value = call_user_func_array($this->filters[$filterName], $args);

		}
		
		return $value;
	}
	
	private $filters = [];
	

	
	private function getVar($path, $model = null) {
		
		// var_dump('getVar('.trim($path).')');
		$pathParts = explode(".", trim($path)); //eg "contact.name"		

		if(!isset($model)) {
			$model = $this;
		}

		foreach ($pathParts as $pathPart) {
			//check for array access eg. contact.emailAddresses[0];
			if(preg_match('/(.*)\[([0-9]+)\]/', $pathPart, $matches)) {

				// var_dump($matches);
				
				$index = (int) $matches[2];
				$pathPart = $matches[1];
			} else{
				$index = null;
			}

			if(is_array($model)) {
				if (!isset($model[$pathPart])) {
					return null;
				}
				$model = $model[$pathPart];
			}else 
			{				
				if (!isset($model->$pathPart)) {

					$getter = 'get' . $pathPart;

					if(method_exists($model, $getter)) {
						$model = $model->$getter();
					} else{
						return null;
					}
				}else{
					$model = $model->$pathPart;
				}

			}
			
			if(isset($index)) {
				if(!isset($model[$index])) {
					return null;
				}
				$model = $model[$index];
				
			}
		}

		return $model;
	}	
	
	public function hasReadableProperty($name) {
		return array_key_exists($name, $this->models);
	}

	/**
	 * Add a key value array or object to add for the parser.
	 * 
	 * @param string $name
	 * @param array|Model|stdClass $model
	 * @return TemplateParser
	 */
	public function addModel($name, $model) {
		$this->models[$name] = $model;

		return $this;
	}

	public function __set($name, $value) {
		$this->addModel($name, $value);
	}

	public function __isset($name) {
		return isset($this->models[$name]);
	}

	public function &__get($name) {
		return $this->models[$name];
	}
}
