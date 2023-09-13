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
		if(preg_match('/^\d{1,2}:\d{2}$/', $strTime)) {
			$strTime .= ':00';
		}
		if(!preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $strTime)) {
			throw new \InvalidArgumentException('Invalid time format');
		}
		$arTime = explode(":", $strTime);
		return (intval($arTime[0]) * 3600) + (intval($arTime[1]) * 60) + intval($arTime[2]);
	}


	/**
	 * Convert a number of seconds to a human-readable time format HH:MM[:SS]
	 *
	 * @param int $seconds number of seconds
	 * @param bool $includeSeconds if true, render seconds
	 * @param bool $zeroPad Render hours and minutes with a leading zero
	 *
	 * @return string
	 */
	public static function fromSeconds(int $seconds, bool $includeSeconds = false, bool $zeroPad = true): string
	{
		$hours = floor($seconds / 3600);
		$seconds -= ($hours * 3600);
		$minutes = floor($seconds / 60);
		if($includeSeconds) {
			$seconds -= ($minutes * 60);
		}

		$ret = (($hours > 9 && $zeroPad) ? $hours : '0' . $hours) . ':' . ($minutes > 9 ? $minutes : '0' . $minutes);
		if($includeSeconds) {
			$ret .= ($seconds > 9 ? $seconds : '0'. $seconds);
		}
		return $ret;
	}
}