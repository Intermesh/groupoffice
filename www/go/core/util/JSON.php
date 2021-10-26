<?php
namespace go\core\util;

use go\core\ErrorHandler;
use InvalidArgumentException;

class JSON {
  /**
   * Encode data to JSON
   * 
   * 
   * @param mixed $value
   * The value being encoded. Can be any type except a resource.
   * 
   * All string data must be UTF-8 encoded.
   * 
   * PHP implements a superset of JSON - it will also encode and decode scalar types and NULL. The JSON standard only supports these values when they are nested inside an array or an object.
   * 
   * @param int $options
   * [optional]
   * 
   * Bitmask consisting of JSON_HEX_QUOT, JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS, JSON_NUMERIC_CHECK, JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES, JSON_FORCE_OBJECT, JSON_UNESCAPED_UNICODE. JSON_THROW_ON_ERROR The behaviour of these constants is described on the JSON constants page.
   * 
   * @param int $depth
   * [optional]
   * 
   * Set the maximum depth. Must be greater than zero.
   * 
   * @return string
   */
  public static function encode($value, int $options = 0, int $depth = 512): string
  {
    $string = json_encode($value, $options, $depth);

    if($string === false && json_last_error() == JSON_ERROR_UTF8) {

			ErrorHandler::log("JSON had invalid UTF8 characters. Trying to cleanup.");
	    //try to clean up data
	    $value = self::cleanup($value);
	    $string = json_encode($value, $options, $depth);
    }

	  if($string === false) {
		   $error = "JSON encoding error: " . json_last_error_msg() .".";
			 throw new InvalidArgumentException($error);
    }
    
    return $string;
  }

	/**
	 * In some cases there can be invalid characters inside the data
	 * @param $mixed
	 *
	 * @return array|mixed|string
	 */
	private static function cleanup( $mixed ) {
		if (is_array($mixed)) {
			foreach ($mixed as $key => $value) {
				$mixed[$key] = self::cleanup($value);
			}
		} elseif (is_string($mixed)) {
			return StringUtil::cleanUtf8($mixed);
		}
		return $mixed;
	}

/**
 * Wrapper for json_decode that throws when an error occurs.
 *
 * @param string $json    JSON data to parse
 * @param bool $assoc     When true, returned objects will be converted
 *                        into associative arrays.
 * @param int $depth   User specified recursion depth.
 * @param int $options Bitmask of JSON decode options.
 *
 * @return mixed
 * @throws \InvalidArgumentException if the JSON cannot be decoded.
 * @link http://www.php.net/manual/en/function.json-decode.php
 */
  public static function decode(string $json, bool $assoc = false, int $depth = 512, int $options = 0) {
    $data = \json_decode($json, $assoc, $depth, $options);
    if (JSON_ERROR_NONE !== json_last_error()) {
        throw new \InvalidArgumentException(
            'json_decode error: ' . json_last_error_msg()
        );
    }

    return $data;
  }
}