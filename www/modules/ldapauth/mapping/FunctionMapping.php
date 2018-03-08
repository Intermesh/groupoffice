<?php

namespace GO\Ldapauth\Mapping;


class FunctionMapping {

	private $_function;

	/**
	 * LDAP Mapping object for functions.
	 * 
	 * @param mixed $function Name of function or array('className','function'). It will be called with the \GO\Base\Ldap\Record $record parameter.
	 */
	function __construct($function) {
		$this->_function = $function;
	}

	function getValue(\GO\Base\Ldap\Record $record) {
		return call_user_func($this->_function, $record);		
	}

}
