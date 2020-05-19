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
	 * @param string$str
	 * @param string
	 */
	public static function escape($str) {
		return str_replace(array('\\', '"'), array('\\\\', '\"'), $str);
	}

	/**
	 * Decodes MIME message header extensions that are non ASCII text (see RFC2047).
	 * 
	 * {@see http://www.faqs.org/rfcs/rfc2047}
	 * 
	 * @param string $string
	 * @param string $defaultCharset
	 * @param string
	 */
	public static function mimeHeaderDecode($string, $defaultCharset = 'UTF-8') {
		/*
		 * (=?ISO-8859-1?Q?a?= =?ISO-8859-1?Q?b?=)     (ab)
		 *  White space between adjacent 'encoded-word's is not displayed.
		 *
		 *  http://www.faqs.org/rfcs/rfc2047.html
		 */
		
		//probably an error in header parsing and not needed anymore.
//		$string = preg_replace("/\?=[\s]*=\?/", "?==?", $string);

		if (preg_match_all("/(=\?[^\?]+\?(q|b)\?[^\?]+\?=)/i", $string, $matches)) {
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
		} elseif (($pos = strpos($string, "''")) && $pos < 64) { //check pos for not being to great
			//eg. iso-8859-1''%66%6F%73%73%2D%69%74%2D%73%6D%61%6C%6C%2E%67%69%66
			$charset = substr($string, 0, $pos);

//			throw new \Exception($charset.' : '.substr($string, $pos+2));
			$string = rawurldecode(substr($string, $pos + 2));

			$string = StringUtil::cleanUtf8($string, $charset);
		} else {
			$string = StringUtil::cleanUtf8($string, $defaultCharset);
		}

		return str_replace(array('\\\\', '\\(', '\\)'), array('\\', '(', ')'), $string);
	}

	/**
	 * Decodes an UTF-7 encoded string into UTF-8
	 * 
	 * This function is needed to decode mailbox names that contain certain characters which are not in range of printable ASCII characters.
	 * 
	 * @param string $str
	 * @param string
	 */
	public static function utf7Decode($str) {

		return mb_convert_encoding($str, "UTF-8", "UTF7-IMAP");
		$Index_64 = array(
			-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
			-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
			-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 62, 63, -1, -1, -1,
			52, 53, 54, 55, 56, 57, 58, 59, 60, 61, -1, -1, -1, -1, -1, -1,
			-1, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14,
			15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, -1, -1, -1, -1, -1,
			-1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40,
			41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, -1, -1, -1, -1, -1
		);

		$u7len = strlen($str);
		$str = strval($str);
		$p = $err = '';

		for ($i = 0; $u7len > 0; $i++, $u7len--) {
			$u7 = $str[$i];
			if ($u7 == '&') {
				$i++;
				$u7len--;
				$u7 = $str[$i];

				if ($u7len && $u7 == '-') {
					$p .= '&';
					continue;
				}

				$ch = 0;
				$k = 10;
				for (; $u7len > 0; $i++, $u7len--) {
					$u7 = $str[$i];

					if ((ord($u7) & 0x80) || ($b = $Index_64[ord($u7)]) == -1)
						break;

					if ($k > 0) {
						$ch |= $b << $k;
						$k -= 6;
					} else {
						$ch |= $b >> (-$k);
						if ($ch < 0x80) {
							/* Printable US-ASCII */
							if (0x20 <= $ch && $ch < 0x7f)
								return $err;
							$p .= chr($ch);
						}
						else if ($ch < 0x800) {
							$p .= chr(0xc0 | ($ch >> 6));
							$p .= chr(0x80 | ($ch & 0x3f));
						} else {
							$p .= chr(0xe0 | ($ch >> 12));
							$p .= chr(0x80 | (($ch >> 6) & 0x3f));
							$p .= chr(0x80 | ($ch & 0x3f));
						}

						$ch = ($b << (16 + $k)) & 0xffff;
						$k += 10;
					}
				}

				/* Non-zero or too many extra bits */
				if ($ch || $k < 6)
					return $err;

				/* BASE64 not properly terminated */
				if (!$u7len || $u7 != '-')
					return $err;

				/* Adjacent BASE64 sections */
				if ($u7len > 2 && $str[$i + 1] == '&' && $str[$i + 2] != '-')
					return $err;
			}
			/* Not printable US-ASCII */
			else if (ord($u7) < 0x20 || ord($u7) >= 0x7f)
				return $err;
			else
				$p .= $u7;
		}

		return $p;
	}

	/**
	 * Encodes an UTF-8 string to UTF-7 encoding
	 * 
	 * This function is needed to encode mailbox names that contain certain characters which are not in range of printable ASCII characters.
	 * 
	 * @param string $str
	 * @param string
	 */
	public static function utf7Encode($str) {

		return mb_convert_encoding($str, "UTF7-IMAP", "UTF-8");

		$B64Chars = array(
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
			'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd',
			'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's',
			't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7',
			'8', '9', '+', ','
		);

		$u8len = strlen($str);
		$base64 = $i = 0;
		$p = $err = '';

		while ($u8len) {
			$u8 = $str[$i];
			$c = ord($u8);

			if ($c < 0x80) {
				$ch = $c;
				$n = 0;
			} else if ($c < 0xc2)
				return $err;
			else if ($c < 0xe0) {
				$ch = $c & 0x1f;
				$n = 1;
			} else if ($c < 0xf0) {
				$ch = $c & 0x0f;
				$n = 2;
			} else if ($c < 0xf8) {
				$ch = $c & 0x07;
				$n = 3;
			} else if ($c < 0xfc) {
				$ch = $c & 0x03;
				$n = 4;
			} else if ($c < 0xfe) {
				$ch = $c & 0x01;
				$n = 5;
			} else
				return $err;

			$i++;
			$u8len--;

			if ($n > $u8len)
				return $err;

			for ($j = 0; $j < $n; $j++) {
				$o = ord($str[$i + $j]);
				if (($o & 0xc0) != 0x80)
					return $err;
				$ch = ($ch << 6) | ($o & 0x3f);
			}

			if ($n > 1 && !($ch >> ($n * 5 + 1)))
				return $err;

			$i += $n;
			$u8len -= $n;

			if ($ch < 0x20 || $ch >= 0x7f) {
				if (!$base64) {
					$p .= '&';
					$base64 = 1;
					$b = 0;
					$k = 10;
				}
				if ($ch & ~0xffff)
					$ch = 0xfffe;

				$p .= $B64Chars[($b | $ch >> $k)];
				$k -= 6;
				for (; $k >= 0; $k -= 6)
					$p .= $B64Chars[(($ch >> $k) & 0x3f)];

				$b = ($ch << (-$k)) & 0x3f;
				$k += 16;
			}
			else {
				if ($base64) {
					if ($k > 10)
						$p .= $B64Chars[$b];
					$p .= '-';
					$base64 = 0;
				}

				$p .= chr($ch);
				if (chr($ch) == '&')
					$p .= '-';
			}
		}

		if ($base64) {
			if ($k > 10)
				$p .= $B64Chars[$b];
			$p .= '-';
		}

		return $p;
	}

}
