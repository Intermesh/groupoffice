<?php
namespace go\core\mail;

use go\core\util\StringUtil;

class Util {
	
	/**
	 * Allow any char @ any char
	 */
	const EMAIL_REGEX = "/^[^@\s]*@[^@\s]*$/";
	
	/**
	 * Check if given email address is formatted correctly.
	 * @param string $email
	 * @return boolean
	 */
	public static function validateEmail($email) {
		return preg_match(self::EMAIL_REGEX, $email);
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
	public static function mimeHeaderDecode(?string $string, string $defaultCharset = 'UTF-8'): string
	{
		/*
		 * (=?ISO-8859-1?Q?a?= =?ISO-8859-1?Q?b?=)     (ab)
		 *  White space between adjacent 'encoded-word's is not displayed.
		 *
		 *  http://www.faqs.org/rfcs/rfc2047.html
		 */
		$string = preg_replace("/\?=\s*=\?/", "?==?", $string);

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
				} elseif (strtoupper($encoding) == 'Q') {
					$fld = quoted_printable_decode($fld);
				}
				$fld = StringUtil::cleanUtf8($fld, $charset);

				$string = str_replace($v, $fld, $string);
			}
		} elseif (preg_match('/([^=]*)\'\'(.*)/', $string, $matches)) { //check pos for not being to great
			//eg. iso-8859-1''%66%6F%73%73%2D%69%74%2D%73%6D%61%6C%6C%2E%67%69%66
			$charset = $matches[1];
			$string = rawurldecode($matches[2]);

			$string = StringUtil::cleanUtf8($string, $charset);
		} else {
			$string = StringUtil::cleanUtf8($string, $defaultCharset);
		}

		return str_replace(array('\\\\', '\\(', '\\)'), array('\\', '(', ')'), $string);
	}
}
