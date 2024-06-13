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
 * @package GO.base.db
 */

/**
 * Model to perform grouped SQL queries. For example SUM, AVG, COUNT etc.
 *
 * @package GO.base.db
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl> 
 * 
 * @method Grouped model()
 */

namespace GO\Base\Model;


class Grouped extends \GO\Base\Model {

	
	private $_attributes=array();
	
	public function __set($name, $value) {
		$this->_attributes[$name]=$value;
	}
	
	public function __get($name) {
		return isset($this->_attributes[$name]) ? $this->_attributes[$name] : parent::__get($name);
	}
	
	/**
	 * Execute a grouped query and return the statement. This class may be extended
	 * so you can document loaded properties or implement additional functions.
	 * 
	 * @param string $modelName
	 * @param array $groupBy eg array('t.name')
	 * @param string $selectFields
	 * @param \GO\Base\Db\FindParams $findParams
	 * @return \GO\Base\Db\ActiveStatement
	 */
	public function load($modelName, $groupBy, $selectFields, \GO\Base\Db\FindParams $findParams=null){
		
		if(!isset($findParams))
			$findParams = \GO\Base\Db\FindParams::newInstance ();
		
		$findParams->ignoreAcl()
				->select($selectFields)
				->group($groupBy)
				->fetchClass(get_class($this));

		$stmt = \GO::getModel($modelName)->find($findParams);
		
		return $stmt;
	}
	
	public function getAttributes(){
		
		$a = $this->_attributes;
		
		$r = new \ReflectionObject($this);
		$publicProperties = $r->getProperties(\ReflectionProperty::IS_PUBLIC);
		foreach($publicProperties as $prop){
			//$att[$prop->getName()]=$prop->getValue($this);
			//$prop = new ReflectionProperty();
			if(!$prop->isStatic()) {
				//$this->_magicAttributeNames[]=$prop->getName();
				$a[$prop->getName()]=$this->{$prop->getName()};
			}
		}
		
		return $a;
	}

}
