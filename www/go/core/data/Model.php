<?php

namespace go\core\data;

use Exception;
use go\core\App;
use go\core\data\ArrayableInterface;
use go\core\data\exception\NotArrayable;
use go\core\util\DateTime;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

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
abstract class Model implements ArrayableInterface, \JsonSerializable {

	/**
	 * Get the readable property names as array
	 * 
	 * @return string[]
	 */
	protected static function getReadableProperties() {

		$cacheKey = 'getReadableProperties-' . str_replace('\\', '-', static::class);
		
		$ret = App::get()->getCache()->get($cacheKey);
		if ($ret) {
			return $ret;
		}

		$arr = [];
		$reflectionObject = new ReflectionClass(static::class);
		$methods = $reflectionObject->getMethods(ReflectionMethod::IS_PUBLIC);

		foreach ($methods as $method) {
			/* @var $method ReflectionMethod */

			if ($method->isStatic()) {
				continue;
			}

			$params = $method->getParameters();
			foreach ($params as $p) {
				/* @var $p ReflectionParameter */
				if (!$p->isDefaultValueAvailable()) {
					continue 2;
				}
			}
			if (substr($method->getName(), 0, 3) == 'get') {
				$arr[] = lcfirst(substr($method->getName(), 3));
			}
		}

		$props = $reflectionObject->getProperties(ReflectionProperty::IS_PUBLIC);

		foreach ($props as $prop) {
			if (!$prop->isStatic()) {
				$arr[] = $prop->getName();
			}
		}
		
		App::get()->getCache()->set($cacheKey, $arr);

		return $arr;
	}

	
	
	/**
	 * Convert model into array for API output.
	 * 
	 * @param string[] $properties
	 * @return array
	 */
	public function toArray($properties = []) {

		$arr = [];
		
		if(empty($properties)) {
			$properties = $this->getReadableProperties();
		}

		foreach ($properties as $propName) {
			try {
				$value = ModelHelper::getValue($this, $propName);
				$arr[$propName] = $this->convertValue($value);
			} catch (NotArrayable $e) {
				
				App::get()->debug("Skipped prop " . static::class . "::" . $propName . " because type '" . gettype($value) . "' not scalar or ArrayConvertable.");
			}
		}
		
		return $arr;
	}

	/**
	 * Converts value to an array if supported
	 * 
	 * 
	 * @param type $value
	 * @param type $subReturnProperties
	 * @return DateTime
	 * @throws NotArrayable
	 */
	private function convertValue($value) {
		if ($value instanceof ArrayableInterface) {
			return $value->toArray();
		} elseif (is_array($value)) {
			//support an array of models too
			if (isset($value[0])) {
				$arr = [];
				foreach ($value as $key => $v) {
					$arr[$key] = $this->convertValue($v);
				}
				return $arr;
			}
			return $value;
		} else if (is_scalar($value) || is_null($value)) {
			return $value;
		} else {
			throw new NotArrayable();
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
	 * @param array $values  ["propNamne" => "value"]
	 * @return \static
	 */
	public function setValues(array $values) {
		ModelHelper::setValues($this, $values);
		return $this;
	}
	
	
	/**
	 * Magic getter that calls get<NAME> functions in objects
	 
	 * @param string $name property name
	 * @return mixed property value
	 * @throws Exception If the property setter does not exist
	 */
	public function __get($name)
	{			
		$getter = 'get'.$name;

		if(method_exists($this,$getter)){
			return $this->$getter();
		}else
		{
			throw new Exception("Can't get not existing property '$name' in '".static::class."'");			
		}
	}		
	
	/**
	 * Magic function that checks the get<NAME> functions
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name) {
		$getter = 'get' . $name;
		if (method_exists($this, $getter)) {
			// property is not null
			return $this->$getter() !== null;
		} else {
			return false;
		}
	}

	/**
	 * Magic properties can't be unset unless you implement logic to this
	 * 
	 * In most cases you want to set the property to null.
	 * 
	 * @param string $name
	 * @throws Exception2
	 */
	public function __unset($name) {
		throw new Exception("Can't unset magic property $name");
	}

	/**
	 * Magic setter that calls set<NAME> functions in objects
	 * 
	 * @param string $name property name
	 * @param mixed $value property value
	 * @throws Exception If the property getter does not exist
	 */
	public function __set($name,$value)
	{
		$setter = 'set'.$name;
			
		if(method_exists($this,$setter)){
			$this->$setter($value);
		}else
		{				
			
			$getter = 'get' . $name;
			if(method_exists($this, $getter)){
				
				//Allow to set read only properties with their original value.
				//http://stackoverflow.com/questions/20533712/how-should-a-restful-service-expose-read-only-properties-on-mutable-resources								
//				$errorMsg = "Can't set read only property '$name' in '".static::class."'";
				//for performance reasons we simply ignore it.
				App::get()->getDebugger()->debug("Discarding read only property '$name' in '".static::class."'");
			}else {
				$errorMsg = "Can't set not existing property '$name' in '".static::class."'";
				throw new Exception($errorMsg);
			}						
		}
	}
	
	public function jsonSerialize() {
		return $this->toArray();
	}
	
	/**
	 * Get's the class name without the namespace
	 * 
	 * eg. class go\modules\community\notes\model\Note becomes just "note"
	 * 
	 * @return string
	 */
	public static function getClassName() {
		$cls = static::class;
		return substr($cls, strrpos($cls, '\\') + 1);
	}
}
