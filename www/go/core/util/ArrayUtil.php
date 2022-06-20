<?php
namespace go\core\util;

class ArrayUtil {
//  public static function isAssociative($array) {
//		return !empty(array_filter(array_keys($array), 'is_string'));
//	}

	/**
	 * Rename string array key and maintain position
	 *
	 * @param string $old
	 * @param string $new
	 * @return bool
	 */
	public static function renameKey($array, string $old, string $new): array
	{

		$i = array_search($old, array_keys($array));
		if($i === false) {
			return false;
		}

		$value = $array[$old];

		return array_merge(
			array_slice($array, 0, $i),
			[$new => $value],
			array_slice($array, $i + 1)
		);


	}
}