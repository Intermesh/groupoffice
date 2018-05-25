<?php
/**
 * Group-Office
 * 
 * Copyright Intermesh BV. 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @version $Id: AbstractSettingsCollection.php 7962 2011-08-24 14:48:45Z wsmits $
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @package GO.base.model
 */

/**
 * 
 * @package GO.base.model
 */

namespace GO\Base\Model;

use GO;

abstract class AbstractSettingsCollection extends \GO\Base\Model {

	/**
	 * The id of the user you want the settings for.
	 * @var int
	 */
	private $_userId = 0;
	
	/**
	 * Cache already loaded models in this array.
	 * 
	 * @var array 
	 */
	private static $_models;

	public function __construct($userId=0) {
		$this->_userId = $userId;
		$this->_loadData();
	}
	
	/**
	 * Load function to load the setting values to the properties
	 * 
	 * @return AbstractSettingsCollection Description
	 */
	public static function load($userId=0){

		$className=  get_called_class();
		if(isset(self::$_models[$className.':'.$userId])){
			$model = self::$_models[$className.':'.$userId];			
		}else
		{
			$model=self::$_models[$className.':'.$userId]=new $className($userId);			
		}	
		
		
		return $model;
	}
	
	/**
	 * All settings of this model will have this prefix.
	 * @return StringHelper
	 */
	protected function myPrefix() {		
		return $this->getModule().'_';
	}
	
	private function _loadData(){
		
		$properties = $this->_getReflectionClass()->getParentPropertiesDiff(\ReflectionProperty::IS_PUBLIC);
		
		$propertyNames=array();
		foreach($properties as $property){
			$propertyNames[] = $this->myPrefix().$property->name;
		}
		
		$values = GO::config()->getSettings($propertyNames,$this->_userId);

		foreach($values as $property=>$value){
			if(isset($value)){
				if(substr($value,0,11)=='serialized:'){
					$value = unserialize(substr($value,11));
				}
				$this->{substr($property,strlen($this->myPrefix()))} = $value;
			}
		}
	} 
	
	
	/**
	 * Determine which properties are of the childs class. 
	 * Return them as an array.
	 * 
	 * @param \ReflectionClass $obj
	 * @return array
	 */
//	public function get_parent_properties_diff( $obj) {
//    $parent = $obj->getParentClass();
//    return array_diff($obj->getProperties(),$parent->getProperties());
//}
	
	
	public function save(){
		
		if(!$this->validate())
			return false;
		
		$properties = $this->_getReflectionClass()->getParentPropertiesDiff(\ReflectionProperty::IS_PUBLIC);
		$success = true;
		foreach($properties as $property){				
			$key = $property->name;
			$value = $this->{$key};
			
			if(is_array($value) || is_object($value))
				$value = 'serialized:'.serialize ($value);

			$success = $success && GO::config()->save_setting($this->myPrefix().$key, $value, $this->_userId);			
		}
		return $success;
	}
	
	/**
	 * A validate function that can be used in the child class.
	 */
	public function validate(){
		return true;
	}
	
	/**
	 * Function to save an array of properties at once. ($key=>$value array)
	 * (Usually the $params array that is send by the browser)
	 * 
	 * @param array $data
	 * @return boolean
	 */
	public function saveFromArray($data){
		
		$properties = $this->_getReflectionClass()->getParentPropertiesDiff(\ReflectionProperty::IS_PUBLIC);
				
		foreach($properties as $property){
			$key = $property->name;
			if(key_exists($key, $data))
				$this->{$key} = $data[$key];
		}
			
		return $this->save();	
	}
	
	/**
	 * Private function to return the Reflection class of the current object
	 * 
	 * @return \ReflectionClass
	 */
	private function _getReflectionClass(){
		return new \GO\Base\Util\ReflectionClass($this);
	}
	
	/**
	 * Return the settings as a $key=>$value array
	 * 
	 * @return array
	 */
	public function getArray(){
		
		$data = array();
		$properties = $this->_getReflectionClass()->getParentPropertiesDiff(\ReflectionProperty::IS_PUBLIC);
		
		foreach($properties as $property){
			$key = $property->name;	  
			$data[$key] = $this->{$key};
		}
		
		return $data;
	}
	
}
