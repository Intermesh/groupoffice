<?php

namespace GO\Base\Mail;


abstract class ImapBase {

	var $touched_folders =array();

	var $max_read=false;

	var $imap_search_charsets = array(
					'UTF-8',
					'US-ASCII',
					'');

	var $imap_keywords = array(
					'ARRIVAL',    'DATE',    'FROM',      'SUBJECT',
					'CC',         'TO',      'SIZE',      'UNSEEN',
					'SEEN',       'FLAGGED', 'UNFLAGGED', 'ANSWERED',
					'UNANSWERED', 'DELETED', 'UNDELETED', 'TEXT',
					'ALL'
	);
	
	
	var $errors=array();
	
	/*
	 * If we don't no the encoding of a filename header. Use the last charset found
	 * in a part. mb_detect_encoding doesn't work reliable.
	 */
	var $default_charset='';
	
	public $lastCommand;
	
	var $command_count=0;

	public static $debug = false;
	
	
	public function last_error($clear=true){
		$count=count($this->errors);
		//\GO::debug($this->errors);
		if($count){
			
			return $clear ? array_pop($this->errors) : $this->errors[$count-1];
		}else
			return false;
	}

	public function clear_errors(){
		$this->errors=array();
	}


	function input_validate($val, $type) {
		//global $imap_search_charsets;
		//global $imap_keywords;
		$valid = false;
		switch ($type) {
			case 'search_str':
				if (preg_match("/^[^\r\n]+$/", $val)) {
					$valid = true;
				}
				break;
			case 'msg_part':
				if (preg_match("/^[\d\.]+$/", $val)) {
					$valid = true;
				}
				break;
			case 'charset':
				if (!$val || in_array(strtoupper($val), $this->imap_search_charsets)) {
					$valid = true;
				}
				break;
			case 'uid':
				if (preg_match("/^\d+$/", $val)) {
					$valid = true;
				}
				break;
			case 'uid_list';				
				//if (preg_match("/^(\d+\s*,*\s*|(\d+|\*):(\d+|\*))+$/", $val)) {
				if (preg_match("/^(?:\d+:\d+|\*:\d+|\d+:\*|\d+)(?:,(?:\d+:\d+|\*:\d+|\d+:\*|\d+))*$/", $val)) {
					$valid = true;
				}
				break;
			case 'mailbox';
				if (preg_match("/^[^\r\n]+$/", $val)) {
					$valid = true;
				}
				break;
			case 'keyword';
				if (in_array(strtoupper($val), $this->imap_keywords)) {
					$valid = true;
				}
				break;
		}
		return $valid;
	}
	function clean($val, $type) {
		if (!$this->input_validate($val, $type)) {
			throw new \Exception("INVALID IMAP INPUT DETECTED: ".$type.': '.$val);
		}
	}


	/* break up a "line" response from imap. If we find
       a literal we read ahead on the stream and include it.
	*/
	function parse_line($line, $current_size, $max, $line_length) {
		$line = str_replace(')(', ') (', $line);
		$parts = array();
		$line_cont = false;
		while ($line) {
			$chunk = false;
			switch ($line[0]) {
				case "\r":
				case "\n":
					$line = false;
					break;
				case ' ':
					$line = substr($line, 1);
					break;
				case '*':
				case '[':
				case ']':
				case '(':
				case ')':
					$chunk = $line[0];
					$line = strlen($line) > 1 ? substr($line, 1) : '';
					break;
				case '"':
					if (preg_match("/^(\"[^\"\\\]*(?:\\\.[^\"\\\]*)*\")/", $line, $matches)) {
						$chunk = substr($matches[1], 1, -1);
					}
					$line = substr($line, strlen($chunk) + 2);
					break;
				case '{':
					$end = strpos($line, '}');
					if ($end !== false) {
						$literal_size  = substr($line, 1, ($end - 1));
					}
					$lit_result = $this->read_literal($literal_size, $max, $current_size, $line_length);
					$chunk = $lit_result[0];
					if ($lit_result[1]) {
						$line = str_replace(')', ' )', $lit_result[1]);
					}
					else {
						$line_cont = true;
						$line = false;
					}
					break;
				default:
					if (strpos($line, ' ') !== false) {
						$marker = strpos($line, ' ');
						$marker_adjust = $marker;
						$chunk = substr($line, 0, $marker);
						$char_check = substr($chunk, -1);
						$temp_chunk = $chunk;
						while ($temp_chunk && ($char_check == ')' || $char_check == ']')) {
							$marker_adjust--;
							$temp_chunk = substr($temp_chunk, 0, -1);
							$char_check = substr($temp_chunk, -1);
						}
						if ($marker_adjust != $marker) {
							$marker = $marker_adjust;
						}
						$chunk = substr($line, 0, $marker);
						$line = substr($line, strlen($chunk));
					}
					else {
						$chunk = trim($line);
						$line = false;
						$marker = strlen($chunk);
						$marker_adjust = $marker;
						$temp_chunk = trim($chunk);
						$char_check = substr($temp_chunk, -1);
						while ($temp_chunk && ($char_check == ')' || $char_check == ']')) {
							$marker_adjust--;
							$temp_chunk = substr($temp_chunk, 0, -1);
							$char_check = substr($temp_chunk, -1);
						}
						if ($marker_adjust != $marker) {
							$marker = $marker_adjust;
							$line = $chunk;
							$chunk = substr($line, 0, $marker);
							$line = substr($line, strlen($chunk));
						}
					}
					break;
			}
			if (is_string($chunk)) {
				$parts[] = $chunk;
			}
		}
		return array($line_cont, $parts);
	}
	/* Read literal found during parse_line().
	*/
	function read_literal($size, $max, $current, $line_length) {
		$left_over = false;
		$literal_data = fgets($this->handle, $line_length);
		$current += strlen($literal_data);
		while (strlen($literal_data) < $size) {
			$chunk = fgets($this->handle, $line_length);
			$current += strlen($chunk);
			$literal_data .= $chunk;
			if ($max && $current > $max) {
				$this->max_read = true;
				break;
			}
		}
		if ($size < strlen($literal_data)) {
			$left_over = substr($literal_data, $size);
			$literal_data = substr($literal_data, 0, $size);
		}
		return array($literal_data, $left_over);
	}

	function get_response($max=false, $chunked=false, $line_length=8192, $sort=false) {
		$result = array();
		$current_size = 0;
		$chunked_result = array();
		$last_line_cont = false;
		$line_cont = false;
		$c = -1;
		$n = -1;
		do {
			$n++;
			if (!is_resource($this->handle)) {
				break;
			}
			$result[$n] = fgets($this->handle, $line_length);

			if(!$result[$n])
				break;

			$current_size += strlen($result[$n]);
			if ($max && $current_size > $max) {
				$this->max_read = true;
				break;
			}
			while(substr($result[$n], -2) != "\r\n") {
				if (!is_resource($this->handle)) {
					break;
				}
				$result[$n] .= fgets($this->handle, $line_length);
				$current_size += strlen($result[$n]);
				if ($max && $current_size > $max) {
					$this->max_read = true;		
					break 2;
				}
			}
			if ($line_cont) {
				$last_line_cont = true;
				$pres = $n - 1;
				if ($chunks) {
					$pchunk = $c;
				}
			}
			if ($sort) {
				$line_cont = false;
				$chunks = explode(' ', trim($result[$n]));
			}
			else {
				list($line_cont, $chunks) = $this->parse_line($result[$n], $current_size, $max, $line_length);
			}
			if ($chunks && !$last_line_cont) {
				$c++;
			}
			if ($last_line_cont) {
				$result[$pres] .= ' '.implode(' ', $chunks);
				if ($chunks) {
					if (!isset($chunked_result[$pchunk])) {
						$chunked_result[$pchunk] = array();
					}
					$line_bits = array_merge($chunked_result[$pchunk], $chunks);
					$chunked_result[$pchunk] = $line_bits;
				}
				$last_line_cont = false;
			}
			else {
				$result[$n] = join(' ', $chunks);
				if ($chunked) {
					$chunked_result[$c] = $chunks;
				}
			}
			
    } while (substr($result[$n], 0, strlen('A'.$this->command_count.' ')) != 'A'.$this->command_count.' ');
//		https://sourceforge.net/p/group-office/bugs/1711/
//		} while (substr($result[$n], 0, strlen('A'.$this->command_count)) != 'A'.$this->command_count);
		
		$this->responses[] = $result;
		if ($chunked) {
			if(!empty(\GO::session()->values['debugSql']) || self::$debug){
				foreach($chunked_result as $chunks)
					\GO::debug("R: ".implode(" ", $chunks));
			}
			$result = $chunked_result;
		}else
		{
			if(!empty(\GO::session()->values['debugSql']) || self::$debug){
				foreach($result as $line)
					\GO::debug("R: ".$line);
			}
		}

		//\GO::debug($result);
		return $result;
	}
	/* increment the imap command prefix such that it counts
       up on each command sent. ('A1', 'A2', ...) */
	function command_number() {
		$this->command_count += 1;
		return $this->command_count;
	}
	/* put a prefix on a command and send it to the server */
	function send_command($command, $piped=false) {
		if ($piped) {
			$final_command = '';
			foreach ($command as $v) {
				$final_command .= 'A'.$this->command_number().' '.$v;
			}
			$command = $final_command;
		}
		else {
			$command = 'A'.$this->command_number().' '.$command;
		}
		if (!is_resource($this->handle)){
				throw new \Exception("Lost connection to ".$this->server);
		}
		
		$this->lastCommand=$command;

		if(!fputs($this->handle, $command)){
			throw new \Exception("Lost connection to ".$this->server);
//				eturn false;
		}
		

		if(!empty(\GO::session()->values['debugSql']) || self::$debug)
			\GO::debug("S: ".$command);
		
		$this->commands[trim($command)] = \GO\Base\Util\Date::getmicrotime();
	}
	/* determine if an imap response returned an "OK", returns
       true or false */
	function check_response($data, $chunked=false, $trackErrors=true) {

		$result = false;
		if ($chunked) {
			if (!empty($data)) {
				$vals = $data[(count($data) - 1)];
				if ($vals[0] == 'A'.$this->command_count) {
					if (strtoupper($vals[1]) == 'OK') {
						$result = true;
					}
				}
				
				if(!$result && $trackErrors){
					foreach($data as $vals){
						if (strtoupper($vals[1]) != 'OK') {
							$this->errors[]=implode(' ', $vals)."\n\nLast command: ".$this->lastCommand;
						}
					}
				}
			}
		}
		else {
			$line = array_pop($data);
			if (preg_match("/^A".$this->command_count." OK/i", $line)) {
				$result = true;
			}
			if(!$result && $trackErrors){
				
				// Strip command count
				$line = preg_replace("/^A".$this->command_count." NO( \[.*?\])? /i", "", $line);
				$line = preg_replace("/ \(.*\)?/i", "", $line);
				$this->errors[] = $line;
//				$this->errors[]=$line."\n\nLast command: ".$this->lastCommand;
			}
		}	
		
		return $result;
	}

	/*function utf7_decode($string) {
		$string = iconv("UTF-7-IMAP", "UTF-8", $string);
		//$string = mb_convert_encoding($string, "UTF-8", "UTF7-IMAP" );
		return $string;
	}
	function utf7_encode($string) {

		$string = iconv("UTF-8", "UTF-7-IMAP", $string);
		//$string = mb_convert_encoding($string, "UTF7-IMAP", "UTF-8" );
		return $string;
	}*/

	function utf7_decode($str) {
		$Index_64 = array(
						-1,-1,-1,-1, -1,-1,-1,-1, -1,-1,-1,-1, -1,-1,-1,-1,
						-1,-1,-1,-1, -1,-1,-1,-1, -1,-1,-1,-1, -1,-1,-1,-1,
						-1,-1,-1,-1, -1,-1,-1,-1, -1,-1,-1,62, 63,-1,-1,-1,
						52,53,54,55, 56,57,58,59, 60,61,-1,-1, -1,-1,-1,-1,
						-1, 0, 1, 2,  3, 4, 5, 6,  7, 8, 9,10, 11,12,13,14,
						15,16,17,18, 19,20,21,22, 23,24,25,-1, -1,-1,-1,-1,
						-1,26,27,28, 29,30,31,32, 33,34,35,36, 37,38,39,40,
						41,42,43,44, 45,46,47,48, 49,50,51,-1, -1,-1,-1,-1
		);

		$u7len = strlen($str);
		$str = strval($str);
		$p = $err = '';

		for ($i=0; $u7len > 0; $i++, $u7len--) {
			$u7 = $str[$i];
			if ($u7 == '&') {
				$i++;
				$u7len--;
				$u7 = isset($str[$i]) ? $str[$i] : '';

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
					}
					else {
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
						}
						else {
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
				if ($u7len > 2 && $str[$i+1] == '&' && $str[$i+2] != '-')
					return $err;
			}
			/* Not printable US-ASCII */
			else if (ord($u7) < 0x20 || ord($u7) >= 0x7f)
				return $err;
			else
				$p .= $u7;
		}

		return str_replace(array('\\\\','\"'), array('\\','"'), $p);
	}


	/**
	 * Convert the data ($str) from UTF-8 to RFC 2060's UTF-7.
	 * Unicode characters above U+FFFF are replaced by U+FFFE.
	 * If input data is invalid, return an empty string.
	 */
	function utf7_encode($str) {
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
			}
			else if ($c < 0xc2)
				return $err;
			else if ($c < 0xe0) {
				$ch = $c & 0x1f;
				$n = 1;
			}
			else if ($c < 0xf0) {
				$ch = $c & 0x0f;
				$n = 2;
			}
			else if ($c < 0xf8) {
				$ch = $c & 0x07;
				$n = 3;
			}
			else if ($c < 0xfc) {
				$ch = $c & 0x03;
				$n = 4;
			}
			else if ($c < 0xfe) {
				$ch = $c & 0x01;
				$n = 5;
			}
			else
				return $err;

			$i++;
			$u8len--;

			if ($n > $u8len)
				return $err;

			for ($j=0; $j < $n; $j++) {
				$o = ord($str[$i+$j]);
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

		return str_replace(array('\\','"'), array('\\\\','\"'), $p);
	}


	function mime_header_decode($string) {
		return Utils::mimeHeaderDecode($string, $this->default_charset);
	}
}
