<?php

/**
 * Helper functions for time fields.
 *
 * In certain newer modules, time fields are saved in the HH:MM:SS format. This class contains a few helper functions
 * that make calculations less um.. tricky.
 *
 * @author Joachim van de Haterd <jvdhaterd@intermesh.nl>
 *
 */

namespace go\core\util;


class Time
{

	/**
	 * Convert a time of HH:MM:SS to number of seconds without a pesky DateTime class.
	 *
	 * @param string $strTime
	 * @return int
	 */
	public static function toSeconds(string $strTime) :int
	{
		if(!preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $strTime)) {
			throw new \InvalidArgumentException('Invalid time format');
		}
		$arTime = explode(":", $strTime);
		return (intval($arTime[0]) * 3600) + (intval($arTime[1]) * 60) + intval($arTime[2]);
	}
}