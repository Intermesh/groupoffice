<?php
namespace go\core\util;

use JsonSerializable;

class ArrayObject extends \ArrayObject implements JsonSerializable {

	public $serializeJsonAsObject = false;
	
	/**
	 * Find the key in an array by a callable function.
	 * If the function returns true the key is returned
	 * 
	 * @example
	 * ```````````````````````````````````````````````````````````````````````````
	 * 
	 * $arr = [3, 5, 9];
	 * 
	 * $findGreatherThan = 4;
	 * 
	 * $arrayObject = new \go\core\util\ArrayObject($arr);
	 *	
	 * $key = $arrayObject->findKey(function($i) use ($findGreatherThan){
	 *		return $i >= $findGreatherThan;
	 * });	
	 * 
	 * //$key is 1 (value 5)
	 * ```````````````````````````````````````````````````````````````````````````
	 * 
	 * Note that if you want to evaluate false you need to use '===' because this 
	 * may return key 0 which evaluates to false too.
	 * 
	 * @param \go\core\util\callable $fn
	 * @return mixed
	 */
	public function findKeyBy(callable $fn) {		
		foreach($this as $key => $value) {
			if(call_user_func($fn, $value)) {
				return $key;
			}
		}
		
		return false;
	}
	
	/**
	 * Similar to getArrayCopy() but it is recursive when there are children that
	 * are also an ArrayObject
	 * 
	 * @return array
	 */
	public function getArray() {
		return array_map( function($item){
        return $item instanceof self ? $item->getArray() : $item;
    }, $this->getArrayCopy() );
	}
	
	
	/**
	 * Merge array recursively. 
	 * 
	 * array_merge_recursive from php does not handle string elements right. 
	 * It does not overwrite them but it creates unwanted sub arrays.
	 * 
	 * @param array|ArrayObject $arr
	 * @return self
	 */
	public function mergeRecursive($arr) {
		foreach ($arr as $key => $value) {
			if (is_array($value) && isset($this[$key]) && is_array($this[$key])) {

				$this[$key] = new self($this[$key]);
				$this[$key]->mergeRecursive($value);
			} else {
				$this[$key] = $value;
			}
		}

		return $this;
	}
	
	/**
	 * Compare this array with a given array and return all values that are not present or different in the given array.
	 * 
	 * @param array $arr
	 * @return array
	 */
	public function diff(array $arr) {
		$diff = [];
		foreach ($this as $key => $value) {
			if (!array_key_exists($key, $arr) || !$this->equals($arr[$key],  $value)) {
				$diff[$key] = $value;
			}
		}
		
		return $diff;
	}
	
	private function equals($a, $b) {
		if(is_array($a) && is_array($b)) {
			$aObj = new static($a);
			$bObj = new static($b);
			return empty($aObj->diff($b)) && empty($bObj->diff($a));
		} else
		{
			return $a === $b;
		}
	}

	public function jsonSerialize()
	{
		if($this->serializeJsonAsObject && empty($this)) 
		{
			return new \stdClass;	
		} 

		return $this->getArray();
	}

	
}
