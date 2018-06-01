<?php
namespace go\core\jmap;

class SetError extends \go\core\data\Model {
	
	public function __construct($type) {
		$this->type = $type;
	}
	public $type;
	public $description;
	public $properties;
	
	/**
	 * 
	 * Not in JMAP spec but useful for debugging
	 * 
	 * @var array
	 */
	public $validationErrors;
}
