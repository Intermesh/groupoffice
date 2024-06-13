<?php
/**
 * Copyright Intermesh BV
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * @copyright Copyright Intermesh
 * @version $Id: Date.php 8255 2011-10-06 15:19:06Z mschering $
 * @package GO.base.util
 */

/**
 * Replace text in HTML but leave HTML tags alone.
 * It can also be used to highlight keywords.
 *
 * @copyright Copyright Intermesh
 * @version $Id: Date.php 8255 2011-10-06 15:19:06Z mschering $
 * @package GO.base.util
 * @since Group-Office 3.0
 */


namespace GO\Base\Util;


class HtmlReplacer {
	
	private static function _replaceInTags($matches) {
		return stripslashes(str_replace($matches[1], '{TEMP}', $matches[0]));
	}

	/**
	 * Replace or highlight text in an HTML document and leave HTML tags alone.
	 * 
	 * @param string $text
	 * @param string $keyword
	 * @param string $replacement Can contain the tag {keyword} for a backreference
	 * @return string 
	 */
	public static function replace($keyword, $replacement, $text) {

		if (substr($keyword, 0, 1) == '*') {
			$keyword = substr($keyword, 1);
			$begin_boundary = '';
		} else {
			$begin_boundary = '\\b';
		}

		if (substr($keyword, -1) == '*') {
			$keyword = substr($keyword, 0, -1);
			$end_boundary = '';
		} else {
			$end_boundary = '\\b';
		}

		$text = preg_replace_callback('/<[^>]*(' . $keyword . ')[^>]*>/uis', array('GO\Base\Util\HtmlReplacer', '_replaceInTags'), $text);
		
		$regex = "/" . $begin_boundary . "(" . $keyword . ")" . $end_boundary . "/sui";

		$replacements = explode('{keyword}', $replacement);
		
		if(count($replacements)>1)
			$text = preg_replace($regex, $replacements[0] . '\\1' . $replacements[1], $text);
		else
			$text = preg_replace($regex, $replacement, $text);
			
		$text = str_replace('{TEMP}', $keyword, $text);

		return $text;
	}

}
