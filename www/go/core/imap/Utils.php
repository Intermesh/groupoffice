<?php

namespace go\core\imap;

use go\core\mail\Util;
use go\core\util\StringUtil;


/**
 * IMAP Functions
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Utils {

	/**
	 * Escape a value for an IMAP command
	 *
	 * @param string $str
	 * @return string
	 */
	public static function escape(string $str): string {
		return str_replace(array('\\', '"'), array('\\\\', '\"'), $str);
	}

	/**
	 * Decodes MIME message header extensions that are non ASCII text (see RFC2047).
	 *
	 * {@see http://www.faqs.org/rfcs/rfc2047}
	 *
	 * @param ?string $string
	 * @param string $defaultCharset
	 * @return array|string|string[]
	 */
	public static function mimeHeaderDecode(?string $string, string $defaultCharset='UTF-8') : string {
		return Util::mimeHeaderDecode($string, $defaultCharset);
	}

	/**
	 * Decodes an UTF-7 encoded string into UTF-8
	 *
	 * This function is needed to decode mailbox names that contain certain characters which are not in range of printable ASCII characters.
	 *
	 * @param ?string $str
	 * @return string | false
	 */
	public static function utf7Decode(?string $str) {
		if(empty($str)) {
			return "";
		}
		return mb_convert_encoding($str, "UTF-8", "UTF7-IMAP");
	}

	/**
	 * Encodes an UTF-8 string to UTF-7 encoding
	 *
	 * This function is needed to encode mailbox names that contain certain characters which are not in range of printable ASCII characters.
	 *
	 * @param ?string $str
	 * @return string|false
	 */
	public static function utf7Encode(?string $str) {

		if(empty($str)) {
			return "";
		}

		return mb_convert_encoding($str, "UTF7-IMAP", "UTF-8");
	}

}
