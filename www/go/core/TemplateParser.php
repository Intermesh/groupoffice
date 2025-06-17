<?php /** @noinspection PhpSameParameterValueInspection */

namespace go\core;

use DateInterval;
use DateTimeZone;
use Exception;
use go\core\data\Model;
use go\core\fs\Blob;
use go\core\model\User;
use go\core\orm\EntityType;
use go\core\db\Query;
use go\core\orm\Entity;
use go\core\util\DateTime;
use GO\Files\Model\Folder;
use Throwable;
use Traversable;
use function GO;

/**
 * Template parser
 *
 * Replaces variables {{varname}} and parse structural blocks like [if] and [each]
 *
 * Structural blocks may be wrapped with <template>[if ...]</template> so they can be valid html inside wysywig editors
 * 
 * By default some variables are already present:
 * 
 * {{now|date:Y-m-d}} The current date time object. In this example a date filter is used.
 *
 * {{system.title}}
 * {{system.url}}
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
 *
 * @example If with and operator
 * `````````````````````````````````````````````````````````````````````````````
 * [if {{document.customFields.downPayment}} && {{document.totalPrice}} > 4000]
 *  [assign downPayment = document.totalPrice|multiply:0.3]
 *  {{downPaymnent|number}}
 * [/if]
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
 * [if {{address|empty}}]
 * [assign address = contact.addresses | first]
 * [/if]
 * {{address.formatted}}
 * `````````````````````````````````````````````````````````````````````
 *
 * The same with sort:
 * ```
 * [assign formattedAddress = contact.addresses | sort:type:"postal" | first | prop:formatted]
 * {{formattedAddress}}
 * ```
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
	 * Values in IF expressions will returned as "false" or "true"
	 *
	 * @var bool
	 */
	private $varsForIfStatement = false;

	private $enableBlocks = true;


	/**
	 * Configuration which can be altered with:
	 *
	 * ```
	 * [config thousandsSeparator=.]
	 * [config decimalSeparator=,]
	 * [config dateFormat=d-m-Y]
	 * ``
	 * @var string[]
	 */
	public $config = [
		'decimals' => 2,
		'decimalSeparator' => '.',
		'thousandsSeparator' => ',',
		'dateFormat' => 'd-m-Y'
	];
	
	public function __construct() {
		$this->addFilter('date', [$this, "filterDate"]);
		$this->addFilter('dateAdd', [$this, "filterDateAdd"]);
		$this->addFilter('timestamp', [$this, "filterUnixTimestamp"]);
		$this->addFilter('number', [$this, "filterNumber"]);
		$this->addFilter('filter', [$this, "filterFilter"]);
		$this->addFilter('sort', [$this, "filterSort"]);
		$this->addFilter('rsort', [$this, "filterRsort"]);
		$this->addFilter('count', [$this, "filterCount"]);
		$this->addFilter('multiply', [$this, "filterMultiply"]);
		$this->addFilter('add', [$this, "filterAdd"]);
		$this->addFilter('first', [$this, "filterFirst"]);
		$this->addFilter('column', [$this, "filterColumn"]);
		$this->addFilter('implode', [$this, "filterImplode"]);
		$this->addFilter('entity', [$this, "filterEntity"]);
		$this->addFilter('links', [$this, "filterLinks"]);
		$this->addFilter('prop', [$this, "filterProp"]);


		$this->addFilter('entityFiles', [$this, "filterEntityFiles"]);

		$this->addFilter('substr', [$this, "filterSubstr"]);
		$this->addFilter('nl2br', [$this, "filterNl2br"]);
		$this->addFilter('markdown', [$this, "filterMarkdown"]);
		$this->addFilter('empty', [$this, "filterEmpty"]);
		$this->addFilter('htmlEncode', [$this, "filterHtmlEncode"]);
		$this->addFilter('dump', [$this, "filterDump"]);
		$this->addFilter('t', [$this, "filterTranslate"]);
		$this->addFilter('blobUrl', [$this, "filterBlobUrl"]);
		$this->addFilter('blobPath', [$this, "filterBlobPath"]);
		$this->addFilter('newRow', [$this,'filterNewRow']);

		$this->addModel('now', new DateTime());

		$this->addModel('system', [
			"title" => go()->getSettings()->title,
			"url" => go()->getSettings()->URL
		]);
		$this->config = [
			'decimals' => 2,
			'decimalSeparator' => go()->getSettings()->defaultDecimalSeparator,
			'thousandsSeparator' => go()->getSettings()->defaultThousandSeparator,
			'dateFormat' => go()->getSettings()->defaultDateFormat
		];


	}

	private $_currentUser;

	protected function _currentUser(): ?User
	{
		if(!isset($this->_currentUser)) {
			$this->_currentUser = go()->getAuthState()->getUser(['dateFormat', 'timezone' ]);
		}
		return $this->_currentUser;
	}

	private function filterEmpty($v) {
		return empty($v) ? "1" : "0";
	}

	private function filterHtmlEncode($v) {
		return  isset($v) ? htmlspecialchars($v) : "";
	}

	private function filterNl2br(?string $v) {
		return isset($v) ? nl2br($v) : "";
	}

	private function filterBlobUrl(string $blobId): string
	{
		return go()->getAuthState()->getDownloadUrl($blobId);
	}


	private function filterBlobPath(string $blobId): string
	{
		$b = Blob::findById($blobId);
		if($b) {
			return "file://" . $b->getFile()->getPath();
		}
		return "";
	}


	/**
	 * @param Entity $entity
	 * @return void
	 */
	private function filterEntityFiles($entity) {
		if(!$entity->filesFolderId) {
			return [];
		}
		$folder = Folder::model()->findForEntity($entity);
		return array_map(function($file) {
			$blob = $file->getBlob();
			return [
				'blobId' => $blob->id,
				'name' => $blob->name,
				'type' => $blob->type
				];
		}, $folder->files->fetchAll());
	}

	/** @noinspection PhpSameParameterValueInspection */
	private function filterTranslate($text, $package = 'core', $module = 'core') {
		return go()->t($text, $package, $module);
	}

	private function filterDate(DateTime|null$date = null, string|null $format = null): string
	{

		if(!isset($date)) {
			return "";
		}

		if(!isset($format)) {
			$format = $this->config['dateFormat'];
		}

		$date->setTimezone(new DateTimeZone($this->_currentUser()->timezone));

		return $date->format($format);
	}

	private function filterDateAdd(?DateTime $date, string $interval): ?DateTime
	{
		if(!isset($date)) {
			return null;
		}

		$di = new DateInterval($interval);
		return $date->add($di);

	}

	private function filterSubstr(string|null $text, int $start, int|null $length = null): string {
		if(!isset($text)) {
			return "";
		}
		return substr($text, $start, $length);
	}

	/**
	 * @throws Exception
	 */
	private function filterUnixTimestamp(int $ts, $format = null): string
	{
		if(!isset($ts)) {
			return "";
		}

		$dt = DateTime::createFromFormat('U', $ts);
		return $this->filterDate($dt, $format);
	}

	private function filterEntity($id, $entityName, $properties = null) {
		if(empty($id)) {
			return null;
		}
		$et = EntityType::findByName($entityName);
		if(!$et) {
			return null;
		}
		$cls = $et->getClassName();
		// TODO: remove when projects2 is ported
		if($cls === "GO\Projects2\Model\Project") {
			$cls .= "Entity";
		}
		return $cls::findById($id, !empty($properties) ? explode(",", $properties) : []);
	}

	/**
	 * @param Entity|GO\Base\Db\ActiveRecord $entity
	 * @param $entityName
	 * @param $properties
	 * @return mixed
	 * @throws Exception
	 */
	private function filterLinks($entity, $entityName, $properties = null) {

		$entityType = EntityType::findByName($entityName);
		if(!$entityType) {
			throw new Exception("Entity '$entityName' doesn't exist");
		}
		$entityCls = $entityType->getClassName();
		return $entityCls::findByLink($entity,!empty($properties) ? explode(",", $properties) : [], true);
	}

	private function filterMarkDown(?string $text): string
	{
		$pd = new \Parsedown();
		$pd->setSafeMode(true);
		$pd->setBreaksEnabled(true);
		return $pd->text($text);
	}



	private function filterNumber($number,$decimals = null, $decimalSeparator = null, $thousandsSeparator = null): string
	{
		if(!isset($decimals) ){
			$decimals = $this->config['decimals'];
		}

		if(!isset($decimalSeparator) ){
			$decimalSeparator = $this->config['decimalSeparator'];
		}
		if(!isset($thousandsSeparator) ){
			$thousandsSeparator = $this->config['thousandsSeparator'];
		}
		return number_format($number,$decimals, $decimalSeparator, $thousandsSeparator);
	}

	private function filterDump($value): string
	{
		ob_start();
		var_dump($value);
		return ob_get_clean();
	}

	private function filterMultiply($number,$factor): string
	{
		return $number * $factor;
	}

	private function filterAdd($number,$add): string
	{
		return $number + $add;
	}
	
	private function filterFilter($array, $propName, $propValue): ?array
	{

		if(!isset($array)) {
			return null;
		}

		return array_filter($array, function($i) use($propValue, $propName){
			return $i->$propName == $propValue;
		});
	}

	private function filterSort(?array $array, string $propName, $propValue = null): ?array
	{
		if(!isset($array)) {
			return null;
		}

		return $this->internalFilterSort($array, $propName, $propValue, false);

	}


	private function internalFilterSort(array $array, string $propName, $propValue, bool $reverse) : array {

		usort($array, function($a, $b) use ($propValue, $propName, $reverse) {

			$first = $reverse ? $b : $a;
			$second = $reverse ? $a : $b;

			if(isset($propValue)) {
				return ($first->$propName == $propValue) <=> ($second->$propName == $propValue);
			}
			return ($first->$propName <=> $second->$propName);
		});

		return $array;
	}

	private function filterRsort(?array $array, string $propName, $propValue = null): ?array
	{
		if(!isset($array)) {
			return null;
		}

		return $this->internalFilterSort($array, $propName, $propValue, true);
	}

	private function filterColumn($array, $propName): ?array
	{

		if(!isset($array)) {
			return null;
		}

		$c = [];

		foreach($array as $item) {
			$c[] = $this->getVar($propName, $item);
		}

		return $c;
	}

	private function filterImplode($array, $glue = ', '): string
	{

		if(!isset($array)) {
			return "";
		}

		return implode($glue, $array);
	}



	private function filterCount($countable): int
	{
		if(!isset($countable)) {
			return 0;
		}
		return count($countable);
	}

	/**
	 * @throws Exception
	 */
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

		if($items instanceof \GO\Base\Db\ActiveStatement) {
			return $items->fetch();
		}

		throw new Exception("Unsupported type for filter 'first'");


	}

	/**
	 * @throws Exception
	 */
	private function filterProp($entity, $propName) {
		if(is_object($entity)) {
			if(!isset($entity->$propName)) {
				return null;
			}

			return $entity->$propName;
		} else if(is_array($entity)) {
			if(!isset($entity[$propName])) {
				return null;
			}

			return $entity[$propName];
		} else {
			return null;
		}
	}

	/**
	 * Check whether configured number of rows is matched
	 *
	 * @example [if {{eachIndex|newRow:4}} == 0]
	 * @param $idx
	 * @param $numCols
	 * @return string
	 */
	private function filterNewRow($idx, $numCols=2): string
	{
		return $idx % $numCols === 0 ? "1" : "0";
	}
	
	/**
	 * Add a filter function
	 * 
	 * @param string $name
	 * @param callable $function
	 */
	public function addFilter(string $name, callable $function) {
		$this->filters[strtolower($name)] = $function;
	}

	/**
	 * @throws Exception
	 */
	private function findBlocks($str): array
	{
			
		preg_match_all('/\[(each|if)/s', $str, $openMatches, PREG_OFFSET_CAPTURE|PREG_SET_ORDER);
		preg_match_all('/\[\/(each|if)\]/s', $str, $closeMatches, PREG_OFFSET_CAPTURE|PREG_SET_ORDER);
		preg_match_all('/\[else\]/s', $str, $elseMatches, PREG_OFFSET_CAPTURE|PREG_SET_ORDER);
		preg_match_all('/\\[assign\s+([a-z0-9A-Z-_\.]+)\s*=\s*(.*?)(?<!\\\\)\\]\n?/', $str, $assignMatches, PREG_OFFSET_CAPTURE|PREG_SET_ORDER);
		preg_match_all('/\\[config\s+([a-z0-9A-Z-_\.]+)\s*=\s*(.*?)(?<!\\\\)\\]\n?/', $str, $configMatches, PREG_OFFSET_CAPTURE|PREG_SET_ORDER);

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

		foreach($configMatches as $a) {
			$offset = $a[0][1];
			$tag = ['tagName' => 'config', 'type'=> null, 'offset' => $offset, 'tagLength' => strlen($a[0][0]), 'value' => $a[2][0], 'name' => $a[1][0]];
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

	/**
	 * @throws Exception
	 */
	private function findExpression($startPos, $str): string
	{
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

		throw new Exception("Invalid block");

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
	 * @param string $str
	 *
	 * return string
	 * @throws Exception
	 * @throws Exception
	 * @throws Exception
	 * @see __construct();
	 *
	 */
	public function parse(string $str) {
		if($this->enableBlocks) {
			// this will break divs
			$str = preg_replace('/<[^>]+>\[else\]<\/[^>]+>/', '[else]', $str);

			$str = $this->parseBlocks($str);
		}
//		$str = preg_replace_callback('/\n?\\[assign\s+([a-z0-9A-Z-_]+)\s*=\s*(.*)(?<!\\\\)\\]\n?/', [$this, 'replaceAssign'], $str);
		return preg_replace_callback('/{{[^:].*?}}/', [$this, 'replaceVar'], $str);
	}


	/**
	 * @throws Exception
	 */
	private function parseBlocks($str) {

		$str = $this->hideMicrosoftIfs($str);


		$tags = $this->findBlocks($str);		
			
		for($i = 0;$i < count($tags); $i++) {
			switch($tags[$i]['tagName']){
				case  'if':
				$tags[$i] = $this->replaceIf($tags[$i]);
				break;

				case 'each':
					$tags[$i] = $this->replaceEach($tags[$i]);
					break;

				case 'assign':
					$tags[$i] = $this->replaceAssign($tags[$i]);
					break;

				case 'config':
					$tags[$i] = $this->replaceConfig($tags[$i]);
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

		$replaced = $this->returnMicrosoftIfs($replaced);

		// remove empty <template></template>
		$replaced = preg_replace("/<template>[\s]*<\/template>/i", "", $replaced);

		return preg_replace("/(.*)\r?\n?$/", "$1", $replaced);
	}




	/**
	 * @throws Exception
	 */
	private function replaceConfig($tag) {
		//config won't output
		$tag['replacement'] = "";
		$this->config[$tag['name']] = $tag['value'];
		return $tag;
	}

	/**
	 * @throws Exception
	 */
	private function replaceAssign($tag) {

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
			} catch(Throwable $e) {
				$value = $e->getMessage();
			}

		} else {
			$value = $this->getVarFiltered($tag['expression']);
		}

		$path = explode(".", $tag['varName']);
		$this->applyAssignToModels($tag, $path, $value,$this->models);

		if(isset($this->models['parent'])) {
			//apply assigns to parent parser if we are iterating an [each] block
			$this->applyAssignToModels($tag, $path, $value,$this->models['parent']->models);
		}

		return $tag;
	}

	/**
	 * @param $tag The tag we are replacing
	 * @param $path The variable path
	 * @param $value The value to set
	 * @param $models The template models that are available
	 * @return mixed|void
	 */
	private function applyAssignToModels($tag, $path, $value, &$models) {

		$lastPart = array_pop($path);

		foreach($path as $part) {
			if(is_array($models)) {
				if (!isset($models[$part])) {
					//ignore invalid assign
					return $tag;
				}
				$models = &$models[$part];
			} else{
				if (!isset($models->$part)) {
					//ignore invalid assign
					return $tag;
				}
				$models = &$models->$part;
			}
		}

		if(is_array($models)) {
			$models[$lastPart] = $value;
		} else if(is_object($models)) {
			$models->$lastPart = $value;
		}

	}


	/**
	 * @throws Exception
	 */
	private function replaceEach($tag) {
		
		//example emailAddress in contact.emailAddresses
		$expressionParts = array_map('trim', explode(' in ', $tag['expression']));

		if(!isset($expressionParts[1])) {
			throw new Exception("Invalid expression: ". $tag['expression']);
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

	private static $tokens = ['==','!=','>','<', '(', ')', '&&', '||', '*', '/', '%', '-', '+', '!', '?', ':'];

	/**
	 * @throws Exception
	 */
	private function replaceIf($tag) {
		
		$this->varsForIfStatement = true;
		$this->enableBlocks = false;
		$parsed = $this->parse($tag['expression']);		
		$this->varsForIfStatement = false;
		$this->enableBlocks = true;
		
		$expression = $this->validateExpression($parsed);	
		try {
			$ret = eval($expression);
		} catch(Throwable $e) {
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

	/**
	 * @throws Exception
	 */
	private function validateExpression($expression): string
	{
		$expression = html_entity_decode(trim($expression));

		//split string into tokens. See http://stackoverflow.com/questions/5475312/explode-string-into-tokens-keeping-quoted-substr-intact		
		foreach(self::$tokens as $token) {
			if($token == '-' || $token == '!') {
				//skip for negative numbers
				continue;
			}
			$expression = str_replace($token, ' '.$token.' ', $expression);
		}
		$expression = str_replace(';', ' ; ', $expression);
		
		//$parts = preg_split('#\s*((?<!\\\\)"[^"]*")\s*|\s+#', $expression, -1 , PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		$parts = empty($expression) ? [] : str_getcsv($expression,' ','"', "");
		$parts = array_map('trim', $parts);
		
		$str = '';
		
		foreach($parts as $part) {
			
			if($part == ';') {
				throw new Exception('; not allowed in expression: ' . $expression);
			}

			if($part == "") {
				continue;
			}


			if(
							(is_numeric($part) && substr($part, 0, 1) != "0") ||
							$part == 'true' ||
							$part == 'false' ||
							$part == 'null' ||
							in_array($part, self::$tokens)
							//$this->isString($part)
				) {
				$str .= $part.' ';
			}else
			{
				$str .= '"'. addslashes($part) . '" ';
			}			
		}		
		return empty($str) ? 'return false;' : 'return ('.$str.');';
	}

	/**
	 * @throws Exception
	 */
	private function replaceVar($matches) {
		//take off {{ .. }}
		$str = substr($matches[0], 2, -2);

		$value =  $this->getVarFiltered($str);

		//If replace value is array use first value for convenience
		if(is_array($value)) {
			$value = array_shift($value);
		}

		if($this->varsForIfStatement) {
			//$value = empty($value) ? "false" : "true";

			$value = is_scalar($value) ||
			!isset($value) ||
			(is_object($value) && method_exists($value, '__toString')) ? '"' . str_replace('"', '\\"', (string) $value) . '"' : !empty($value);

		}

		return $value;
	}

	/**
	 * @throws Exception
	 */
	private function getVarFiltered($expression) {
		$filters = explode('|', $expression);
		
		$varPath = trim(array_shift($filters)); //eg "contact.name";		
		
		$value = $this->getVar($varPath);		
		foreach($filters as $filter) {
			
			$args = array_map('trim', str_getcsv($filter, ':', '"', ""));
			$filterName = strtolower(array_shift($args));
			array_unshift($args, $value);

			if(!isset($this->filters[$filterName])) {
				throw new Exception("Filter '" . $filterName . "' is undefined");
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
			if(preg_match('/(.*)\[(\d+)]/', $pathPart, $matches)) {
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
			}else if(is_object($model))
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

			} else{
				return null;
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
	
	public function hasReadableProperty($name): bool
	{
		return array_key_exists($name, $this->models);
	}

	/**
	 * Add a key value array or object to add for the parser.
	 * 
	 * @param string $name
	 * @param mixed $model
	 * @return TemplateParser
	 */
	public function addModel(string $name, $model): TemplateParser
	{
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

	/**
	 * Temporarily remove conflicting if statements from microsoft
	 *
	 * eg.
	 * ```
	 * <!--[if (gte mso 9)|(IE)]>
	 *  <div> you fool!</div>
	 * <![endif]-->
	 * ```
	 *
	 * @param string $str
	 * @return string
	 */
	private function hideMicrosoftIfs(string $str): string
	{
		return preg_replace('/<!--\s*\[if/i', '<!--[MSIF', $str);
	}

	private function returnMicrosoftIfs(string $str): string
	{
		return str_replace( '<!--[MSIF', '<!--[if', $str);
	}
}
