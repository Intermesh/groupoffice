<?php

namespace go\core;

use Exception;
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
 * @example Print billing address if available, else print first.
 * [if {{contact.emailAddresses | filter:type:"billing" | count}} > 0]
 *  {{contact.emailAddresses | filter:type:"billing"}}
 * [else]
 *  {{contact.emailAddresses}}
 * [/if]
 * 
 * @example or by index
 * {{contact.emailAddresses[0].email}} 
 * 
 * ::::::Maybe?::::::
 * 
 * {{contact.emailAddresses[type=billing] ?? contact.emailAddresses ?? "-"}}
 *  
 * ``````````````````
 
 */
class TemplateParser {	

	private $models = [];

	public $encloseVars = false;

	public $enableBlocks = true;
	
	public function __construct() {
		$this->addFilter('date', [$this, "filterDate"]);		
		$this->addFilter('number', [$this, "filterNumber"]);
		$this->addFilter('filter', [$this, "filterFilter"]);
		$this->addFilter('count', [$this, "filterCount"]);
		
		$this->addModel('now', new DateTime());	
	}

	private $user;

	protected function getUser() {
		if(!isset($this->user)) {
			$this->user = go()->getAuthState()->getUser(['dateFormat', 'timezone']);	
		}
		return $this->user;
	}
	
	private function filterDate(DateTime $date = null, $format = null) {

		if(!isset($date)) {
			return "";
		}

		if(!isset($format)) {
			$format = $this->getUser()->dateFormat;
		}

		$date->setTimezone(new \DateTimeZone($this->getUser()->timezone));

		return $date->format($format);
	}
	
	private function filterNumber($number,$decimals=2, $decimalSeparator='.', $thousandsSeparator=',') {
		return number_format($number,$decimals, $decimalSeparator, $thousandsSeparator);
	}
	
	private function filterFilter($array, $propName, $propValue) {
		
		$filtered = array_filter($array, function($i) use($propValue, $propName){
			return $i->$propName == $propValue;
		});

		return $filtered;
	}

	private function filterCount($countable) {
		return count($countable);
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
			
		preg_match_all('/\n?\[(each|if)/s', $str, $openMatches, PREG_OFFSET_CAPTURE|PREG_SET_ORDER);
		preg_match_all('/\[\/(each|if)\]\n?/s', $str, $closeMatches, PREG_OFFSET_CAPTURE|PREG_SET_ORDER);
		preg_match_all('/\[else\]/s', $str, $elseMatches, PREG_OFFSET_CAPTURE|PREG_SET_ORDER);
		
		$count = count($openMatches);
		if($count != count($closeMatches)) {
			throw new Exception("Open and close tags don't match");
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
		
		//close and open 
		ksort($tags);
		$tags = array_values($tags);		
	
		$tags = $this->findCloseTags($tags);				
	
		$tags = array_values(array_filter($tags, function($tag) {
			return $tag['type'] == 'open' && isset($tag['close']);
		}));
	
		foreach($elseMatches as $elseMatch) {
			$tags = $this->matchElseTag($tags, $elseMatch);
		}

		//only parse top level blocks because other tags will be parsed separately.
		$tags = array_filter($tags, function($tag) {
			return $tag['nesting'] == 0;
		});

		//Make sure index is reset after filtering
		$tags = array_values($tags);
		
		for($i = 0, $c = count($tags); $i < $c; $i++) {
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
			if($tags[$i]['type'] == 'open') {
				$opened[$open] = &$tags[$i];
				$opened[$open]['nesting'] = $open;
				$open++;										
			} else {
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
		$str = preg_replace_callback('/{{.*?}}/', [$this, 'replaceVar'], $str);	
		return $str;
	}	

	private function parseBlocks($str) {
		$tags = $this->findBlocks($str);		
			
		for($i = 0;$i < count($tags); $i++) {
			if($tags[$i]['tagName'] == 'if') {
				$tags[$i] = $this->replaceIf($tags[$i], $str);
			} else
			{
				$tags[$i] = $this->replaceEach($tags[$i], $str);
			}
		}
		
		$replaced = "";
		$offset = 0;
		foreach($tags as $tag) {
			
			if($tag['offset'] > 0) {
				$cut = substr($str, $offset, $tag['offset'] - $offset);
				$replaced .= $cut;
			}

			$replaced .=  $tag['replacement'];
			$offset = $tag['close']['offset'] + $tag['close']['tagLength'];
		}
		
		$replaced .= substr($str, $offset);

		return $replaced;
	}
	
	private function replaceEach($tag, $str) {
		
		//example emailAddress in contact.emailAddresses
		$expressionParts = array_map('trim', explode(' in ', $tag['expression']));

		$array = $this->getVarFiltered($expressionParts[1]);	
		
		if(!is_array($array) && !($array instanceof Traversable)) {
			$tag['replacement'] = "";
			return $tag;
		}
		
		$varName = trim($expressionParts[0]);		
		
		$replacement = '';
		$eachIndex = 0;
		$parser = clone $this;		
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
		} catch(\ParseError $e) {
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
	
	
	private function isVar($path) {
		$pathParts = explode(".", trim($path)); //eg "contact.name"		

		$model = $this;

		foreach ($pathParts as $pathPart) {
			if(is_array($model)) {
				if(!array_key_exists($pathPart, $model)) {
					return false;
				}
				$model = $model[$pathPart];
			}else
			{
				if(!$model->hasReadableProperty($pathPart)) {
					return false;
				}
				$model = $model->$pathPart;
			}			
		}

		return true;
	}
	
	private function getVar($path) {
		
		// var_dump('getVar('.trim($path).')');
		$pathParts = explode(".", trim($path)); //eg "contact.name"		

		$model = $this;

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
					return null;
				}
				$model = $model->$pathPart;
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
	 * @param array|stdClass $model 
	 * @return TemplateParser
	 */
	public function addModel($name, $model) {
		$this->models[$name] = $model;

		return $this;
	}

	public function __isset($name) {
		return isset($this->models[$name]);
	}

	public function __get($name) {
		return $this->models[$name];
	}
}
