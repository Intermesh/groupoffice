<?php
namespace go\core\validate;

/**
 * Collection of common validation error codes
 * 
 * 
 * @example
 * ```
 * 
 * class Foo {
 * 
 *  use \go\core\validate\ValidationTrait;
 * 
 *  public $bar;
 * 
 *	public function save() {
 * 
 *		if(empty($var)) {
 *			$this->setValidationError('bar', ErrorCode::REQUIRED);
 *		}
 * 
 *		return !$this->hasValidationErrors();
 *	} 
 * 
 * }
 * 
 * ```
 * 
 * @copyright (c) 2017, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class ErrorCode {
	
	/**
	 * Property is required
	 */
	const REQUIRED = 1;
	
	/**
	 * Value is malformed
	 */
	const MALFORMED = 2;
	
	/**
	 * Delete impossible because it's in use
	 */
	const INUSE = 3;
	
	/**
	 * Error occurred in a related record
	 */
	const RELATIONAL = 4;
	
	/**
	 * Not found
	 */
	const NOT_FOUND = 5;
	
	/**
	 * Time zone is invalid
	 */
	const TIMEZONE_INVALID = 6;
	
	/**
	 * Conflict
	 */
	const CONFLICT = 7;
	
	/**
	 * Dependency not satisfied
	 */
	const DEPENDENCY_NOT_SATISFIED = 8;
	
	/**
	 * Error while establishing connection
	 */
	const CONNECTION_ERROR = 9;
	
	/**
	 * Invalid input
	 */
	const INVALID_INPUT = 10;
	
	/**
	 * The property must be unique
	 */
	const UNIQUE = 11;
	
	/**
	 * When the user does something that is not allowed
	 */
	const FORBIDDEN = 12;
	
	/**
	 * In case of an unexpectede exception
	 */
	const GENERAL = 13;
	
	private static $descriptions = [
			self::REQUIRED => 'Property is required',
			self::MALFORMED => 'Value is malformed',
			self::INUSE => 'Delete impossible because it\'s in use',
			self::RELATIONAL => 'Error occurred in a related record',
			self::NOT_FOUND => 'Not found',
			self::TIMEZONE_INVALID => 'Time zone is invalid',
			self::CONFLICT => 'Conflict',
			self::DEPENDENCY_NOT_SATISFIED => 'Dependency not satisfied',
			self::CONNECTION_ERROR => 'Error while establishing connection',
			self::INVALID_INPUT => 'Invalid input',
			self::UNIQUE => 'The property must be unique',
			self::FORBIDDEN => 'Forbidden'
	];	
	
	/**
	 * Get the error code description
	 * 
	 * The descriptions are for the developers and should not be used for the 
	 * application logic.
	 * 
	 * @param int $code
	 * @return string
	 */
	static function getDescription($code) {		
		return isset(self::$descriptions[$code]) ? self::$descriptions[$code] : '';
	}
}
