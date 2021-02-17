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
 * This class contains functions for string operations
 *
 * @copyright Copyright Intermesh
 * @version $Id: StringHelper.php 22467 2018-03-07 08:42:50Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.util
 * @since Group-Office 3.0
 */


namespace GO\Base\Util;


class StringHelper {
	
	/**
	 * Check if the given string is a valid JSON string
	 * 
	 * @param string $string
	 * @return boolean
	 */
	public static function isJSON($string){
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
	
	
	/**
	 * Normalize Carites Return Line Feed
	 * 
	 * @param string $text
	 * @param string $crlf
	 * @return string
	 */
	public static function normalizeCrlf($text, $crlf="\r\n"){		
		return \go\core\util\StringUtil::normalizeCrlf($text, $crlf);
	}
	
	/**
	 * Convert non ascii characters to chars that come close to them.
	 * @param type $string
	 * @return type 
	 */
		public static function utf8ToASCII($string) {

		//cyrillic
//		$cyr = array(
//		"а", "б", "в", "г", "д", "ђ", "е", "ж", "з", "и", "й", "ј", "к", "л", "љ", "м", "н",
//    "њ", "о", "п", "р", "с", "т", "ћ", "у", "ф", "х", "ц", "ч", "џ", "ш","ъ","ы","ь","э","ю","я",
//				
//    "А", "Б", "В", "Г", "Д", "Ђ", "Е", "Ж", "З", "И", "Й", "Ј", "К", "Л", "Љ", "М", "Н",
//    "Њ", "О", "П", "Р", "С", "Т", "Ћ", "У", "Ф", "Х", "Ц", "Ч", "Џ", "Ш","Ъ","Ы","Ь","Э","Ю","Я");
//		
//
//
//    $lat = array ("a", "b", "v", "g", "d", "d", "e", "z", "z", "i", "j", "j", "k", "l", "lj", "m", "n", "nj", "o", "p",
//    "r", "s", "t", "c", "u", "f", "h", "c", "c", "dz", "s","'","Y","'","e","yu","ya",
//				
//    "A", "B", "B", "G", "D", "D", "E", "Z", "Z", "I", "J", "J", "K", "L", "LJ", "M", "N", "NJ", "O", "P",
//    "R", "S", "T", "C", "U", "F", "H", "C", "C", "DZ", "S","'","Y","'","E","Yu","Ya"
//    );
//		$string = str_replace($cyr, $lat, $string);
		
		$rus = array("/а/", "/б/", "/в/",
				"/г/", "/ґ/", "/д/", "/е/", "/ё/", "/ж/",
				"/з/", "/и/", "/й/", "/к/", "/л/", "/м/",
				"/н/", "/о/", "/п/", "/р/", "/с/", "/т/",
				"/у/", "/ф/", "/х/", "/ц/", "/ч/", "/ш/",
				"/щ/", "/ы/", "/э/", "/ю/", "/я/", "/ь/",
				"/ъ/", "/і/", "/ї/", "/є/", "/А/", "/Б/",
				"/В/", "/Г/", "/ґ/", "/Д/", "/Е/", "/Ё/",
				"/Ж/", "/З/", "/И/", "/Й/", "/К/", "/Л/",
				"/М/", "/Н/", "/О/", "/П/", "/Р/", "/С/",
				"/Т/", "/У/", "/Ф/", "/Х/", "/Ц/", "/Ч/",
				"/Ш/", "/Щ/", "/Ы/", "/Э/", "/Ю/", "/Я/",
				"/Ь/", "/Ъ/", "/І/", "/Ї/", "/Є/", "/Ü/", 
				"/ü/", "/Ö/", "/ö/", "/Ä/", "/ä/", "/ß/");
		
		$lat = array("a", "b", "v",
				"g", "g", "d", "e", "e", "zh", 
				"z", "i",	"j", "k", "l", "m", 
				"n", "o", "p", "r",	"s", "t", 
				"u", "f", "h", "c", "ch", "sh",
				"sh'", "y", "e", "yu", "ya", "'", 
				"'", "i",	"i", "e", "A", "B", 
				"V", "G", "G", "D",	"E", "E", 
				"ZH", "Z", "I", "J", "K", "L",
				"M", "N", "O", "P", "R", "S", 
				"T", "U",	"F", "H", "C", "CH",
				"SH", "SH'", "Y", "E","YU", "YA", 
				"'", "'", "I", "I", "E", "Ue",
				"ue", "Oe", "oe", "Ae", "ae", "ss");
		
		$string = preg_replace($rus, $lat, $string);

		$converted = iconv("UTF-8", "US-ASCII//TRANSLIT", $string);
		if(!empty($converted)){
			return $converted;
		}else
		{
			$converted = preg_replace('/[^a-zA-Z0-9 ,-:_]+/','',$string);
			if(!empty($converted)){
				return $converted;
			}else
			{
				throw new \Exception("Could not convert string to ASCII");
			}							
		}
		//return preg_replace('/[^a-zA-Z0-9 ,-:_]+/','',$string);
	}


	public static function get_first_letters($phrase) {

		//remove all non word characters
		$phrase = preg_replace('/[()\[\]\.<>\{\}]+/u','', $phrase);
		$phrase = str_replace(',',' ', $phrase);

		//remove double spaces
		$phrase = preg_replace('/[\s]+/u',' ', $phrase);

		//echo $phrase;

		$words = explode(' ',$phrase);

		$func = function_exists('mb_substr') ? 'mb_substr' : 'substr';
		
		for ($i=0;$i<count($words);$i++) {
			$words[$i] = $func($words[$i],0,1);
		}
		
		return implode('',$words);
	}

	public static function array_to_string($arr){
		$s='';
		foreach($arr as $key=>$value){
			$s .= $key.': '.$value."\n";
		}
		return $s;
	}

	
	public static function escape_javascript($str){
		return strtr($str, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));
	}
	
	/**
	 * Tests if string contains 8bit symbols.
	 *
	 * If charset is not set, function defaults to default_charset.
	 * $default_charset global must be set correctly if $charset is
	 * not used.
	 * @param StringHelper $string tested string
	 * @param StringHelper $charset charset used in a string
	 * @return bool true if 8bit symbols are detected
	 */
	public static function is8bit($string, $charset = 'UTF-8') {
		
		/**
		 * Don't use \240 in ranges. Sometimes RH 7.2 doesn't like it.
		 * Don't use \200-\237 for iso-8859-x charsets. This ranges
		 * stores control symbols in those charsets.
		 * Use preg_match instead of ereg in order to avoid problems
		 * with mbstring overloading
		 */
		if (preg_match("/^iso-8859/i", $charset)) {
			$needle = '/\240|[\241-\377]/';
		} else {
			$needle = '/[\200-\237]|\240|[\241-\377]/';
		}
		return preg_match("$needle", $string);
	}


	public static function to_utf8($str, $from_charset=''){

		if(empty($str))
			return $str;
				
		if(strtoupper($from_charset)=='UTF-8'){
			return $str;
		}else{
			
			//Some mail clients send a different charset while the string is already utf-8 :(
			//
			//This went wrong with UTF-7
			//
//			if(function_exists('mb_check_encoding') && mb_check_encoding($str,'UTF-8'))
//				return $str;

			if(empty($from_charset)){

				/*if(function_exists('mb_detect_encoding'))
				{
					$from_charset = mb_detect_encoding($str, "auto");
				}
				if(empty($from_charset))*/
				$from_charset='windows-1252';
			}
			
			if(substr($from_charset,0,5)=='x-mac')
				return Charset\Xmac::toUtf8($str, $from_charset);
			
			$from_charset = self::fixCharset($from_charset);

			
			return iconv($from_charset, 'UTF-8//IGNORE', $str);
		}
	}
	
/**
	 * Makes charset name suitable for decoding cycles
	 *
	 * ks_c_5601_1987, x-euc-* and x-windows-* charsets are supported
	 * since 1.4.6 and 1.5.1.
	 *
	 * @since 1.4.4 and 1.5.0
	 * @param StringHelper $charset Name of charset
	 * @return StringHelper $charset Adjusted name of charset
	 */
	public static function fixCharset($charset) {
	
		$charset = preg_replace('/win-([0-9]+)/i','windows-$1', $charset);
		
		$charset=strtolower($charset);


		// OE ks_c_5601_1987 > cp949
		$charset = str_replace('ks_c_5601-1987', 'cp949', $charset);
		// Moz x-euc-tw > euc-tw
		$charset = str_replace('x_euc', 'euc', $charset);
		// Moz x-windows-949 > cp949
		$charset = str_replace('x-windows-', 'cp', $charset);

		// windows-125x and cp125x charsets
		$charset = str_replace('windows-', 'cp', $charset);

		// ibm > cp
		$charset = str_replace('ibm', 'cp', $charset);

		// iso-8859-8-i -> iso-8859-8
		// use same cycle until I'll find differences
		$charset = str_replace('iso-8859-8-i', 'iso-8859-8', $charset);

		return $charset;
	}
	
//	public static function stripInvalidUtf8($utf8string){
//		$utf8string = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
//
//			'|(?<=^|[\x00-\x7F])[\x80-\xBF]+'.
//
//			'|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
//
//			'|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
//
//			'|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/',
//
//			'�', $utf8string );
//
//
//		$utf8string = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]'.
//						'|\xED[\xA0-\xBF][\x80-\xBF]/S','?', $utf8string );
//		
//		return $utf8string;
//	}

	public static function clean_utf8($str, $source_charset='UTF-8') {
		
		//must use html_entity_decode here other wise some weird utf8 might be decoded later
		//$str = html_entity_decode($str, ENT_COMPAT, $source_charset);			
		
		//fix incorrect win-1252 to Windows-1252
		$source_charset = preg_replace('/win-([0-9]+)/i','WINDOWS-$1', $source_charset);
		
		//fix for euro signs in windows-1252 encoding. We convert it to iso-8859-15.
		$source_charset=strtoupper($source_charset);
		if($source_charset=='ISO-8859-1' || $source_charset=='ISO-8859-15' || $source_charset=='WINDOWS-1252')
			$str = str_replace("\x80","€", $str);
		
		// UNICODE IS NOT A VALID CHARSET SO WE USE THE UTF-8 
		if($source_charset == 'UNICODE')
			$source_charset = 'UTF-8';
		
		
		
		$str = str_replace("€","&euro;", $str);
		
		$source_charset = self::fixCharset($source_charset);
		try {
			$c = iconv($source_charset, 'UTF-8//IGNORE', $str);
		} catch(\Exception $e) {
			//Does not always work. We suppress the:
			//Notice:  iconv() [function.iconv]: Detected an illegal character in input string in /var/www/community/trunk/www/classes/String.class.inc.php on line 31		
		}
		
		if(!empty($c))
		{
			$str=$c;
		}else{
			if(function_exists('mb_detect_encoding'))
			{
				$from_charset = mb_detect_encoding($str, "auto");
			}else
			{
				$from_charset = "ISO-8859-1";
			}
			$from_charset=strtolower($from_charset);
			
			if($from_charset!=$source_charset)
				$str=self::clean_utf8($str, $from_charset);
		}
							
		//Check if preg validates it as UTF8
		if(function_exists('mb_check_encoding') && mb_check_encoding($str,'utf8')){
			
			return $str;
		}else{
		//remove non utf8. taken from http://stackoverflow.com/questions/1401317/remove-non-utf8-characters-from-string
				$regex = <<<'END'
/
  (
    (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
    |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences   110xxxxx 10xxxxxx
    |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences   1110xxxx 10xxxxxx * 2
    |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence 11110xxx 10xxxxxx * 3 
    ){1,100}                        # ...one or more times
  )
| .                                 # anything else
/x
END;
		
			return preg_replace($regex, '$1', $str);
			
		}
//		//Not valid still so we are going to validate each utf byte sequence with
//		//help from Henri Sivonen http://hsivonen.iki.fi/php-utf8/
//		
//
//		$mState = 0;     // cached expected number of octets after the current octet
//		// until the beginning of the next UTF8 character sequence
//		$mUcs4  = 0;     // cached Unicode character
//		$mBytes = 1;     // cached expected number of octets in the current sequence
//
//		$out = '';
//		$chr = '';
//
//		$len = strlen($str);
//
//		for($i = 0; $i < $len; $i++) {
//
//			$chr.=$str{$i};
//
//			$in = ord($str{$i});
//			if ( $mState == 0) {
//
//
//
//			// When mState is zero we expect either a US-ASCII character or a
//			// multi-octet sequence.
//				if (0 == (0x80 & ($in))) {
//				// US-ASCII, pass straight through.
//					$mBytes = 1;
//
//					$out .= $chr;
//
//					$chr='';
//
//				} elseif (0xC0 == (0xE0 & ($in))) {
//				// First octet of 2 octet sequence
//					$mUcs4 = ($in);
//					$mUcs4 = ($mUcs4 & 0x1F) << 6;
//					$mState = 1;
//					$mBytes = 2;
//
//				} elseif (0xE0 == (0xF0 & ($in))) {
//				// First octet of 3 octet sequence
//					$mUcs4 = ($in);
//					$mUcs4 = ($mUcs4 & 0x0F) << 12;
//					$mState = 2;
//					$mBytes = 3;
//
//				} elseif (0xF0 == (0xF8 & ($in))) {
//				// First octet of 4 octet sequence
//					$mUcs4 = ($in);
//					$mUcs4 = ($mUcs4 & 0x07) << 18;
//					$mState = 3;
//					$mBytes = 4;
//
//				} elseif (0xF8 == (0xFC & ($in))) {
//							 /* First octet of 5 octet sequence.
//							 *
//							 * This is illegal because the encoded codepoint must be either
//							 * (a) not the shortest form or
//							 * (b) outside the Unicode range of 0-0x10FFFF.
//							 * Rather than trying to resynchronize, we will carry on until the end
//							 * of the sequence and let the later error handling code catch it.
//							 */
//					$mUcs4 = ($in);
//					$mUcs4 = ($mUcs4 & 0x03) << 24;
//					$mState = 4;
//					$mBytes = 5;
//
//
//				} elseif (0xFC == (0xFE & ($in))) {
//				// First octet of 6 octet sequence, see comments for 5 octet sequence.
//					$mUcs4 = ($in);
//					$mUcs4 = ($mUcs4 & 1) << 30;
//					$mState = 5;
//					$mBytes = 6;
//
//				} else {
//							 /* Current octet is neither in the US-ASCII range nor a legal first
//								* octet of a multi-octet sequence.
//								*/
//					//return FALSE;
//					$out .= '?';
//
//				}
//
//			} else {
//
//			// When mState is non-zero, we expect a continuation of the multi-octet
//			// sequence
//				if (0x80 == (0xC0 & ($in))) {
//
//				// Legal continuation.
//					$shift = ($mState - 1) * 6;
//					$tmp = $in;
//					$tmp = ($tmp & 0x0000003F) << $shift;
//					$mUcs4 |= $tmp;
//
//					/**
//					 * End of the multi-octet sequence. mUcs4 now contains the final
//					 * Unicode codepoint to be output
//					 */
//					if (0 == --$mState) {
//
//									 /*
//									 * Check for illegal sequences and codepoints.
//									 */
//					// From Unicode 3.1, non-shortest form is illegal
//						if (((2 == $mBytes) && ($mUcs4 < 0x0080)) ||
//								((3 == $mBytes) && ($mUcs4 < 0x0800)) ||
//								((4 == $mBytes) && ($mUcs4 < 0x10000)) ||
//								(4 < $mBytes) ||
//								// From Unicode 3.2, surrogate characters are illegal
//								(($mUcs4 & 0xFFFFF800) == 0xD800) ||
//								// Codepoints outside the Unicode range are illegal
//								($mUcs4 > 0x10FFFF)) {
//
//							//return FALSE;
//							$out .= '?';
//
//						}else
//						{
//							//echo $chr."\n";
//							$out .= $chr;
//						}
//
//						//initialize UTF8 cache
//						$mState = 0;
//						$mUcs4  = 0;
//						$mBytes = 1;
//						$chr='';
//					}
//
//				} else {
//				/**
//				 *((0xC0 & (*in) != 0x80) && (mState != 0))
//				 * Incomplete multi-octet sequence.
//				 */
//
//					//return FALSE;
//					$out .= '?';
//				}
//			}
//		}
//		
//		return $out;
	}
	
	/**
	 * Check if string has UTF8 characters
	 * 
	 * @param StringHelper $str
	 * @return boolean
	 */
	public static function isUtf8($str){
		return strlen($str) != strlen(\utf8_decode($str));
	}

	/**
	 * Replace a string within a string once.
	 *
	 * @param StringHelper $search
	 * @param StringHelper $replace
	 * @param StringHelper $subject
	 * @param bool $found Pass this to check if an occurence was replaced or not
	 * @return StringHelper
	 */

	public static function replaceOnce($search, $replace, $subject, &$found=false) {
		$firstChar = strpos($subject, $search);
		if($firstChar !== false) {
			$found=true;
			$beforeStr = substr($subject,0,$firstChar);
			$afterStr = substr($subject, $firstChar + strlen($search));
			return $beforeStr.$replace.$afterStr;
		} else {
			$found=false;
			return $subject;
		}
	}

	/**
	 * Reverse strpos. couldn't get PHP strrpos to work with offset
	 *
	 * @param StringHelper $haystack
	 * @param StringHelper $needle
	 * @param int $offset
	 * @return int
	 */
	public static function rstrpos ($haystack, $needle, $offset=0)
	{
		$size = strlen ($haystack);
		$pos = strpos (strrev($haystack), strrev($needle), $size - $offset);

		if ($pos === false)
		return false;

		return $size - $pos - strlen($needle);
	}

	public static function trim_lines($text)
	{
		str_replace("\r\n","\n", $text);

		$trimmed='';

		$lines = explode("\n", $text);
		foreach($lines as $line)
		{
			if($trimmed=='')
			{
				$trimmed .= $line."\n";
			}elseif(empty($line))
			{
				$trimmed .= "\n";
			}elseif($line[0]!=' ')
			{
				return $text;
			}else{
				$trimmed .= substr($line,1)."\n";
			}
		}

		return $trimmed;
	}



	/**
	 * Grab an e-mail address out of a string
	 *
	 * @param	int $level The log level. See sys_log() of the PHP docs
	 * @param	StringHelper $message The log message
	 * @access public
	 * @return void
	 */
	public static function get_email_from_string($email) {
		if (preg_match("/(\b)([\w\.\-]+)(@)([\w\.-]+)([A-Za-z]{2,4})\b/i", $email, $matches)) {
			return $matches[0];
		} else {
			return false;
		}
	}

	/**
	 * Grab all e-mail addresses out of a string
	 *
	 * @param	int $level The log level. See sys_log() of the PHP docs
	 * @param	StringHelper $message The log message
	 * @access public
	 * @return void
	 */
	public static function get_emails_from_string($emails) {
		if (preg_match_all("/(\b)([\w\.\-]+)(@)([\w\.-]+)([A-Za-z]{2,4})\b/i", $emails, $matches)) {
			return $matches[0];
		} else {
			return false;
		}
	}

	/**
	 * Return only the contents of the body tag from a HTML page
	 *
	 * @param	StringHelper $html A HTML formatted string
	 * @access public
	 * @return StringHelper HTML formated string
	 */

	public static function get_html_body($html) {
		$to_removed_array = array ("'<html[^>]*>'si", "'</html>'si", "'<body[^>]*>'si", "'</body>'si", "'<head[^>]*>.*?</head>'si", "'<style[^>]*>.*?</style>'si", "'<object[^>]*>.*?</object>'si",);

		//$html = str_replace("\r", "", $html);
		//$html = str_replace("\n", "", $html);

		$html = preg_replace($to_removed_array, '', $html);
		return $html;

	}


	/**
	 * Give it a full name and it tries to determine the First, Middle and Lastname
	 *
	 * @param	StringHelper $full_name A full name
	 * @access public
	 * @return array array with keys first, middle and last
	 */

	public static function split_name($full_name) {
		if (strpos($full_name,',')) {
			
			$parts = explode(',',$full_name);
			$full_name = implode(' ',array_reverse($parts));			
		} 
		
		$full_name = trim(preg_replace("/[\s]+/", " ", $full_name));
		
		$name_arr = explode(' ', $full_name);

		$name['first_name'] = $full_name;
		$name['middle_name'] = '';
		$name['last_name'] = '';
		$count = count($name_arr);
		$last_index = $count -1;
		for ($i = 0; $i < $count; $i ++) {
			switch ($i) {
				case 0 :
					$name['first_name'] = $name_arr[$i];
					break;

				case $last_index :
					$name['last_name'] = $name_arr[$i];
					break;

				default :
					$name['middle_name'] .= $name_arr[$i].' ';
					break;
			}
		}
		$name['middle_name'] = trim($name['middle_name']);
		
		return $name;
	}

	/**
	 * Get the regex used for validating an email address
	 * Requires the Top Level Domain to be between 2 and 6 alphanumeric chars
	 *
	 * @param	none
	 * @access	public
	 * @return	StringHelper
	 */
	public static function get_email_validation_regex() {
		return \go\core\mail\Util::EMAIL_REGEX;
		//return "/^[_a-z0-9\-+\&\']+(\.[_a-z0-9\-+\&\']+)*@[a-z0-9\-]+(\.[a-z0-9\-]+)*(\.[a-z]{2,100})$/i";
	}


	/**
	 * Check if an email adress is in a valid format
	 *
	 * @param	StringHelper $email E-mail address
	 * @deprecated since version 4.1
	 * @return bool
	 */
	public static function validate_email($email) {
		return \go\core\mail\Util::validateEmail($email);
	}

	/**
	 * Checks for empty string and returns stripe when empty
	 *
	 * @param	StringHelper $input Any string
	 * @access public
	 * @return StringHelper
	 */
	public static function empty_to_stripe($input) {
		if ($input == "") {
			return "-";
		} else {
			return $input;
		}
	}

//	/**
//	 * Return a formatted address string
//	 *
//	 * @param	array $object User or contact
//	 * @access public
//	 * @return string Address formatted
//	 */
//	public static function address_format($object, $linebreak = '<br />') {
//		if (isset ($object['name'])) {
//			$name = $object['name'];
//		} else {
//			$middle_name = $object['middle_name'] == '' ? '' : $object['middle_name'].' ';
//
//			if ($object['title'] != '' && $object['initials'] != '') {
//				$name = $object['title'].' '.$object['initials'].' '.$middle_name.$object['last_name'];
//			} else {
//				$name = $object['first_name'].' '.$middle_name.$object['last_name'];
//			}
//		}
//
//		$address = $name.$linebreak;
//
//		if ($object['address'] != '') {
//			$address .= $object['address'];
//			if (isset ($object['address_no'])) {
//				$address .= ' '.$object['address_no'];
//			}
//			$address .= $linebreak;
//		}
//		if ($object['zip'] != '') {
//			$address .= $object['zip'].' ';
//		}
//		if ($object['city'] != '') {
//			$address .= $object['city'].$linebreak;
//		}
//		if ($object['country'] != '') {
//			global $lang;
//			require_once($GLOBALS['GO_LANGUAGE']->get_base_language_file('countries'));
//
//			$address .= $countries[$object['country']].$linebreak;
//		}
//		return $address;
//
//	}


	/**
	 * Formats a name in Group-Office
	 *
	 * @param StringHelper $sort_name string Vlaue can be last_name or first_name
	 * @return StringHelper base64 encoded string
	 */
	public static function format_name($last, $first = '', $middle = '', $sort_name='') {

		if(is_array($last))
		{
			$first = isset($last['first_name']) ? $last['first_name'] : '';
			$middle = isset($last['middle_name']) ? $last['middle_name'] : '';
			$last = isset($last['last_name']) ? $last['last_name'] : '';
		}
		if(\GO::user())
			$sort_name = $sort_name == '' ? \GO::user()->sort_name : $sort_name;
		else
			$sort_name ='first_name';

		if ($sort_name== 'last_name') {
			$name = 	!empty ($last) ? $last : '';
			if(!empty($last) && !empty($first))
			{
				$name .= ', ';
			}
			$name .= !empty ($first) ? $first : '';
			$name .= !empty ($middle) ? ' '.$middle : '';
		} else {
			$name = !empty ($first) ? $first : ' ';
			$name .= !empty ($middle) ? ' '.$middle.' ' : ' ';
			$name .= $last;
		}

		return trim($name);
	}


	/**
	 * Chop long strings with 3 dots
	 *
	 * Chops of the string after a given length and puts three dots behind it
	 * function editted by Tyler Gee to make it chop at whole words
	 *
	 * @param	StringHelper $string The string to chop
	 * @param	int $maxlength The maximum number of characters in the string
	 * @access public
	 * @return StringHelper
	 */

	public static function cut_string($string, $maxlength, $cut_whole_words = true, $append='...') {
		if (strlen($string) > $maxlength) {
			
			$substrFunc = function_exists('mb_substr') ? 'mb_substr' : 'substr';
			
			$maxlength -= strlen($append);
			
			$temp = $substrFunc($string, 0, $maxlength);
			if ($cut_whole_words) {
				if ($pos = strrpos($temp, ' ')) {
					return $substrFunc($temp, 0, $pos).$append;
				} else {
					return $temp = $substrFunc($string, 0, $maxlength).$append;
				}
			} else {
				return $temp.$append;
			}

		} else {
			return $string;
		}
	}

	/**
	 * Trim plain text to a maximum number of lines
	 *
	 * @param $string
	 * @param $maxlines
	 * @return StringHelper
	 */
	public static function limit_lines($string,$maxlines)
	{
		$string = str_replace("\r", '', $string);
		$lines = explode("\n", $string, $maxlines);
		$new_string =  implode("\n", $lines);

		if(strlen($new_string)<strlen($string))
		{
			$new_string .= "\n...";
		}
		return $new_string;
	}





	/**
	 * Convert an enriched formated string to HTML format
	 *
	 * @param	StringHelper $enriched Enriched formatted string
	 * @access public
	 * @return StringHelper HTML formated string
	 */
	public static function enriched_to_html($enriched, $convert_links=true) {

		// We add space at the beginning and end of the string as it will
		// make some regular expression checks later much easier (so we
		// don't have to worry about start/end of line characters)
		$enriched = ' '.$enriched.' ';

		// Get color parameters into a more useable format.
		$enriched = preg_replace('/<color><param>([\da-fA-F]+),([\da-fA-F]+),([\da-fA-F]+)<\/param>/Uis', '<color r=\1 g=\2 b=\3>', $enriched);
		$enriched = preg_replace('/<color><param>(red|blue|green|yellow|cyan|magenta|black|white)<\/param>/Uis', '<color n=\1>', $enriched);

		// Get font family parameters into a more useable format.
		$enriched = preg_replace('/<fontfamily><param>(\w+)<\/param>/Uis', '<fontfamily f=\1>', $enriched);

		// Single line breaks become spaces, double line breaks are a
		// real break. This needs to do <nofill> tracking to be
		// compliant but we don't want to deal with state at this
		// time, so we fake it some day we should rewrite this to
		// handle <nofill> correctly.
		$enriched = preg_replace('/([^\n])\r\n([^\r])/', '\1 \2', $enriched);
		$enriched = preg_replace('/(\r\n)\r\n/', '\1', $enriched);

		// We try to protect against bad stuff here.
		$enriched = @ htmlspecialchars($enriched, ENT_QUOTES);

		// Now convert the known tags to html. Try to remove any tag
		// parameters to stop people from trying to pull a fast one
		$enriched = preg_replace('/(?<!&lt;)&lt;bold.*&gt;(.*)&lt;\/bold&gt;/Uis', '<span style="font-weight: bold">\1</span>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;italic.*&gt;(.*)&lt;\/italic&gt;/Uis', '<span style="font-style: italic">\1</span>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;underline.*&gt;(.*)&lt;\/underline&gt;/Uis', '<span style="text-decoration: underline">\1</span>', $enriched);
		$enriched = preg_replace_callback('/(?<!&lt;)&lt;color r=([\da-fA-F]+) g=([\da-fA-F]+) b=([\da-fA-F]+)&gt;(.*)&lt;\/color&gt;/Uis', create_function('$colors',
		'for ($i = 1; $i < 4; $i ++) {
			$colors[$i] = sprintf(\'%02X\', round(hexdec($colors[$i]) / 255));
		}
		return \'<span style="color: #\'.$colors[1].$colors[2].$colors[3].\'">\'.$colors[4].\'</span>\';'), $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;color n=(red|blue|green|yellow|cyan|magenta|black|white)&gt;(.*)&lt;\/color&gt;/Uis', '<span style="color: \1">\2</span>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;fontfamily&gt;(.*)&lt;\/fontfamily&gt;/Uis', '\1', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;fontfamily f=(\w+)&gt;(.*)&lt;\/fontfamily&gt;/Uis', '<span style="font-family: \1">\2</span>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;smaller.*&gt;/Uis', '<span style="font-size: smaller">', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;\/smaller&gt;/Uis', '</span>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;bigger.*&gt;/Uis', '<span style="font-size: larger">', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;\/bigger&gt;/Uis', '</span>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;fixed.*&gt;(.*)&lt;\/fixed&gt;/Uis', '<font face="fixed">\1</font>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;center.*&gt;(.*)&lt;\/center&gt;/Uis', '<div align="center">\1</div>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;flushleft.*&gt;(.*)&lt;\/flushleft&gt;/Uis', '<div align="left">\1</div>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;flushright.*&gt;(.*)&lt;\/flushright&gt;/Uis', '<div align="right">\1</div>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;flushboth.*&gt;(.*)&lt;\/flushboth&gt;/Uis', '<div align="justify">\1</div>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;paraindent.*&gt;(.*)&lt;\/paraindent&gt;/Uis', '<blockquote>\1</blockquote>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;excerpt.*&gt;(.*)&lt;\/excerpt&gt;/Uis', '<blockquote>\1</blockquote>', $enriched);

		// Now we remove the leading/trailing space we added at the
		// start.
		$enriched = preg_replace('/^ (.*) $/s', '\1', $enriched);

		if($convert_links)
		{
			$enriched = preg_replace("/(?:^|\b)(((http(s?):\/\/)|(www\.-))([\w\.-]+)([,:;%#&\/?=\w+\.\-@]+))(?:\b|$)/is", "<a href=\"http$4://$5$6$7\" target=\"_blank\" class=\"blue\">$1</a>", $enriched);
			$enriched = preg_replace("/(\A|\s)([\w\.\-]+)(@)([\w\.-]+)([A-Za-z]{2,4})\b/i", "\\1<a href=\"mailto:\\2\\3\\4\\5\" class=\"blue\">\\2\\3\\4\\5</a>", $enriched);
		}

		$enriched = nl2br($enriched);
		$enriched = str_replace("\r", "", $enriched);
		$enriched = str_replace("\n", "", $enriched);

		return $enriched;

	}


	/**
	 * Convert plain text to HTML
	 *
	 * @param	StringHelper $text Plain text string
	 * @access public
	 * @return StringHelper HTML formatted string
	 */
	public static function text_to_html($text, $convert_links=true) {
	
		if($convert_links)
		{
			$text = preg_replace("/\b(https?:\/\/[\pL0-9\.&\-\/@#;`~=%?:_\+,\)\(]+)\b/ui", '{lt}a href={quot}$1{quot} target={quot}_blank{quot} class={quot}normal-link{quot}{gt}$1{lt}/a{gt}', $text."\n");
			$text = preg_replace("/\b([\pL0-9\._\-]+@[\pL0-9\.\-_]+\.[a-z]{2,4})(\s)/ui", "{lt}a class={quot}normal-link{quot} href={quot}mailto:$1{quot}{gt}$1{lt}/a{gt}$2", $text);
		}

		//replace repeating spaces with &nbsp;		
		$text = htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
		$text = str_replace('  ', '&nbsp;&nbsp;', $text);

		
		$text = nl2br(trim($text));
		//$text = str_replace("\r", "", $text);
		//$text = str_replace("\n", "", $text);

		//we dont use < and > directly with the preg functions because htmlspecialchars will screw it up. We don't want to use
		//htmlspecialchars before the pcre functions because email address like <mschering@intermesh.nl> will fail.

		$text = str_replace("{quot}", '"', $text);
		$text = str_replace("{lt}", "<", $text);
		$text = str_replace("{gt}", ">", $text);

		return $text;
	}

	public static function html_to_text($text, $link_list=true){

		$htmlToText = new Html2Text ($text);
		return $htmlToText->get_text($link_list);
	}

	private static function extractStyles($html) {

		preg_match_all("'<style[^>]*>(.*?)</style>'usi", $html, $matches);
		$css = "";
		for($i = 0, $l = count($matches[0]); $i < $l; $i++) {

			//don't add the style added by group-office inline because it will double up.
			if(!strstr($matches[0][$i], 'groupoffice-email-style')) {
				$css .= $matches[1][$i] . "\n\n";
			}
		}

		return self::prefixCSSSelectors($css);
	}

	private static function prefixCSSSelectors($css, $prefix = '.go-html-formatted') {
		# Wipe all block comments
		$css = preg_replace('!/\*.*?\*/!s', '', $css);

		$parts = explode('}', $css);
		$mediaQueryStarted = false;

		foreach($parts as &$part)
		{
			$part = trim($part); # Wht not trim immediately .. ?
			if(empty($part)) continue;
			else # This else is also required
			{
				$partDetails = explode('{', $part);
				if(substr_count($part, "{")==2)
				{
					$mediaQuery = $partDetails[0]."{";
					$partDetails[0] = $partDetails[1];
					$mediaQueryStarted = true;
				}

				$subParts = explode(',', $partDetails[0]);
				foreach($subParts as &$subPart)
				{
					if(trim($subPart)==="@font-face") continue;
					else $subPart = $prefix . ' ' . trim($subPart);
				}

				if(substr_count($part,"{")==2)
				{
					$part = $mediaQuery."\n".implode(', ', $subParts)."{".$partDetails[2];
				}
				elseif(empty($part[0]) && $mediaQueryStarted)
				{
					$mediaQueryStarted = false;
					$part = implode(', ', $subParts)."{".$partDetails[2]."}\n"; //finish media query
				}
				else
				{
					if(isset($partDetails[1]))
					{   # Sometimes, without this check,
						# there is an error-notice, we don't need that..
						$part = implode(', ', $subParts)."{".$partDetails[1];
					}
				}

				unset($partDetails, $mediaQuery, $subParts); # Kill those three ..
			}   unset($part); # Kill this one as well
		}

		# Finish with the whole new prefixed string/file in one line
		return(preg_replace('/\s+/',' ',implode("} ", $parts)));

	}

	/**
	 * Convert Dangerous HTML to safe HTML for display inside of Group-Office
	 *
	 * This also removes everything outside the body and replaces mailto links
	 *
	 * @param	StringHelper $text Plain text string
	 * @access public
	 * @return StringHelper HTML formatted string
	 */
	public static function sanitizeHtml($html) {
	
		//needed for very large strings when data is embedded in the html with an img tag
		ini_set('pcre.backtrack_limit', (int)ini_get( 'pcre.backtrack_limit' )+ 1000000 );

		//don't do this because it will mess up <pre></pre> tags
		//$html = str_replace("\r", '', $html);
		//$html = str_replace("\n",' ', $html);

		//remove strange white spaces in tags first
		//sometimes things like this happen <style> </ style >
		
		
//		Doesn't work well because some mails hav body tags all over the place :(
//		$body_startpos = stripos($html, '<body');
//		$body_endpos = strripos($html, '</body');
//		if($body_startpos){
//			if($body_endpos)
//				$html = substr($html, $body_startpos, $body_endpos-$body_startpos);
//			else
//				$html = substr($html, $body_startpos);
//		}

		$styles = self::extractStyles($html);
		
		$html = preg_replace("'</[\s]*([\w]*)[\s]*>'u","</$1>", $html);
		
		$to_removed_array = array (
		"'<!DOCTYPE[^>]*>'usi",
		"'<html[^>]*>'usi",
		"'</html>'usi",
		"'<body[^>]*>'usi",
		"'</body>'usi",
		"'<meta[^>]*>'usi",
		"'<link[^>]*>'usi",
		"'<title>.*?</title>'usi",
		"'<head[^>]*>.*?</head>'usi",
		"'<head[^>]*>'usi",
		"'<base[^>]*>'usi",
		"'<meta[^>]*>'usi",
		"'<bgsound[^>]*>'usi",
		
		/* MS Word junk */
		"'<xml[^>]*>.*?</xml>'usi",
		"'<\/?o:[^>]*>'usi",
		"'<\/?v:[^>]*>'usi",
		"'<\/?st1:[^>]*>'usi",
		"'<\?xml[^>]*>'usi",

		"'<style[^>]*>.*?</style>'usi",
		"'<script[^>]*>.*?</script>'usi",
		"'<iframe[^>]*>.*?</iframe>'usi",
		"'<object[^>]*>.*?</object>'usi",
		"'<embed[^>]*>.*?</embed>'usi",
		"'<applet[^>]*>.*?</applet>'usi",
		"'<form[^>]*>'usi",
		//"'<input[^>]*>'usi",
		//"'<select[^>]*>.*?</select>'usi",
		//"'<textarea[^>]*>.*?</textarea>'usi",
		"'</form>'usi",
		"'<!--.*-->'Uusi",
		);

		$html = preg_replace($to_removed_array, '', $html);
		
		//Remove any attribute starting with "on" or xmlns. Had to do this always becuase many mails contain weird tags like online="1". 
		//These were detected as xss attacks by detectXSS().
		$html = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[a-z]+[^>]*+>#iu', '$1>', $html);
	
		//remove high z-indexes
		$matched_tags = array();
		preg_match_all( "/(z-index)[\s]*:[\s]*([0-9]+)[\s]*/u", $html, $matched_tags, PREG_SET_ORDER );
		foreach ($matched_tags as $tag) {
			if ($tag[2]>8000) {
				$html = str_replace($tag[0],'z-index:8000',$html);
			}
		}
		
		// Check for smilies to be enabled by the user (settings->Look & Feel-> Show Smilies)
		if(\GO::user() && \GO::user()->show_smilies)
			$html = StringHelper::replaceEmoticons($html,true);

		return "<style>" . $styles . '</style>' . $html;
	}
	
	
	public static function encodeHtml($str) {
		
		if(is_array($str)){
			return array_map(array("\GO\Base\Util\StringHelper", "encodeHtml"),$str);
		}
		
		if(!is_string($str)){
			return $str;
		}
		return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');		
	}
	
	/**
	 * Convert text to emoticons
	 *
	 * @param StringHelper $string String without emoticons
	 * @return StringHelper String with emoticons
	 */
	public static function replaceEmoticons($string, $html = false) {		
		$emoticons = array(
//				":@" => "angry.gif",
//				":d" => "bigsmile.gif",
//				"(brb)" => "brb.gif",
				//"(o)"=>"clock.gif",
				//"(c)"=>"coffee.gif", //conflicts with copyright
//				"(co)" => "computer.gif",
//				":s" => "confused.gif",
//				":'(" => "cry.gif",
//				":'|" => "dissapointed.gif",
//				":^)" => "dontknow.gif",
				//"(e)"=>"email.gif",
//				"+o(" => "ill.gif",
//				"(k)" => "kiss.gif",
//				"(l)" => "love.gif",
//				"(mp)" => "mobile.gif",
//				"(mo)" => "money.gif",
//				"(n)" => "notok.gif",
//				"(y)" => "ok.gif",
//				"<o)" => "party.gif",
//				"(g)" => "present.gif",
				":(" => "sad.gif",
				":-(" => "sad.gif",
//				"^o)" => "sarcasm.gif",
//				"^-o)" => "sarcasm.gif",
//				":$" => "shy.gif",
//				"|-)" => "sleepy.gif",
				":)" => "smile.gif",
				":-)" => "smile.gif",
//				"(*)" => "star.gif",
//				"(h)" => "sunglasses.gif",
//				":o" => "surprised.gif",
//				":-o" => "surprised.gif",
//				"(ph)" => "telephone.gif",
//				"*-)" => "thinking.gif",
				":p" => "tongue.gif",
				":-p" => "tongue.gif",
				";)" => "wink.gif",
				";-)" => "wink.gif",
		);

		foreach ($emoticons as $emoticon => $img) {
			$rel = 'views/Extjs3/themes/' . \GO::user()->theme . '/img/emoticons/normal/' . $img;
			if(!file_exists(\GO::config()->root_path.$rel)) {
				$rel = 'views/Extjs3/themes/Paper/img/emoticons/normal/' . $img;
			}
			
			$imgpath = \GO::config()->host . $rel;
			$imgstring = '<img src="' . $imgpath . '" alt="' . $emoticon . '" />';
			if ($html)
				$string = StringHelper::htmlReplace($emoticon, $imgstring, $string);
			else
				$string = preg_replace('/([^a-z0-9])' . preg_quote($emoticon) . '([^a-z0-9])/i', "\\1" . $imgstring . "\\2", $string);
		}
		return $string;
	}
	
	/**
	 * Replace string in html. It will leave strings inside html tags alone.
	 * 
	 * @param StringHelper $search
	 * @param StringHelper $replacement
	 * @param StringHelper $html
	 * @return StringHelper 
	 */
	public static function htmlReplace($search, $replacement, $html){
    $html = preg_replace_callback('/<[^>]*('.preg_quote($search).')[^>]*>/uis',array('GO\Base\Util\StringHelper', '_replaceInTags'), $html);
    $html = preg_replace('/([^a-z0-9])'.preg_quote($search).'([^a-z0-9])/i',"\\1".$replacement."\\2", $html);
    
    //$html = str_ireplace($search, $replacement, $html);
    return str_replace('{TEMP}', $search, $html);
  }

	/**
	 * Private callback function for htmlReplace.
	 * 
	 * @param type $matches
	 * @return type 
	 */
  public static function _replaceInTags($matches)
  {
    return stripslashes(str_replace($matches[1], '{TEMP}', $matches[0]));
  }
	
	/**
	 * Detect known XSS attacks.
	 * 
	 * @param boolean $string
	 * @return boolean
	 * @throws Exception 
	 */
	public static function detectXSS($string) {
		
		if (!is_string($string)) {
			\GO::debug($string);
			throw new \Exception('Passed parameter is not a string.');
		}

// Keep a copy of the original string before cleaning up
		$orig = $string;

// URL decode
		$string = urldecode($string);

// Convert Hexadecimals
//		$string = preg_replace('!(&#|\\\)[xX]([0-9a-fA-F]+);?!e', 'chr(hexdec("$2"))', $string);		
		$string = preg_replace_callback('!(&#|\\\)[xX]([0-9a-fA-F]+);?!', function ($matches) {return chr(hexdec($matches[2]));}, $string);

// Clean up entities
		$string = preg_replace('!(&#0+[0-9]+)!', '$1;', $string);

// Decode entities
		$string = html_entity_decode($string, ENT_NOQUOTES, 'UTF-8');

// Strip whitespace characters
		$string = preg_replace('!\s!', '', $string);

// Set the patterns we'll test against
		$patterns = array(
// Match any attribute starting with "on" or xmlns
				//'#(<[^>]+[\x00-\x20\"\'\/])(on|xmlns)[^>]*>?#iUu',
				'#(<[^>]+[\s])(on|xmlns)[^>]*>?#iUu',
// Match javascript:, livescript:, vbscript: and mocha: protocols
				'!((java|live|vb)script|mocha):(\w)*!iUu',
				'#-moz-binding[\x00-\x20]*:#u',
// Match style attributes
				'#(<[^>]*+[\x00-\x20\"\'\/])*style=[^>]*(expression|behavior)[^>]*>?#iUu',
// Match unneeded tags
				'#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)\s[^>]*>?#i'
		);

		foreach ($patterns as $pattern) {
// Test both the original string and clean string
			if (preg_match($pattern, $string, $matches) || preg_match($pattern, $orig, $matches)){
				\GO::debug("XSS pattern matched: ".$pattern);
				//\GO::debug($matches);
				return true;			
			}
		}

		return false;
	}

	/**
	 * Filter possible XSS attacks
	 * 
	 * @param StringHelper $data;
	 * @return StringHelper
	 */
	public static function filterXSS($data)
	{
		//echo $data; exit();
		// Fix &entity\n;
		$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
		$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
		$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
		$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
		
		// Remove any attribute starting with "on" or xmlns
		$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[a-z]+[^>]*+>#iu', '$1>', $data);
		// Remove javascript: and vbscript: protocols
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
				
//
//		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
//	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '', $data);
//	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '', $data);

		//the next line removed valid stuff from the body
		//$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

		// Remove namespaced elements (we do not need them)
		$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

		return $data;
	}
	
	/**
	 * Change HTML links to Group-Office links. For example mailto: links will call
	 * the Group-Office e-mail module if installed.
	 *
	 *
	 * @param	StringHelper $text Plain text string
	 * @access public
	 * @return StringHelper HTML formatted string
	 */

	public static function convertLinks($html)
	{
		$baseUrl = '';
		if(preg_match('/base href="([^"]+)"/', $html, $matches)){
			if(isset($matches[1]))
			{
				$baseUrl = $matches[1];
			}
		}

		
//		Don't strip new lines or it will mess up <pre> tags
//		$html = str_replace("\r", '', $html);
//		$html = str_replace("\n",' ', $html);
//		
		//strip line breaks inside html tags
		$html = preg_replace_callback('/<[^>]+>/sm',function($matches){
			$replacement = str_replace("\r", '', $matches[0]);
			return str_replace("\n",'  ', $replacement);
		}, $html);

//		$regexp="/<a[^>]*href=\s*([\"']?)(http|https|ftp|bf2)(:\/\/)(.+?)>/i";
		//$html = preg_replace($regexp, "<a target=$1_blank$1 class=$1blue$1 href=$1$2$3$4>", $html);

		$html = str_replace('<a ', '<a target="_blank" ', $html);

		if(!empty($baseUrl)){
			$regexp="/href=\s*('|\")(?![a-z]+:)/i";
			$html = preg_replace($regexp, "href=$1".$baseUrl, $html);
		}

		//$regexp="/<a.+?href=([\"']?)".str_replace('/','\\/', \GO::config()->full_url)."(.+?)>/i";
		//$html = preg_replace($regexp, "<a target=$1main$1 class=$1blue$1 href=$1".\GO::config()->host."$2$3>", $html);

		//Following line breaks links on mobile phones
		//$html =str_replace(\GO::config()->full_url, \GO::config()->host, $html);
		
		return $html;
	}


	/**
	 * Quotes a string with >
	 *
	 * @param	StringHelper $text
	 * @access public
	 * @return StringHelper A string quoted with >
	 */
	public static function quote($text) {
		$text = "> ".ereg_replace("\n", "\n> ", trim($text));
		return ($text);
	}

	/**
	 * This function generates a randomized password.
	 *
	 * @access static
	 *
	 * @param StringHelper $characters_allow
	 * @param StringHelper $characters_disallow
	 * @param int $password_length
	 * @param int $repeat
	 *
	 * @return StringHelper
	 */
	static function randomPassword($password_length = 0, $characters_allow = 'a-z,1-9', $characters_disallow = 'i,o' ) {

		if($password_length==0)
		{
			$password_length=\GO::config()->default_password_length;
		}

		// Generate array of allowable characters.
		$characters_allow = explode(',', $characters_allow);

		for ($i = 0; $i < count($characters_allow); $i ++) {
			if (substr_count($characters_allow[$i], '-') > 0) {
				$character_range = explode('-', $characters_allow[$i]);

				for ($j = ord($character_range[0]); $j <= ord($character_range[1]); $j ++) {
					$array_allow[] = chr($j);
				}
			} else {
				$array_allow[] = $characters_allow[$i];
			}
		}
		
		// Generate array of disallowed characters.
		$characters_disallow = explode(',', $characters_disallow);

		for ($i = 0; $i < count($characters_disallow); $i ++) {
			if (substr_count($characters_disallow[$i], '-') > 0) {
				$character_range = explode('-', $characters_disallow[$i]);

				for ($j = ord($character_range[0]); $j <= ord($character_range[1]); $j ++) {
					$array_disallow[] = chr($j);
				}
			} else {
				$array_disallow[] = $characters_disallow[$i];
			}
		}

		mt_srand(( double ) microtime() * 1000000);

		// Generate array of allowed characters by removing disallowed
		// characters from array.
		$array_allow = array_diff($array_allow, $array_disallow);
		// Resets the keys since they won't be consecutive after
		// removing the disallowed characters.
		reset($array_allow);
    $array_allow = array_values($array_allow);
		
		$password = '';
		while (strlen($password) < $password_length) {
			$character = mt_rand(0, count($array_allow) - 1);

			// If characters are not allowed to repeat,
			// only add character if not found in partial password string.
//			if ($repeat == 0) {
				if (substr_count($password, $array_allow[$character]) == 0) {
					$password .= $array_allow[$character];
				}
//			} else {
//				$password .= $array_allow[$character];
//			}
		}
		return $password;
	}

/*

	function quoted_printable_encode($sText,$bEmulate_imap_8bit=false) {
		// split text into lines

		$sText = str_replace("\r", '', $sText);

		$aLines=explode("\n",$sText);

		//var_dump($aLines);

		for ($i=0;$i<count($aLines);$i++) {
			$sLine =& $aLines[$i];
			if (strlen($sLine)===0) continue; // do nothing, if empty

			$sRegExp = '/[^\x09\x20\x21-\x3C\x3E-\x7E]/e';

			// imap_8bit encodes x09 everywhere, not only at lineends,
			// for EBCDIC safeness encode !"#$@[\]^`{|}~,
			// for complete safeness encode every character :)
			if ($bEmulate_imap_8bit)
			$sRegExp = '/[^\x21-\x3C\x3E-\x7E]/e';

			$sReplmt = 'sprintf( "=%02X", ord ( "$0" ) ) ;';
			$sLine = preg_replace( $sRegExp, $sReplmt, $sLine );

			// encode x09,x20 at lineends
			{
				$iLength = strlen($sLine);
				$iLastChar = ord($sLine{$iLength-1});

				//              !!!!!!!!
				// imap_8_bit does not encode x20 at the very end of a text,
				// here is, where I don't agree with imap_8_bit,
				// please correct me, if I'm wrong,
				// or comment next line for RFC2045 conformance, if you like
				if (!($bEmulate_imap_8bit && ($i==count($aLines)-1)))

				if (($iLastChar==0x09)||($iLastChar==0x20)) {
					$sLine{$iLength-1}='=';
					$sLine .= ($iLastChar==0x09)?'09':'20';
				}
			}    // imap_8bit encodes x20 before chr(13), too
			// although IMHO not requested by RFC2045, why not do it safer :)
			// and why not encode any x20 around chr(10) or chr(13)
			if ($bEmulate_imap_8bit) {
				$sLine=str_replace(' =0D','=20=0D',$sLine);
				$sLine=str_replace(' =0A','=20=0A',$sLine);
				$sLine=str_replace('=0D ','=0D=20',$sLine);
				$sLine=str_replace('=0A ','=0A=20',$sLine);
			}

			//merijn$sLine  = str_replace(' ','=20',$sLine);

			// finally split into softlines no longer than 76 chars,
			// for even more safeness one could encode x09,x20
			// at the very first character of the line
			// and after soft linebreaks, as well,
			// but this wouldn't be caught by such an easy RegExp

			//preg_match_all( '/.{1,73}([^=]{0,2})?/', $sLine, $aMatch );
			//$sLine = implode( '=' . chr(13).chr(10), $aMatch[0] ); // add soft crlf's
		}

		// join lines into text
		return implode('=0D=0A',$aLines);
		//return implode(chr(13).chr(10),$aLines);
	}
*/

	/**
	 * This function generates the view with a template
	 *
	 * @access static
	 *
	 * @param StringHelper $template
	 * @param StringHelper $objectarray
	 *
	 * @return $objectarray
	 */
	static function reformat_name_template($template, $name)
	{
		$keys = array_keys($name);

		$editedKeys = array_map(array("GO\Base\Util\String", "_addAccolades"), $keys);

		$res = trim(preg_replace('/\s+/', ' ',str_replace($editedKeys, array_values($name),$template)));

		$res = str_replace(array('()','[]'),'', $res);

		return $res;
	}

	static protected function _addAccolades($string)
	{
		return '{'.$string.'}';
	}
	
	/**
	 * Check the length of a string. Works with UTF8 too.
	 * 
	 * @param StringHelper $str
	 * @return int 
	 */
	public static function length($str){
		return function_exists("mb_strlen") ? mb_strlen($str, 'UTF-8') : strlen($str);
	}
	
	public static function substr($string, $start, $length=null){
		return function_exists("mb_substr") ? mb_substr($string, $start, $length) : substr($string, $start, $length);
	}
	
	
	/**
	 * Encode an url but leave the forward slashes alone
	 * 
	 * @param StringHelper $str
	 * @return StringHelper
	 */
	public static function rawurlencodeWithourSlash($str){
		$parts = explode('/', $str);
		
		$parts = array_map('rawurlencode', $parts);
		
		return implode('/', $parts);
	}
	
		/**
	 * Replace linebreaks with the given char
	 * 
	 * @param string $text
	 * @param string $replacement
	 * @return string
	 */
	public static function convertLineBreaks($text,$replacement=";"){

		// replace the linebreak (\r\n OR \n) to the replacement char
		$text = str_replace(array("\r\n","\n"),$replacement, $text);
		
		// Check if the replace action did not place the replacement twice after each other.
		// If so, then replace it with only a single replacement char.
		$doubleReplacement = $replacement.$replacement;
		$text = str_replace($doubleReplacement,$replacement, $text);

		return $text;
	}
	

}
