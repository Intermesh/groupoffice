<?php
/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: Number.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/**
 * This class contains functions that perform operations on numbers. It 
 * formats numbers according to the user preferences.
 *  
 * @copyright Copyright Intermesh
 * @version $Id: Number.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.utils
 * @since Group-Office 3.0
 */

class Number {

	/**
	 * Format a number by using the user preferences
	 *
	 * @param	int $number The number
	 * @param	int $decimals Number of decimals to display
	 * @access public
	 * @return StringHelper
	 */

	public static function format($number, $decimals=2)
	{		
		return number_format(floatval($number), $decimals, $_SESSION['GO_SESSION']['decimal_separator'], $_SESSION['GO_SESSION']['thousands_separator']);
	}

	/**
	 * Conver a number formatted by using the user preferences to a number understood by PHP
	 *
	 * @param	int $number The number
	 * @param	int $decimals Number of decimals to display
	 * @access public
	 * @return StringHelper
	 */

	public static function to_phpnumber($number)
	{
		$number = str_replace($_SESSION['GO_SESSION']['thousands_separator'],'', $number);
		return floatval(str_replace($_SESSION['GO_SESSION']['decimal_separator'],'.',$number));
	}

	/**
	 * Format a size to a human readable format.
	 *
	 * @param	int $size The size in bytes
	 * @param	int $decimals Number of decimals to display
	 * @access public
	 * @return StringHelper
	 */

	public static function format_size($size, $decimals = 1) {
		
		if($size==0)
			return 0;
		
		switch ($size) {
			case ($size > 1073741824) :
				$size = number_format($size / 1073741824, $decimals, $_SESSION['GO_SESSION']['decimal_separator'], $_SESSION['GO_SESSION']['thousands_separator']);
				$size .= " GB";
				break;

			case ($size > 1048576) :
				$size = number_format($size / 1048576, $decimals, $_SESSION['GO_SESSION']['decimal_separator'], $_SESSION['GO_SESSION']['thousands_separator']);
				$size .= " MB";
				break;

			case ($size > 1024) :
				$size = number_format($size / 1024, $decimals, $_SESSION['GO_SESSION']['decimal_separator'], $_SESSION['GO_SESSION']['thousands_separator']);
				$size .= " KB";
				break;

			default :
				$size = number_format($size, $decimals, $_SESSION['GO_SESSION']['decimal_separator'], $_SESSION['GO_SESSION']['thousands_separator']);
				$size .= " bytes";
				break;
		}
		return $size;
	}
}
?>