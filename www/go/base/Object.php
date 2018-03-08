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
 * @version $Id: Number.php 7962 2011-08-24 14:48:45Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base
 */

/**
 * Base object class
 * 
 * All objects extend this class
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @version $Id: Number.php 7962 2011-08-24 14:48:45Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base
 * @abstract
 */

namespace GO\Base;


abstract class Object extends Observable{

	/**
     * Returns the name of this class.
     * @return StringHelper the name of this class.
     */
    public static function className()
    {
        return get_called_class();
    }
	
	/**
	 * Magic getter that calls get<NAME> functions in objects
	 
	 * @param StringHelper $name property name
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
			if(\GO::config()->debug)
				throw new \Exception("Can't get not existing property '$name' in '".$this->className()."'");
			else{
//				TODO Enable this when we're sure all properties exist
				trigger_error("Can't get not existing property '$name' in '".$this->className()."'", E_USER_NOTICE);
				return null;
			}
		}
	}		
	
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
	 * Magic setter that calls set<NAME> functions in objects
	 * 
	 * @param StringHelper $name property name
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
				$errorMsg = "Can't set read only property '$name' in '".$this->className()."'";
			}else {
				$errorMsg = "Can't set not existing property '$name' in '".$this->className()."'";
			}
			
			if(\GO::config()->debug)
				throw new \Exception($errorMsg);
			else{
				trigger_error($errorMsg, E_USER_NOTICE);
			}
		}
	}
	
}