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
 * @version $Id: String.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/**
 * This class contains functions for string operations
 *
 * @copyright Copyright Intermesh
 * @version $Id: String.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.utils
 * @since Group-Office 3.0
 */

class String {

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

	/*
	 * Check if parenthesis are closed properly.
	 */
	public static function check_parentheses($str){
		
		if(preg_match('/SELECT.*FROM/i', $str))
			return false;
		
		if(preg_match('/DELETE.*FROM/i', $str))
			return false;
		
		if(preg_match('/INSERT.*INTO/i', $str))
			return false;
		
		if(preg_match('/update.*set/i', $str))
			return false;

		//remove escaped slashes
		$str = str_replace("\'", "", $str);
		$str = str_replace('\"', "", $str);

		//remove slashed strings
		$str = preg_replace('/"[^"]*"/', '', $str);
		$str = preg_replace("/'[^']*'/", '', $str);

		$opened=0;

		for($i=0,$max=strlen($str);$i<$max;$i++){
			switch($str[$i]){
				case '(':
					$opened++;
				break;

				case ')':
					if($opened>0){
						$opened--;
					}else
					{
						//closing bracket and it wasn't opened. This is invalid
						return false;
					}
					break;
			}
		}

		//opened should be 0 if number of ( matches ).
		return $opened==0;
	}

	public static function escape_javascript($str){
		return strtr($str, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));
	}


	public static function to_utf8($str, $from_charset=''){

		if(empty($str))
			return $str;
		
//		Went wrong on some servers maybe php 5.2 related?
//		if(function_exists('mb_check_encoding') && mb_check_encoding($str,'UTF-8'))
//			return $str;
		
		
		if($from_charset=='UTF-8'){
			return $str;
		}else{

			if(empty($from_charset)){

				/*if(function_exists('mb_detect_encoding'))
				{
					$from_charset = mb_detect_encoding($str, "auto");
				}
				if(empty($from_charset))*/
				$from_charset='windows-1252';
			}
			
			if(substr($from_charset,0,5)=='x-mac'){
				
				global $GO_CONFIG;
				require_once($GO_CONFIG->root_path.'go/base/util/String.php');
				require_once($GO_CONFIG->root_path.'go/base/util/charset/Xmac.php');
				return GO_Base_Util_Charset_Xmac::toUtf8($str, $from_charset);
			}

			return iconv($from_charset, 'UTF-8//IGNORE', $str);
		}
	}

	public static function clean_utf8($str, $source_charset='UTF-8') {
		//echo $source_charset;
		//must use html_entity_decode here other wise some weird utf8 might be decoded later
		//Commented out to prevent XML parse errors on ampersands when used in syncml.
		//    if(strtolower($source_charset)!='ascii')
		//      $str = @html_entity_decode($str, ENT_COMPAT, $source_charset);

		//Does not always work. We suppress the:
		//Notice:  iconv() [function.iconv]: Detected an illegal character in input string in /var/www/community/trunk/www/classes/String.class.inc.php on line 31
		$old_lvl = error_reporting (E_ALL ^ E_NOTICE);
		$c = iconv($source_charset, 'UTF-8//IGNORE', $str);
		error_reporting ($old_lvl);
		
		if(!empty($c))
		{
			$str=$c;
		}else{
			if(function_exists('mb_detect_encoding'))
			{
				$from_charset = mb_detect_encoding($str, "auto");
			}else
			{
				$from_charset = "iso-8859-1";
			}
			if($from_charset!=$source_charset)
				return String::clean_utf8($str, $from_charset);
			else
				return $str;
		}
		
		//Check if preg validates it as UTF8
		if(preg_match('/^.{1}/us', $str)){
			return $str;
		}


		//Not valid still so we are going to validate each utf byte sequence with
		//help from Henri Sivonen http://hsivonen.iki.fi/php-utf8/
		

		$mState = 0;     // cached expected number of octets after the current octet
		// until the beginning of the next UTF8 character sequence
		$mUcs4  = 0;     // cached Unicode character
		$mBytes = 1;     // cached expected number of octets in the current sequence

		$out = '';
		$chr = '';

		$len = strlen($str);

		for($i = 0; $i < $len; $i++) {

			$chr.=$str{$i};

			$in = ord($str{$i});
			if ( $mState == 0) {



			// When mState is zero we expect either a US-ASCII character or a
			// multi-octet sequence.
				if (0 == (0x80 & ($in))) {
				// US-ASCII, pass straight through.
					$mBytes = 1;

					$out .= $chr;

					$chr='';

				} elseif (0xC0 == (0xE0 & ($in))) {
				// First octet of 2 octet sequence
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 0x1F) << 6;
					$mState = 1;
					$mBytes = 2;

				} elseif (0xE0 == (0xF0 & ($in))) {
				// First octet of 3 octet sequence
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 0x0F) << 12;
					$mState = 2;
					$mBytes = 3;

				} elseif (0xF0 == (0xF8 & ($in))) {
				// First octet of 4 octet sequence
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 0x07) << 18;
					$mState = 3;
					$mBytes = 4;

				} elseif (0xF8 == (0xFC & ($in))) {
							 /* First octet of 5 octet sequence.
							 *
							 * This is illegal because the encoded codepoint must be either
							 * (a) not the shortest form or
							 * (b) outside the Unicode range of 0-0x10FFFF.
							 * Rather than trying to resynchronize, we will carry on until the end
							 * of the sequence and let the later error handling code catch it.
							 */
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 0x03) << 24;
					$mState = 4;
					$mBytes = 5;


				} elseif (0xFC == (0xFE & ($in))) {
				// First octet of 6 octet sequence, see comments for 5 octet sequence.
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 1) << 30;
					$mState = 5;
					$mBytes = 6;

				} else {
							 /* Current octet is neither in the US-ASCII range nor a legal first
								* octet of a multi-octet sequence.
								*/
					//return FALSE;
					$out .= '?';

				}

			} else {

			// When mState is non-zero, we expect a continuation of the multi-octet
			// sequence
				if (0x80 == (0xC0 & ($in))) {

				// Legal continuation.
					$shift = ($mState - 1) * 6;
					$tmp = $in;
					$tmp = ($tmp & 0x0000003F) << $shift;
					$mUcs4 |= $tmp;

					/**
					 * End of the multi-octet sequence. mUcs4 now contains the final
					 * Unicode codepoint to be output
					 */
					if (0 == --$mState) {

									 /*
									 * Check for illegal sequences and codepoints.
									 */
					// From Unicode 3.1, non-shortest form is illegal
						if (((2 == $mBytes) && ($mUcs4 < 0x0080)) ||
								((3 == $mBytes) && ($mUcs4 < 0x0800)) ||
								((4 == $mBytes) && ($mUcs4 < 0x10000)) ||
								(4 < $mBytes) ||
								// From Unicode 3.2, surrogate characters are illegal
								(($mUcs4 & 0xFFFFF800) == 0xD800) ||
								// Codepoints outside the Unicode range are illegal
								($mUcs4 > 0x10FFFF)) {

							//return FALSE;
							$out .= '?';

						}else
						{
							//echo $chr."\n";
							$out .= $chr;
						}

						//initialize UTF8 cache
						$mState = 0;
						$mUcs4  = 0;
						$mBytes = 1;
						$chr='';
					}

				} else {
				/**
				 *((0xC0 & (*in) != 0x80) && (mState != 0))
				 * Incomplete multi-octet sequence.
				 */

					//return FALSE;
					$out .= '?';
				}
			}
		}
		
		return $out;
	}

	/**
	 * Replace a string within a string once.
	 *
	 * @param String $search
	 * @param String $replace
	 * @param String $subject
	 * @param bool $found Pass this to check if an occurence was replaced or not
	 * @return String
	 */

	public static function replace_once($search, $replace, $subject, &$found=false) {
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
		if (preg_match("/(\b)([\w\.\-+']+)(@)([\w\.-]+)([a-z]{2,6})\b/i", $email, $matches)) {
			return $matches[0];
		} else {
			return false;
		}
	}

	/*
	 function get_name_from_string($string) {
		if (preg_match('/([\D]*|[\D]*[\040][\D]*)/i', $string, $matches)) {
		$matches[0] = str_replace('"', '', $matches[0]);
		return $matches[0];
		} else {
		return $string;
		}
		}*/

	/**
	 * Adds paramaters to an URL
	 *
	 * @param	StringHelper $url
	 * @param	StringHelper $params
	 * @access public
	 * @return StringHelper
	 */

	public static function add_params_to_url($url, $params) {
		if (strpos($url, '?') === false) {
			$url .= '?'.$params;
		} else {
			$url .= '&amp;'.$params;
		}
		return $url;
	}



	/**
	 * Get's all queries from an SQL dump file in an array
	 *
	 * @param	StringHelper $file The absolute path to the SQL file
	 * @access public
	 * @return array An array of SQL strings
	 */

	public static function get_sql_queries($file) {
		$sql = '';
		$queries = array ();
		if ($handle = fopen($file, "r")) {
			while (!feof($handle)) {
				$buffer = trim(fgets($handle, 4096));
				if ($buffer != '' && substr($buffer, 0, 1) != '#' && substr($buffer, 0, 1) != '-') {
					$sql .= $buffer;
				}
			}
			fclose($handle);
		} else {
			die("Could not read SQL dump file $file!");
		}
		$length = strlen($sql);
		$in_string = false;
		$start = 0;
		$escaped = false;
		for ($i = 0; $i < $length; $i ++) {
			$char = $sql[$i];
			if ($char == '\'' && !$escaped) {
				$in_string = !$in_string;
			}
			if ($char == ';' && !$in_string) {
				$offset = $i - $start;
				$queries[] = substr($sql, $start, $offset);

				$start = $i +1;
			}
			if ($char == '\\') {
				$escaped = true;
			} else {
				$escaped = false;
			}
		}
		return $queries;
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
		$name_arr = explode(' ', $full_name);

		$name['first'] = $full_name;
		$name['middle'] = '';
		$name['last'] = '';
		$count = count($name_arr);
		$last_index = $count -1;
		for ($i = 0; $i < $count; $i ++) {
			switch ($i) {
				case 0 :
					$name['first'] = $name_arr[$i];
					break;

				case $last_index :
					$name['last'] = $name_arr[$i];
					break;

				default :
					$name['middle'] .= $name_arr[$i].' ';
					break;
			}
		}
		$name['middle'] = trim($name['middle']);
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
		return "/^[a-z0-9\._\-+']+@[a-z0-9\.\-_]+\.[a-z]{2,6}$/i";
	}
  
	/**
	 * Check if an email adress is in a valid format
	 *
	 * @param	StringHelper $email E-mail address
	 * @access public
	 * @return bool
	 */
	public static function validate_email($email) {
		return preg_match(String::get_email_validation_regex(), $email);
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

	/**
	 * Return a formatted address string
	 *
	 * @param	array $object User or contact
	 * @access public
	 * @return StringHelper Address formatted
	 */
	public static function address_format($object, $linebreak = '<br />') {
		if (isset ($object['name'])) {
			$name = $object['name'];
		} else {
			$middle_name = $object['middle_name'] == '' ? '' : $object['middle_name'].' ';

			if ($object['title'] != '' && $object['initials'] != '') {
				$name = $object['title'].' '.$object['initials'].' '.$middle_name.$object['last_name'];
			} else {
				$name = $object['first_name'].' '.$middle_name.$object['last_name'];
			}
		}

		$address = $name.$linebreak;

		if ($object['address'] != '') {
			$address .= $object['address'];
			if (isset ($object['address_no'])) {
				$address .= ' '.$object['address_no'];
			}
			$address .= $linebreak;
		}
		if ($object['zip'] != '') {
			$address .= $object['zip'].' ';
		}
		if ($object['city'] != '') {
			$address .= $object['city'].$linebreak;
		}
		if ($object['country'] != '') {
			global $lang;
			require_once($GLOBALS['GO_LANGUAGE']->get_base_language_file('countries'));

			$address .= $countries[$object['country']].$linebreak;
		}
		return $address;

	}


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

		$sort_name = $sort_name == '' ? $_SESSION['GO_SESSION']['sort_name'] : $sort_name;

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

		return $name;
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

	public static function cut_string($string, $maxlength, $cut_whole_words = true) {
		if (strlen($string) > $maxlength) {
			$temp = substr($string, 0, $maxlength -3);
			if ($cut_whole_words) {
				if ($pos = strrpos($temp, ' ')) {
					return substr($temp, 0, $pos).'...';
				} else {
					return $temp = substr($string, 0, $maxlength -3).'...';
				}
			} else {
				return $temp.'...';
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
	 * @return String
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
		global $GO_CONFIG, $GO_MODULES;

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

		$module = $GLOBALS['GO_MODULES']->modules['email'];

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
		global $GO_CONFIG, $GO_MODULES;

		//replace repeating spaces with &nbsp;		
		$text = htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
		$text = str_replace('  ', '&nbsp;&nbsp;', $text);

		if($convert_links)
		{
			$text = preg_replace("/\b(https?:\/\/[\pL0-9\.&\-\/@#;`~=%?:_\+,\)\(]+)\b/ui", '{lt}a href={quot}$1{quot} target={quot}_blank{quot} class={quot}normal-link{quot}{gt}$1{lt}/a{gt}', $text."\n");
			$text = preg_replace("/\b([\pL0-9\._\-]+@[\pL0-9\.\-_]+\.[a-z]{2,4})(\s)/ui", "{lt}a class={quot}normal-link{quot} href={quot}mailto:$1{quot}{gt}$1{lt}/a{gt}$2", $text);
		}

		
		$text = nl2br(trim($text));
		//$text = str_replace("\r", "", $text);
		//$text = str_replace("\n", "", $text);

		//we dont use < and > directly with the preg functions because htmlspecialchars will screw it up. We don't want to use
		//htmlspecialchars before the pcre functions because email address like <mschering@intermesh.nl> will fail.

		$text = str_replace("{quot}", '"', $text);
		$text = str_replace("{lt}", "<", $text);
		$text = str_replace("{gt}", ">", $text);

    // Replace emoticons
    $text = String::text_replace_emoticons($text,true);

    return ($text);
	}


  /**
   * Convert text to emoticons
   *
   * @param StringHelper $string String without emoticons
   * @return StringHelper String with emoticons
   */
  public static function text_replace_emoticons($string, $html=false)
  {
    // Check for smilies to be enabled by the user (settings->Look & Feel-> Show Smilies)
    if(!empty($_SESSION['GO_SESSION']['show_smilies']))
    {

      global $GO_CONFIG;

      $emoticons = array(
          ":@"=>"angry.gif",
          ":d"=>"bigsmile.gif",
          "(brb)"=>"brb.gif",
          //"(o)"=>"clock.gif",
          //"(c)"=>"coffee.gif", //conflicts with copyright
          "(co)"=>"computer.gif",
          ":s"=>"confused.gif",
          ":'("=>"cry.gif",
          ":'|"=>"dissapointed.gif",
          ":^)"=>"dontknow.gif",
          //"(e)"=>"email.gif",
          "+o("=>"ill.gif",
          "(k)"=>"kiss.gif",
          "(l)"=>"love.gif",
          "(mp)"=>"mobile.gif",
          "(mo)"=>"money.gif",
          "(n)"=>"notok.gif",
          "(y)"=>"ok.gif",
          "<o)"=>"party.gif",
          "(g)"=>"present.gif",
          ":("=>"sad.gif",
					":-("=>"sad.gif",
          "^o)"=>"sarcasm.gif",
					"^-o)"=>"sarcasm.gif",
          ":$"=>"shy.gif",
          "|-)"=>"sleepy.gif",
          ":)"=>"smile.gif",
					":-)"=>"smile.gif",
          "(*)"=>"star.gif",
          "(h)"=>"sunglasses.gif",
          ":o"=>"surprised.gif",
					":-o"=>"surprised.gif",
          "(ph)"=>"telephone.gif",
          "*-)"=>"thinking.gif",
          ":p"=>"tongue.gif",
					":-p"=>"tongue.gif",
          ";)"=>"wink.gif",
					";-)"=>"wink.gif",
          );

    
      foreach($emoticons as $emoticon=>$img)
      {

        $imgpath = $GO_CONFIG->full_url.'views/Extjs3/themes/'.$GO_CONFIG->theme.'/images/emoticons/normal/'.$img;
        $imgstring = '<img src="'.$imgpath.'" alt="'.$emoticon.'" />';
        if($html)
          $string = String::html_replace($emoticon, $imgstring, $string);
        else
          $string = preg_replace('/([^a-z0-9])'.preg_quote($emoticon).'([^a-z0-9])/i',"\\1".$imgstring."\\2", $string);
          
      }

    }
    
    return $string;
  }


  public static function html_replace($search, $replacement, $html){
    $html = preg_replace_callback('/<[^>]*('.preg_quote($search).')[^>]*>/uis',array('String', '_replace_in_tags'), $html);
    $html = preg_replace('/([^a-z0-9])'.preg_quote($search).'([^a-z0-9])/i',"\\1".$replacement."\\2", $html);
    
    //$html = str_ireplace($search, $replacement, $html);
    return str_replace('{TEMP}', $search, $html);
  }

  public static function _replace_in_tags($matches)
  {
    return stripslashes(str_replace($matches[1], '{TEMP}', $matches[0]));
  }

	function html_to_text($text, $link_list=true){
		global $GO_CONFIG;
		require_once($GLOBALS['GO_CONFIG']->class_path.'html2text.class.inc');

		$htmlToText = new Html2Text ($text);
		return $htmlToText->get_text($link_list);
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
	public static function convert_html($html) {
		global $GO_CONFIG;

		//needed for very large strings when data is embedded in the html with an img tag
		ini_set('pcre.backtrack_limit', (int)ini_get( 'pcre.backtrack_limit' )+ 1000000 );

		//don't do this because it will mess up <pre></pre> tags
		//$html = str_replace("\r", '', $html);
		//$html = str_replace("\n",' ', $html);

		//remove strange white spaces in tags first
		//sometimes things like this happen <style> </ style >
		
		//Unfortunately some mail clients put html outside the body tags :(
		// so the next code block didn't work.
//		$body_startpos = stripos($html, '<body');
//		$body_endpos = stripos($html, '</body');
//		if($body_startpos){
//			if($body_endpos)
//				$html = substr($html, $body_startpos, $body_endpos-$body_startpos);
//			else
//				$html = substr($html, $body_startpos);
//		}
		
		$html = preg_replace("'</[\s]*([\w]*)[\s]*>'u","</$1>", $html);
		
		$to_removed_array = array (
		"'<!DOCTYPE[^>]*>'usi",
		"'<!--.*-->'Uusi",
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
		
		/* MS Word junk */
		"'<xml[^>]*>.*?</xml>'usi",
		"'<\/?o:[^>]*>'usi",
		"'<\/?v:[^>]*>'usi",
		"'<\/?st1:[^>]*>'usi",
		"'<\?xml[^>]*>'usi",

		"'<style[^>]*>.*?</style>'usi",
		"'<script[^>]*>.*?</script>'usi",
		"'<iframe[^>]*>.*?</iframe>'usi",
		"'<iframe[^>]*>'usi",
		"'<object[^>]*>.*?</object>'usi",
		"'<embed[^>]*>.*?</embed>'usi",
		"'<applet[^>]*>.*?</applet>'usi",
		"'<form[^>]*>'usi",
		"'<input[^>]*>'usi",
		"'<select[^>]*>.*?</select>'usi",
		"'<textarea[^>]*>.*?</textarea>'usi",
		"'</form>'usi"
		);
				//go_debug($html);

		$html = preg_replace($to_removed_array, '', $html);
		$html = String::xss_clean($html);

		//remove high z-indexes
		$matched_tags = array();
		preg_match_all( "/(z-index)[\s]*:[\s]*([0-9]+)[\s]*;/u", $html, $matched_tags, PREG_SET_ORDER );
		foreach ($matched_tags as $tag) {
			if ($tag[2]>8000) {
				$html = str_replace($tag[0],'z-index:8000;',$html);
			}
		}

    // Replace emoticons
    $html = String::text_replace_emoticons($html,true);

		return $html;
	}

	public static function xss_clean($data)
	{
		// Fix &entity\n;
		$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
		$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
		$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
		$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
		
		// Remove any attribute starting with "on" or xmlns
		$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
		// Remove javascript: and vbscript: protocols
//		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
//		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
//		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
		
		$dangerousWords = array('script','expression','behavior');
		foreach($dangerousWords as $word)
			$data = str_ireplace($word,substr($word,0,2).'<b></b>'.substr($word,2),$data);
			
//
//		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
//		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
//		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);

		//the next line removed valid stuff from the body
		//$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

		// Remove namespaced elements (we do not need them)
		//$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

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

	public static function convert_links($html)
	{
		global $GO_CONFIG, $GO_MODULES;

		$html = str_replace("\r", '', $html);
		$html = str_replace("\n",' ', $html);

		$regexp="/<a[^>]*href=([\"']?)(http|https|ftp|bf2)(:\/\/)(.+?)>/i";
		$html = preg_replace($regexp, "<a target=$1_blank$1 class=$1blue$1 href=$1$2$3$4>", $html);

		//$regexp="/<a.+?href=([\"']?)".str_replace('/','\\/', $GLOBALS['GO_CONFIG']->full_url)."(.+?)>/i";
		//$html = preg_replace($regexp, "<a target=$1main$1 class=$1blue$1 href=$1".$GLOBALS['GO_CONFIG']->host."$2$3>", $html);

		$html =str_replace($GLOBALS['GO_CONFIG']->full_url, $GLOBALS['GO_CONFIG']->host, $html);

		if ($GLOBALS['GO_MODULES']->modules['email'] && $GLOBALS['GO_MODULES']->modules['email']['read_permission']) {
			$html = preg_replace("/(href=([\"']?)mailto:)([\w\.\-]+)(@)([\w\.\-\"]+)\b/i",
			"href=\"javascript:this.showComposer({values: {to : '$3$4$5'}});", $html);
		}
		
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
	 * Used by icalendar convertor
	 *
	 * @param unknown_type $sText
	 * @param unknown_type $bEmulate_imap_8bit
	 * @return unknown
	 */

	public static function quoted_printable_encode($sLine,$bEmulate_imap_8bit=false) {

		if(empty($sLine)){
			return $sLine;
		}
	
		$sLine = str_replace("\r", '', $sLine);
		$sLine = str_replace("\n", "\r\n", $sLine);
		
		// split text into lines

			$sRegExp = '/[^\x09\x20\x21-\x3C\x3E-\x7E]/e';

			// imap_8bit encodes x09 everywhere, not only at lineends,
			// for EBCDIC safeness encode !"#$@[\]^`{|}~,
			// for complete safeness encode every character :)
			if ($bEmulate_imap_8bit)
				$sRegExp = '/[^\x20\x21-\x3C\x3E-\x7E]/e';

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
				//$sLine=str_replace(' =0A','=20=0A',$sLine);
				//$sLine=str_replace('=0D ','=0D=20',$sLine);
				//$sLine=str_replace('=0A ','=0A=20',$sLine);
			}

			// finally split into softlines no longer than 76 chars,
			// for even more safeness one could encode x09,x20
			// at the very first character of the line
			// and after soft linebreaks, as well,
			// but this wouldn't be caught by such an easy RegExp
			//preg_match_all( '/.{1,73}([^=]{0,2})?/', $sLine, $aMatch );
			//$sLine = implode( '=' . chr(13).chr(10), $aMatch[0] ); // add soft crlf's
		//}
		return $sLine;
		// join lines into text
		//return implode(chr(13).chr(10),$aLines);
	}

	public static function wrap_quoted_printable_encoded_string($sText, $add_leading_space=false){
		$lb = '='.chr(13).chr(10);
		
		//$lb = chr(10);

		//funambol clients need this to parse the vcard correctly.
		//if($add_leading_space)
			//$lb .= ' ';

		preg_match_all( '/.{1,73}([^=]{0,2})?/', $sText, $aMatch );
		$lines = array_map('trim',$aMatch[0]);
		return implode($lb, $lines); // add soft crlf's
		
	}

	public static function format_vcard_line($name_part, $value_part, $add_leading_space=false, $dont_use_quoted_printable=false)
	{
		//$value_part = str_replace("\r\n","\n", $value_part);

		if($dont_use_quoted_printable){
			//just wrap texts
			$value_part = str_replace("\r",'', $value_part);
			$value_part = str_replace("\n",'\n', $value_part);
			$value_part = wordwrap($value_part, 74, "\n ");
			$name_part .= ';CHARSET=UTF-8:';
			return array($name_part.$value_part);
		}

		$qp_value_part = String::quoted_printable_encode($value_part);

		if($value_part != $qp_value_part || strlen($name_part.$value_part)>=73)
		{
			$name_part .= ";ENCODING=QUOTED-PRINTABLE;CHARSET=UTF-8:";
			//disable wrapping for funambol
			$str = $add_leading_space ? $name_part.$qp_value_part : String::wrap_quoted_printable_encoded_string($name_part.$qp_value_part, $add_leading_space);
			return array($str);
		}else
		{
			$name_part .= ';CHARSET=UTF-8:';
		}
		return array($name_part.$value_part);
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
	static function random_password( $characters_allow = 'a-z,1-9', $characters_disallow = 'i,o', $password_length = 0, $repeat = 0 ) {

		if($password_length==0)
		{
			global $GO_CONFIG;
			$password_length=$GLOBALS['GO_CONFIG']->default_password_length;
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
		$new_key = 0;
		while (list ($key, $val) = each($array_allow)) {
			$array_allow_tmp[$new_key] = $val;
			$new_key ++;
		}

		$array_allow = $array_allow_tmp;
		$password = '';
		while (strlen($password) < $password_length) {
			$character = mt_rand(0, count($array_allow) - 1);

			// If characters are not allowed to repeat,
			// only add character if not found in partial password string.
			if ($repeat == 0) {
				if (substr_count($password, $array_allow[$character]) == 0) {
					$password .= $array_allow[$character];
				}
			} else {
				$password .= $array_allow[$character];
			}
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

		$editedKeys = array_map(array("String", "_addAccolades"), $keys);

		$res = trim(preg_replace('/\s+/', ' ',str_replace($editedKeys, array_values($name),$template)));

		$res = str_replace(array('()','[]'),'', $res);

		return $res;
	}

	function _addAccolades($string)
	{
		return '{'.$string.'}';
	}

}