<?php

namespace GO\Site\Components;


abstract class Widget extends \GO\Base\Model {
	
	/**
	 * @var integer the counter for generating implicit IDs.
	 */
	private static $_counter=0;
	/**
	 * Id will be set automaticaly is setId() is never called
	 * @var StringHelper id of the widget.
	 */
	private $_id;
	
	public function __construct($config=array()) {
		
		if(!is_array($config))
			throw new \Exception('Widget::__construct param \$config must be an array!');
		
		$ref = new \ReflectionClass($this);
		foreach($config as $key => $value){
			
			if(!$ref->hasProperty($key)){
				throw new \Exception('Config option '.$key.' does not exist for '. get_class($this));	
			}
			
			if($ref->getProperty ($key)->isPublic()){
				$this->{$key}=$value;
			}
		}
			
		$this->init();
	}
	
	/**
	 * Overwrite this for initial widget setup before rendering anything
	 * Do not overwrite the constructor because it will lose it functionality
	 * to set the default option as a config array
	 */
	protected function init() {
		
	}
	
	/**
	 * Returns the ID of the widget or generates a new one if requested.
	 * @param boolean $autoGenerate whether to generate an ID if it is not set previously
	 * @return string id of the widget.
	 */
	public function getId()
	{
		if($this->_id!==null)
			return $this->_id;
		return $this->_id='go'.self::$_counter++;
	}
	
	/**
	 * Sets the ID of the widget.
	 * @param string $value id of the widget.
	 */
	public function setId($value)
	{
		$this->_id=$value;
	}
	
	/**
	 * PHP magic method that returns the string representation of this object.
	 * __toString cannot throw exception use trigger_error to bypass this limitation
	 * @return string the string representation of this object.
	 */
	public function __toString() {
		try {
			return $this->render();
		} catch (\Exception $e) {
			trigger_error($e->getMessage());
			return '';
		}
	}
	
	/**
	 * The render function to render this widget 
	 */
	abstract public function render();
	
	public static function getAjaxResponse($params){
		return true;
	}
	
}
