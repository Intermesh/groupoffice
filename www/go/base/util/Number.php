<?php
/*
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * This class contains functions that perform operations on numbers. It 
 * formats numbers according to the user preferences.
 *  
 * @copyright Copyright Intermesh
 * @version $Id: Number.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.util
 * @since Group-Office 3.0
 */


namespace GO\Base\Util;


class Number {

	/**
	 * Format a number by using the user preferences
	 *
	 * @param	int $number The number
	 * @param	int $decimals Number of decimals to display
	 * @access public
	 * @return StringHelper
	 */

	public static function localize($number, $decimals=2)
	{		
		if($number===null)
			return "";
		
		$ts = \GO::user() ? \GO::user()->thousands_separator : \GO::config()->default_thousands_separator;
		$ds = \GO::user() ? \GO::user()->decimal_separator : \GO::config()->default_decimal_separator;
		return number_format(floatval($number), (int) $decimals, $ds, $ts);
	}

	/**
	 * Conver a number formatted by using the user preferences to a number understood by PHP
	 *
	 * @param	int $number The number
	 * @param	int $decimals Number of decimals to display
	 * @access public
	 * @return StringHelper
	 */

	public static function unlocalize($number)
	{	
		if($number=="")
			return null;
		
		$ts = \GO::user() ? \GO::user()->thousands_separator : \GO::config()->default_thousands_separator;
		$ds = \GO::user() ? \GO::user()->decimal_separator : \GO::config()->default_decimal_separator;
		$number = str_replace($ts,'', $number);
		$number = str_replace($ds,'.',$number);
		
		if(!empty($number) && !is_numeric($number))
			return false;
		
		return floatval($number);
		//return str_replace($ds,'.',$number);
	}

	/**
	 * Format a size to a human readable format.
	 *
	 * @deprecated
	 * @param	int $size The size in bytes
	 * @param	int $decimals Number of decimals to display
	 * @access public
	 * @return StringHelper
	 */

	public static function formatSize($size, $decimals = 1) {
		
		if($size==0)
			return "0 bytes";
		
		switch ($size) {
			case ($size >= 1073741824) :
				$size = self::localize($size / 1073741824, $decimals);
				$size .= " G";
				break;

			case ($size >= 1048576) :
				$size = self::localize($size / 1048576, $decimals);
				$size .= " M";
				break;

			case ($size >= 1024) :
				$size = self::localize($size / 1024, $decimals);
				$size .= " K";
				break;

			default :
				$size = self::localize($size, $decimals);
				$size .= " bytes";
				break;
		}
		return $size;
	}
	
	/**
	 * Return size in MB. Value can be 1G, 1M, 1K or a size in bytes.
	 * @param mixed $value
	 * @return int
	 */
	public static function configSizeToMB($value){
		$value=trim($value);
		$lastchar = substr($value, -1);
		
		switch($lastchar){
			case 'M':
				return substr($value,0,-1);
				break;
			
			case 'G':
				return substr($value,0,-1)*1024;
				break;
			
			case 'K':
				return substr($value,0,-1)/1024;
				break;
			
			default:
				//assume bytes
				return $value/1024/1024;
				break;
		}
	}
}
?>
