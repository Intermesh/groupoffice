<?php
namespace go\core\jmap;

class SetError extends \go\core\data\Model {

	const ERROR_SERVER_FAIL = "serverFail";
	
	public function __construct($type, $description = null, $properties = null) {
		$this->type = $type;
		$this->description = $description;
		$this->properties = $properties;
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
