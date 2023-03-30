<?php

namespace go\core\imap;

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
		/*
		 * (=?ISO-8859-1?Q?a?= =?ISO-8859-1?Q?b?=)     (ab)
		 *  White space between adjacent 'encoded-word's is not displayed.
		 *
		 *  http://www.faqs.org/rfcs/rfc2047.html
		 */
		$string = preg_replace("/\?=[\s]*=\?/","?==?", $string);

		if (preg_match_all("/(=\?[^?]+\?([qb])\?(?!\?=).+\?=)/iU", $string, $matches)) {
			foreach ($matches[1] as $v) {
				$fld = substr($v, 2, -2);
				$charset = strtolower(substr($fld, 0, strpos($fld, '?')));
				$fld = substr($fld, (strlen($charset) + 1));
				$encoding = $fld[0];
				$fld = substr($fld, (strpos($fld, '?') + 1));
				$fld = str_replace('_', '=20', $fld);
				if (strtoupper($encoding) == 'B') {
					$fld = base64_decode($fld);
				}
				elseif (strtoupper($encoding) == 'Q') {
					$fld = quoted_printable_decode($fld);
				}
				$fld = StringUtil::cleanUtf8($fld, $charset);

				$string = str_replace($v, $fld, $string);
			}
		}	elseif(preg_match('/([^=]*)\'\'(.*)/', $string, $matches)){ //check pos for not being to great
			//eg. iso-8859-1''%66%6F%73%73%2D%69%74%2D%73%6D%61%6C%6C%2E%67%69%66
			$charset = $matches[1];
			$string = rawurldecode($matches[2]);

			$string = StringUtil::cleanUtf8($string, $charset);
		}else
		{
			$string = StringUtil::cleanUtf8($string, $defaultCharset);
		}

		return str_replace(array('\\\\', '\\(', '\\)'), array('\\','(', ')'), $string);
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
