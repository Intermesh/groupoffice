<?php
namespace go\core\util;


class Color {
	public static function background(): string
	{
		$color = str_pad(dechex(mt_rand(0, 255)), 3, '0', STR_PAD_LEFT);

		return '#' . $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
	}
}