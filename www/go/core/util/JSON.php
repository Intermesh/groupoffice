<?php
namespace go\core\util;

use function GuzzleHttp\json_decode;

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
  public static function encode($value, $options = 0, $depth = 512) {
    $string = json_encode($value, $options);
		
		if($string === false) {
      $this->handleEncodeError($value);
    }
    
    return $string;
  }

  private static function handleEncodeError($data) {
    $error = "JSON encoding error: " . json_last_error_msg() .".";

    $string = var_export($data, true);
    $regex = '/(
      [\xC0-\xC1] # Invalid UTF-8 Bytes
      | [\xF5-\xFF] # Invalid UTF-8 Bytes
      | \xE0[\x80-\x9F] # Overlong encoding of prior code point
      | \xF0[\x80-\x8F] # Overlong encoding of prior code point
      | [\xC2-\xDF](?![\x80-\xBF]) # Invalid UTF-8 Sequence Start
      | [\xE0-\xEF](?![\x80-\xBF]{2}) # Invalid UTF-8 Sequence Start
      | [\xF0-\xF4](?![\x80-\xBF]{3}) # Invalid UTF-8 Sequence Start
      | (?<=[\x00-\x7F\xF5-\xFF])[\x80-\xBF] # Invalid UTF-8 Sequence Middle
      | (?<![\xC2-\xDF]|[\xE0-\xEF]|[\xE0-\xEF][\x80-\xBF]|[\xF0-\xF4]|[\xF0-\xF4][\x80-\xBF]|[\xF0-\xF4][\x80-\xBF]{2})[\x80-\xBF] # Overlong Sequence
      | (?<=[\xE0-\xEF])[\x80-\xBF](?![\x80-\xBF]) # Short 3 byte sequence
      | (?<=[\xF0-\xF4])[\x80-\xBF](?![\x80-\xBF]{2}) # Short 4 byte sequence
      | (?<=[\xF0-\xF4][\x80-\xBF])[\x80-\xBF](?![\x80-\xBF]) # Short 4 byte sequence (2)
    )/x';

    if(preg_match($regex, $string, $matches, PREG_OFFSET_CAPTURE)) {
      $pos = $matches[0][1];
      $fragment = mb_substr($string, max(0, $pos - 50), 100);

      $error .= "\nFound invalid UTF-8: " . $fragment;
    }

    throw new \InvalidArgumentException($error);
  }

/**
 * Wrapper for json_decode that throws when an error occurs.
 *
 * @param string $json    JSON data to parse
 * @param bool $assoc     When true, returned objects will be converted
 *                        into associative arrays.
 * @param int    $depth   User specified recursion depth.
 * @param int    $options Bitmask of JSON decode options.
 *
 * @return mixed
 * @throws \InvalidArgumentException if the JSON cannot be decoded.
 * @link http://www.php.net/manual/en/function.json-decode.php
 */
  public static function decode($json, $assoc = false, $depth = 512, $options = 0) {
    $data = \json_decode($json, $assoc, $depth, $options);
    if (JSON_ERROR_NONE !== json_last_error()) {
        throw new \InvalidArgumentException(
            'json_decode error: ' . json_last_error_msg()
        );
    }

    return $data;
  }
}