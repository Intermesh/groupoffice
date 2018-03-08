<?php

namespace GO\Ldapauth\Mapping;


class Constant {

	private $_value;

	/**
	 * LDAP Mapping object for functions or constants
	 * 
	 * @param mixed $function Name of function or array('className','function') or contstant value.
	 */
	function __construct($value) {
		$this->_value = $value;
	}

	function getValue(\GO\Base\Ldap\Record $record) {
		return $this->_value;
	}

}