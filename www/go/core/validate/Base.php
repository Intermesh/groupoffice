<?php

namespace go\core\validate;

use go\core\data\Model;

/**
 * Abstract validator for the ActiveRecord class
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
abstract class Base {

	private $id;

	/**
	 * Set to an error code if it doesn't validate.
	 * 
	 * @var int
	 */
	private $errorCode = null;

	/**
	 * Set extra error information to provide to the client
	 * 
	 * @var array 
	 */
	private $errorData = [];
	
	/**
	 * Error description
	 * @var string 
	 */
	private $errorDescription = "";

	/**
	 * Creates a new validation rule
	 * 
	 * @param string $id In most cases it should be set to an attribute name.
	 */
	public function __construct($id) {
		$this->id = $id;
	}

	/**
	 * Get the validation rule identifier. In most cases it should be set to an attribute name.
	 * @param string $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Get's the error code if validation failed.
	 * 
	 * @param string
	 */
	public function getErrorCode() {
		return $this->errorCode;
	}

	/**
	 * Get's the error code if validation failed.
	 * 
	 * @param string
	 */
	public function getErrorData() {
		return $this->errorData;
	}

	/**
	 * Get's the error code if validation failed.
	 * 
	 * @param string
	 */
	public function getErrorDescription() {
		return $this->errorDescription;
	}
	
	/**
	 * Set the validation error
	 * 
	 * @param int $code {@see ErrorCode}
	 * @param string $description Information for the API developers
	 * @param array $data Arbitrary data with information about the error
	 */
	protected function setValidationError($code, $description = null, $data = null) {
		$this->errorCode = $code;
		$this->errorDescription = $description;
		$this->errorData = $data;
	}

	/**
	 * Validate this rule on a model
	 * 
	 * @param Model $model The model to apply the rule on.
	 * @return bool
	 */
	public final function validate(Model $model) {
		$this->internalValidate($model);
		
		return !isset($this->errorCode);
	}
	
	abstract protected function internalValidate(Model $model);
					
}
