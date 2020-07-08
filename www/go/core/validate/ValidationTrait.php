<?php

namespace go\core\validate;

use go\core\App;

trait ValidationTrait {

	private $validationErrors = [];

	/**
	 * You can override this function to implement validation in your model.
	 * 
	 * @return boolean
	 */
	public final function validate() {

		$this->internalValidate();

		return !$this->hasValidationErrors();
	}

	abstract protected function internalValidate();

	/**
	 * Return all validation errors of this model
	 * 
	 * @return array eg. [['code' => $code, 'description' => $description, 'data' => $data]]
	 */
	public function getValidationErrors() {
		return $this->validationErrors;
	}

	/**
	 * Get validation errors formatted as string.
	 *
	 * @return string
	 */
	public function getValidationErrorsAsString() {

		$s = "";
		foreach($this->validationErrors as $key => $value) {
			$s .= '"' . $key . '": ' . $value['description'] . "\n";
		}

		return $s;
	}

	/**
	 * Get the validationError for the given attribute
	 * If the attribute has no error then fals will be returned
	 * 
	 * @param string $key
	 * @return array eg. array('code'=>'maxLength','info'=>array('length'=>10)) 
	 */
	public function getValidationError($key) {
		$validationErrors = $this->getValidationErrors();
		if (!empty($validationErrors[$key])) {
			return $validationErrors[$key];
		} else {
			return false;
		}
	}

	/**
	 * Set a validation error for the given field.
	 * If the error key is equal to a model attribute name, the view can render 
	 * an error on the associated form field.
	 * The key for an error must be unique.
	 * 
	 * @param string $key 
	 * @param int $code  Error code. {@see \go\core\validate\ErrorCode} class for general constants
	 * @param string $description Override the default description. Pure info for the API developer. Clients shouldn't use this.
	 * @param array $data Arbitrary data for output to the client
	 */
	public function setValidationError($key, $code, $description = null, $data = []) {

		if (!isset($description)) {
			$description = ErrorCode::getDescription($code);
		}
		
		go()->warn("Validation error in " . get_class($this) . '::' . $key . ': ' . $code .' = '.$description);

		$this->validationErrors[$key] = array_merge($data, ['code' => $code, 'description' => $description]);
	}

	/**
	 * Returns a value indicating whether there is any validation error.
	 * @param string $key attribute name. Use null to check all attributes.
	 * @return boolean whether there is any error.
	 */
	public function hasValidationErrors($key = null) {
		$validationErrors = $this->getValidationErrors();

		if ($key === null) {
			return count($validationErrors) > 0;
		} else {
			return isset($validationErrors[$key]);
		}
	}

}
