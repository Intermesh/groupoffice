<?php
namespace go\core\util;

use ArrayAccess;
use go\core\ErrorHandler;
use go\core\exception\JsonPointerException;
use InvalidArgumentException;
use JsonException;
use function json_decode;

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
   * @throws InvalidArgumentException
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
 * @throws JsonException if the JSON cannot be decoded.
 * @link http://www.php.net/manual/en/function.json-decode.php
 */
  public static function decode(string $json, bool $assoc = false, int $depth = 512, int $options = 0) {
    $data = json_decode($json, $assoc, $depth, $options);
    if (JSON_ERROR_NONE !== json_last_error()) {
        throw new JsonException(
            "JSON decoding error: '".json_last_error_msg()."'.\n\nJSON data given: \n\n".var_export($json, true)
        );
    }

    return $data;
  }


	/**
	 * Get a value from a JSON document
	 *
	 * @param array|ArrayAccess $doc JSON document
	 * @param string $pointer JSON Pointer according to https://datatracker.ietf.org/doc/html/rfc6901
	 * @return mixed
	 * @throws JsonPointerException
	 */
	public static function get(array|ArrayAccess $doc, string $pointer) : mixed {
		return self::resolvePointer($doc, self::explodePointer($pointer));
	}

	private static function explodePointer(string $pointer) : array {

		$parts = explode('/', ltrim($pointer, '/'));
//		if (array_shift($parts) !== "")
//		{
//			throw new JsonPointerException("path must start with / in $pointer");
//		}
		for ($i = 0; $i < count($parts); $i++)
		{
			$parts[$i] = str_replace('~1', '/', $parts[$i]);
			$parts[$i] = str_replace('~0', '~', $parts[$i]);
		}
		return $parts;
	}

	private static function resolvePointer(mixed $doc, array $pointer) : mixed {

		if(empty($pointer)) {
			return $doc;
		}

		$part = array_shift($pointer);
		if ($part == '*') {
			$ret = [];
			foreach ($doc as $val) {
				$res = self::resolvePointer($val, $pointer);
				// According to JMAP spec:
				// If the result of applying the rest of the pointer tokens to each item was itself an array, the contents of
				// this array are added to the output rather than the array itself (i.e., the result is flattened from an
				// array of arrays to a single array).
				if(is_array($res)) {
					$ret = array_merge($ret, $res);
				} else {
					$ret[] = $res;
				}
			}
			return $ret;
		}

		if(is_array($doc)) {
			if (!array_key_exists($part, $doc)) {
				throw new JsonPointerException("Could not resolve path part " . $part);
			}
		} else {
			if (!$doc->offsetExists($part)) {
				throw new JsonPointerException("Could not resolve path part " . $part);
			}
		}

		return self::resolvePointer($doc[$part], $pointer);
	}

	/**
	 * Patch a JSON document according to the JMAP spec
	 *
	 * A PatchObject is of type String[*] and represents an unordered set of patches. The keys are a path in JSON Pointer Format [@!RFC6901], with an implicit leading “/” (i.e., prefix each key with “/” before applying the JSON Pointer evaluation algorithm).
	 *
	 * All paths MUST also conform to the following restrictions; if there is any violation, the update MUST be rejected with an invalidPatch error:
	 *
	 * - The pointer MUST NOT reference inside an array (i.e., you MUST NOT insert/delete from an array; the array MUST be replaced in its entirety instead).
	 * - All parts prior to the last (i.e., the value after the final slash) MUST already exist on the object being patched.
	 * - There MUST NOT be two patches in the PatchObject where the pointer of one is the prefix of the pointer of the other, e.g., “alerts/1/offset” and “alerts”.
	 *
	 * The value associated with each pointer determines how to apply that patch:
	 *
	 * - If null, set to the default value if specified for this property; otherwise, remove the property from the patched object. If the key is not present in the parent, this a no-op.
	 * - Anything else: The value to set for this property (this may be a replacement or addition to the object being patched).
	 *
	 * @link https://jmap.io/spec-core.html#set
	 *
	 * @param array|ArrayAccess $doc
	 * @param array $patch
	 * @return ArrayAccess|array
	 * @throws JsonPointerException
	 */
	public static function patch(array|ArrayAccess $doc, array $patch): mixed
	{
		foreach($patch as $pointer => $value) {
			try {
				$deepPatch = str_starts_with($pointer, "/");
				$doc = static::patchProp($doc, self::explodePointer($pointer), $value, $deepPatch);
			} catch(JsonPointerException $e) {
				throw new JsonPointerException("The path " . $pointer ." doesn't exist");
			}
		}

		return $doc;
	}

	private static function patchProp(mixed $doc, array $pointer, mixed $value, bool $mustExist)
	{
		$count = count($pointer);
		$part = array_shift($pointer);
		if(!isset($doc[$part])) {
			if($mustExist) {
				throw new JsonPointerException();
			}
			$doc[$part] = [];
		}

		if(empty($pointer)) {
			$doc[$part] = $value;
		} else {
			$doc[$part] = self::patchProp($doc[$part], $pointer, $value, $count > 2);
		}

		return $doc;
	}
}