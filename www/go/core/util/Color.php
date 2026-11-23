<?php
namespace go\core\util;


class Color {
	public static function background(): string
	{
		$color = str_pad(dechex(mt_rand(0, 255)), 3, '0', STR_PAD_LEFT);

		return '#' . $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
	}

	/**
	 * Check if the value is a hexadecimal color without "#" like "FF0000", "F00" or "FF000080"
	 *
	 * Use it to validate colors that are rendered in HTML or CSS.
	 *
	 * @param string $color
	 * @return bool
	 */
	public static function isHex(string $color): bool
	{
		return preg_match('/^([0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/iD', $color) === 1;
	}
}