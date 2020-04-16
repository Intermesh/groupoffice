<?php
namespace go\core\http;

use Exception as CoreException;


/**
 * Throw when an operation was forbidden.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Exception extends CoreException
{
	public $code = 200;
	/**
	 *  HTTP code error messages
	 *
	 * @var array
	 */
	public static $codes = [
//		'100' => 'Continue',
//		'101' => 'Switching Protocols',
		'200' => 'OK',
		'201' => 'Created',
//		'202' => 'Accepted',
//		'203' => 'Non-Authoritative Information',
		'204' => 'No Content',
//		'205' => 'Reset Content',
//		'206' => 'Partial Content',
//		'300' => 'Multiple Choices',
//		'301' => 'Moved Permanently',
//		'302' => 'Found',
//		'303' => 'See Other',
		'304' => 'Not Modified',
//		'305' => 'Use Proxy',
//		'307' => 'Temporary Redirect',
		'400' => 'Bad Request',
		'401' => 'Unauthorized',
//		'402' => 'Payment Required',
		'403' => 'Forbidden',
		'404' => 'Not Found',
		'405' => 'Method Not Allowed',
//		'406' => 'Not Acceptable',
//		'407' => 'Proxy Authentication Required',
//		'408' => 'Request Timeout',
		'409' => 'Conflict',
//		'410' => 'Gone',
//		'411' => 'Length Required',
//		'412' => 'Precondition Failed',
		'413' => 'Request Entity Too Large',
//		'414' => 'Request-URI Too Long',
//		'415' => 'Unsupported Media Type',
//		'416' => 'Requested Range Not Satisfiable',
//		'417' => 'Expectation Failed',
		'422' => 'Unprocessable Entity',
		'500' => 'Internal Server Error',
		'501' => 'Not Implemented',
//		'502' => 'Bad Gateway',
		'503' => 'Service Unavailable',
//		'504' => 'Gateway Timeout',
//		'505' => 'HTTP Version Not Supported',
	];
	
	public function __construct($code, $message = null) {
		$this->code = $code;
		if(empty($message)){
			$message = self::$codes[$code];
		}	
		
		parent::__construct($message, $code);
	}

}
