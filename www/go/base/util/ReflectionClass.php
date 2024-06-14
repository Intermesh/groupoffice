<?php


namespace GO\Base\Util;


class ReflectionClass extends \ReflectionClass {

	
	private $_overriddenMethods;
	/**
	 * Determine which properties are of the childs class. 
	 * Return them as an array.
	 * 
	 * @param int $filter The optional filter, for filtering desired property 
	 * types. It's configured using the ReflectionProperty constants, and defaults
	 * to all property types.
	 * 
	 * @return array
	 */
	public function getParentPropertiesDiff($filter=null){
		$parent = $this->getParentClass();
    return array_diff($this->getProperties($filter),$parent->getProperties($filter));
	}
	
	/**
	 * Determine which methods are of the childs class. 
	 * Return them as an array.
	 * 
	 * @return array
	 */
	public function getParentMethodsDiff(){
		$parent = $this->getParentClass();
    return array_diff($this->getMethods(),$parent->getMethods());
	}
	
	/**
	 * Returns all methods that override a parent method.
	 * 
	 * @return array
	 */
	public function getOverriddenMethods() {
		if(!isset($this->_overriddenMethods)){
			$this->_overriddenMethods = array();

			if (!$parentClass = $this->getParentClass())
				return $this->_overriddenMethods;

			//find all public and protected methods in ParentClass
			$parentMethods = $parentClass->getMethods(
							\ReflectionMethod::IS_PUBLIC ^ \ReflectionMethod::IS_PROTECTED
			);

			//find all parentmethods that were redeclared in ChildClass
			foreach ($parentMethods as $parentMethod) {
				$declaringClass = $this->getMethod($parentMethod->getName())
								->getDeclaringClass()
								->getName();

				if ($declaringClass === $this->getName()) {
					$this->_overriddenMethods[]=$parentMethod->getName(); // print the method name
				}
			}
		}
		
		return $this->_overriddenMethods;
	}
	
	/**
	 * Check if a method is overriding a parent method
	 * 
	 * @param string $method
	 * @return boolean
	 */
	public function methodIsOverridden($method){
		$overrides = $this->getOverriddenMethods();
		return in_array($method, $overrides);
	}

}
