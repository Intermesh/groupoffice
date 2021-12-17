<?php

namespace go\core\data;

use Exception;
use go\core\App;
use go\core\util\DateTime;
use InvalidArgumentException;
use JsonSerializable;
use ReflectionClass;
use ReflectionMethod;
use go\core\util\ArrayObject;


/**
 * The abstract model class. 
 * 
 * Models implement validation by default and can be converted into an Array for
 * the API.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
abstract class Model implements ArrayableInterface, JsonSerializable {

	const PROP_PROTECTED = 1;

	const PROP_PUBLIC = 2;

	/**
	 * Get all properties exposed to the API
	 *
	 * @example
	 * ```
	 * [
	 *  "propName" => [
	 *    'setter' => true, //Set with setPropName
	 *    'getter'=> true', //Get with getPropName
	 *    'access' => self::PROP_PROTECTED | self::PROP_PUBLIC | null // is a public or protected property or null if there's just setters and getters
	 * ]
	 * ```
	 * So properties are:
	 *
	 * 1. Readable in the API if they have access public or getter = true
	 * 2. Writable in the API if they have access public or setter = true
	 *
	 * @return array
	 * @example Get writable property names
	 * ```
	 * $writablePropNames = array_keys(array_filter($this->getApiProperties(), function($r) {return ($r['setter'] || $r['access'] = self::PROP_PUBLIC);}));
	 * ```
	 */
	public static function getApiProperties(): array
	{
		$cacheKey = 'api-props-' . static::class;
		
		$ret = App::get()->getCache()->get($cacheKey);
		if ($ret !== null) {
			return $ret;
		}

		$arr = [];
		$reflectionObject = new ReflectionClass(static::class);
		$methods = $reflectionObject->getMethods(ReflectionMethod::IS_PUBLIC);

		foreach ($methods as $method) {

			if ($method->isStatic()) {
				continue;
			}
			
			if (substr($method->getName(), 0, 3) == 'get') {

				$params = $method->getParameters();
				foreach ($params as $p) {
					if (!$p->isDefaultValueAvailable()) {
						continue 2;
					}
				}

				$propName = lcfirst(substr($method->getName(), 3));
				if(!isset($arr[$propName])) {
					$arr[$propName] = ["setter" => false, "getter" => false, "access" => null];
				}
				$arr[$propName]['getter'] = true;				
			}

			if (substr($method->getName(), 0, 3) == 'set') {
				$propName = lcfirst(substr($method->getName(), 3));
				if(!isset($arr[$propName])) {
					$arr[$propName] = ["setter" => false, "getter" => false, "access" => null];
				}
				$arr[$propName]['setter'] = true;				
			}
		}

		$props = $reflectionObject->getProperties();

		foreach ($props as $prop) {
			if (!$prop->isStatic()) {
				$propName = $prop->getName();
				if(!isset($arr[$propName])) {
					$arr[$propName] = ["setter" => false, "getter" => false, "access" => null];
				}

				if($prop->isPublic()) {	
					$arr[$propName]['access'] = self::PROP_PUBLIC;					
					$arr[$propName]['setter'] = false;
					$arr[$propName]['getter'] = false;
				}				
				if($prop->isProtected()) {
					$arr[$propName]['access'] = self::PROP_PROTECTED;					
				}
			}
		}
		
		App::get()->getCache()->set($cacheKey, $arr);

		return $arr;
	}


	/**
	 * Load properties from the config.php file defined as:
	 *
	 * ```
	 * [
	 *  'modulePackage' => [
	 *    'moduleName' => [
	 *      'propName' => 'value'
	 *    ]
	 *   ]
	 * ]
	 * ```
	 *
	 * @return array
	 */
	protected static function loadPropertiesFromConfigFile() : array {
		$config = go()->getConfig();

		$pkgName = static::getModulePackageName();

		if(!isset($config[$pkgName])) {
			return [];
		}

		if($pkgName == "core") {
			$c = $config[$pkgName];
		} else
		{
			$modName = static::getModuleName();

			if(!isset($config[$pkgName][$modName])) {
				return [];
			}
			$c = $config[$pkgName][$modName];
		}

		$props = array_filter(static::getApiProperties(), function($p) {
			// only defined props
			return isset($p['access']);
		});

		return array_filter($c, function($key) use ($props) { return in_array($key, $props);}, ARRAY_FILTER_USE_KEY);
	}

  /**
   * Get the readable property names as array
   *
   * @return string[]
   */
	public static function getReadableProperties(): array
	{
		return array_keys(array_filter(static::getApiProperties(), function($props){
			return $props['getter'] || $props['access'] == self::PROP_PUBLIC;
		}));
	}

  /**
   * Get the readable property names as array
   *
   * @return string[]
   */
	public static function getWritableProperties(): array
	{
		return array_keys(array_filter(static::getApiProperties(), function($props){
			return $props['setter'] || $props['access'] == self::PROP_PUBLIC;
		}));
	}

	protected static function isProtectedProperty($name): bool
	{
		$props = static::getApiProperties();

		if(!isset($props[$name])) {
			return false;
		}

		return $props[$name]['access'] === self::PROP_PROTECTED;
	}

  /**
   * Convert model into array for API output.
   *
   * @param string[]|null $properties
   * @return array
   */
	public function toArray(array $properties = null): array
	{
		$arr = [];
		
		if(empty($properties)) {
			$properties = $this->getReadableProperties();
		}

		foreach ($properties as $propName) {
		  $arr[$propName] = $this->propToArray($propName);
		}
		
		return $arr;
	}


	protected function propToArray($name) {
		$value = $this->getValue($name);
		return $this->convertValueToArray($value);
	}

	/**
	 * Converts value to an array if supported

	 * @param mixed $value
	 * @return mixed
	 */
	public static function convertValueToArray($value) {
		if ($value instanceof ArrayableInterface) {
			return $value->toArray();
		} elseif (is_array($value)) {
			foreach ($value as $key => $v) {
				$value[$key] = static::convertValueToArray($v);
			}
			return $value;
		} else if($value instanceof ArrayObject) {

			if(empty($value)) {
				return $value;
			}
			$arr = $value->getArray();
			foreach ($arr as $key => $v) {
				$arr[$key] = static::convertValueToArray($v);
			}
			return $arr;
		} else if($value instanceof DateTime) { //if (is_null($value) || is_scalar($value) || $value instanceof \StdClass) {
			return (string) $value;
		} else {
			return $value;
		}
	}


  /**
   * Set public properties with key value array.
   *
   * This function should also normalize input when you extend this class.
   *
   * For example dates in ISO format should be converted into DateTime objects
   * and related models should be converted to an instance of their class.
   *
   *
   * @Example
   * ```````````````````````````````````````````````````````````````````````````
   * $model = User::findByIds([1]);
   * $model->setValues(['username' => 'admin']);
   * $model->save();
   * ```````````````````````````````````````````````````````````````````````````
   *
   *
   * @param array $values ["propName" => "value"]
   * @return static
   * @throws InvalidArgumentException
   */
	public function setValues(array $values): Model
	{
		foreach($values as $name => $value) {
			$this->setValue($name, $value);
		}
		return $this;
	}


  /**
   * Set a property with API input normalization.
   *
   * It also uses a setter function if available
   *
   * @param string $propName
   * @param mixed $value
   * @return $this
   * @throws InvalidArgumentException
   */
	public function setValue(string $propName, $value): Model
	{

		$props = $this->getApiProperties();

		if(!isset($props[$propName])) {
			throw new InvalidArgumentException("Not existing property $propName for " . static::class);
		}

		if($props[$propName]['setter']) {
			$setter = 'set' . $propName;	
			$this->$setter($value);
		} else if($props[$propName]['access'] == self::PROP_PUBLIC){
			$this->{$propName} = $this->normalizeValue($propName, $value);
		}	else if($props[$propName]['getter']) {
			go()->warn("Ignoring setting of read only property ". $propName ." for " . static::class);
		} else{
			throw new InvalidArgumentException("Invalid property ". $propName ." for " . static::class);
		}

		return $this;
	}

	/**
	 * Normalizes API input for this model.
	 * 
	 * @param string $propName
	 * @param mixed $value
	 * @return mixed
	 */
	protected function normalizeValue(string $propName, $value) {
		return $value;
	}

  /**
   * Gets a public property. Also uses getters functions.
   *
   * @param string $propName
   * @return mixed
   * @throws InvalidArgumentException
   */
	public function getValue(string $propName) {
		$props = $this->getApiProperties();
		
		if(!isset($props[$propName])) {
			throw new InvalidArgumentException("Not existing property $propName in " . static::class);
		}

		if($props[$propName]['getter']) {
			$getter = 'get' . $propName;	
			return $this->$getter();
		} elseif($props[$propName]['access'] === self::PROP_PUBLIC){
			return $this->{$propName};
		}	else{
			throw new InvalidArgumentException("Can't get write only property ". $propName . " in " . static::class);
		}
	}
	
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
	
	/**
	 * Gets the class name without the namespace
	 * 
	 * eg. class go\modules\community\notes\model\Note becomes just "note"
	 * 
	 * @return string
	 */
	public static function getClassName(): string
	{
		$cls = static::class;
		return substr($cls, strrpos($cls, '\\') + 1);
	}


	/**
	 * Get module name
	 *
	 * @return string
	 */
	public static function getModuleName(): string
	{
		if(strpos(static::class, "go\\core") === 0) {
			return "core";
		}

		return explode("\\", static::class)[3];
	}

	/**
	 * Get the module package name
	 * Is nullable for backwards compatibility with old framework
	 * @return string|null
	 */
	public static function getModulePackageName(): ?string
	{
		if(strpos(static::class, "go\\core") === 0) {
			return "core";
		}

		return explode("\\", static::class)[2];
	}
}
