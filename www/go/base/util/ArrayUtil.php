<?php


namespace GO\Base\Util;


class ArrayUtil {

	/**
	 * Merge array recurively. 
	 * 
	 * array_merge_recursive from php does not handle string elements right. 
	 * It does not overwrite them but it creates unwanted sub arrays.
	 * 
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	public static function mergeRecurive(array $array1, array $array2) {
		foreach ($array2 as $key => $value) {
			if (is_array($value) && isset($array1[$key])) {
				$array1[$key] = self::mergeRecurive($array1[$key], $value);
			} else {
				$array1[$key] = $value;
			}
		}

		return $array1;
	}
	
	public static function caseInsensitiveSort(&$array) {
		if (version_compare(PHP_VERSION, "5.4") > -1) {
			return ksort($array, SORT_STRING | SORT_FLAG_CASE);
		} else {
			return uksort($array, 'strcasecmp');
		}
	}

}
