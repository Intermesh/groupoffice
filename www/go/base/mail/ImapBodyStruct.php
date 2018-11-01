<?php


/* parsing routines for the imap bodstructure response */

namespace GO\Base\Mail;


class ImapBodyStruct extends ImapBase {
	function update_part_num($part) {
		if (!strstr($part, '.')) {
			$part++;
		}
		else {
			$parts = explode('.', $part);
			$parts[(count($parts) - 1)]++;
			$part = implode('.', $parts);
		}
		return $part;
	}
	function parse_single_part($array) {
		$vals = $array[0];
		
		array_shift($vals);
		array_pop($vals);
		$atts = array('name', 'type', 'subtype', 'charset', 'id', 'description', 'encoding',
						'size', 'lines', 'md5', 'disposition', 'language', 'location');
		$res = array();
		if (count($vals) > 7) {
			$res['type'] = strtolower(trim(array_shift($vals)));
			$res['subtype'] = strtolower(trim(array_shift($vals)));
			if ($vals[0] == '(') {
				array_shift($vals);
				$break=false;
				while($vals[0] != ')') {
					if (isset($vals[0]) && isset($vals[1])) {

						$key = strtolower($vals[0]);
						
						if($key==='name') //Only take first part for filename
							$break=true;
						
						$starpos=strpos($key, '*');
						if($starpos){
							$key = substr($key, 0, $starpos);
							if(!isset($res[$key]))
								$res[$key]='';
							if(!$break) //dont append when key = name
								$res[$key].= $vals[1];
						}else
						{
							$res[$key] = $vals[1];
						}
						$vals = array_splice($vals, 2);
					}
				}
				array_shift($vals);
			}
			else {
				array_shift($vals);
			}
			$res['id'] = array_shift($vals);
			$res['description'] = array_shift($vals);
			$res['encoding'] = strtolower(array_shift($vals));
			$res['size'] = array_shift($vals);
			if ($res['type'] == 'text' && isset($vals[0])) {
				$res['lines'] = array_shift($vals);
			}
			if (isset($vals[0]) && $vals[0] != ')') {
				$res['md5'] = array_shift($vals);
			}
			if (isset($vals[0]) && $vals[0] == '(') {
				array_shift($vals);
			}
			if (isset($vals[0]) && $vals[0] != ')') {
				$res['disposition'] = array_shift($vals);

				if ((strtolower($res['disposition']) == 'attachment' || strtolower($res['disposition']) == 'inline') && $vals[0] == '(') {
					array_shift($vals);

					$res['filename']='';

					while (isset($vals[0]) && strtolower(substr($vals[0],0,8)) == 'filename' && isset($vals[1]) && $vals[1] != ')') {
						array_shift($vals);
						$res['filename'] .= array_shift($vals);
						if ($vals[0] == ')') {
							array_shift($vals);
						}
					}
				}
			}

			if(empty($res['name']) && !empty($res['filename']))
				$res['name']=$res['filename'];
			
			if (isset($vals[0]) && $vals[0] != ')') {
				$res['language'] = array_shift($vals);
			}
			if (isset($vals[0]) && $vals[0] != ')') {
				$res['location'] = array_shift($vals);
			}
			foreach ($atts as $v) {
				if (!isset($res[$v]) || trim(strtoupper($res[$v])) == 'NIL') {
					$res[$v] = false;
				}
				else {
					if ($v == 'charset') {
						$res[$v] = strtolower(trim($res[$v]));
						
						//get a default charset to decode filenames of attachments that don't have
						//that value
//						if(empty($this->default_charset) && !empty($res[$v])){
//							$this->default_charset = $res[$v];
//						}
					}
					else {
						$res[$v] = trim($res[$v]);
					}
				}
//if ($v=='filename') var_dump($res);
			}
			if (!isset($res['name'])) {
				$res['name'] = 'message';
			}
		}
		return $res;
	}



	function filter_alternatives($struct, $filter, $parent_type=false, $cnt=0) {
		$filtered = array();
		if (!is_array($struct) || empty($struct)) {
			return array($filtered, $cnt);
		}
		if (!$parent_type) {
			if (isset($struct['subtype'])) {
				$parent_type = $struct['subtype'];
			}
		}
		foreach ($struct as $index => $value) {
			if ($parent_type == 'alternative' && isset($value['subtype']) && $value['subtype'] != $filter) {
				$cnt += 1;
			}
			else {
				$filtered[$index] = $value;
			}
			if (isset($value['subs']) && is_array($value['subs'])) {
				if (isset($struct['subtype'])) {
					$parent_type = $struct['subtype'];
				}
				else {
					$parent_type = false;
				}
				list($filtered[$index]['subs'], $cnt) = $this->filter_alternatives($value['subs'], $filter, $parent_type, $cnt);
			}
		}
		return array($filtered, $cnt);
	}
	function parse_multi_part($array, $part_num, $run_num) {
		$struct = array();
		$index = 0;
		foreach ($array as $vals) {
			if ($vals[0] != '(') {
				break;
			}
			$type = strtolower($vals[1]);
			$sub = strtolower($vals[2]);
			$part_type = 1;
			
//			switch ($type) {
//				case 'message':
//					switch ($sub) {
//						case 'delivery-status':
//						case 'external-body':
//						case 'disposition-notification':
//						case 'rfc822-headers':
//							break;
//						default:
//							$part_type = 2;
//							break;
//					}
//					break;
//			}
			if ($vals[0] == '(' && $vals[1] == '(') {
				$part_type = 3;
			}
			if ($part_type == 1) {
				$struct[$part_num] = $this->parse_single_part(array($vals));
				$part_num = $this->update_part_num($part_num);
			}
			elseif ($part_type == 2) {
				$parts = $this->split_toplevel_result($vals);
				$struct[$part_num] = $this->parse_rfc822($parts[0], $part_num);
				$part_num = $this->update_part_num($part_num);
			}
			else {
				$parts = $this->split_toplevel_result($vals);
				$struct[$part_num]['subs'] = $this->parse_multi_part($parts, $part_num.'.1', $part_num);
				$part_num = $this->update_part_num($part_num);
			}
			$index++;
		}
		if (isset($array[$index][0])) {
			$struct['type'] = 'message';
			$struct['subtype'] = $array[$index][0];
		}
		return $struct;
	}
	function parse_rfc822($array, $part_num) {
		$res = array();
		array_shift($array);
		$res['type'] = strtolower(trim(array_shift($array)));
		$res['subtype'] = strtolower(trim(array_shift($array)));
		if ($array[0] == '(') {
			array_shift($array);
			while($array[0] != ')') {
				if (isset($array[0]) && isset($array[1])) {
					$res[strtolower($array[0])] = $array[1];
					$array = array_splice($array, 2);
				}
			}
			array_shift($array);
		}
		else {
			array_shift($array);
		}
		$res['id'] = array_shift($array);
		$res['description'] = array_shift($array);
		$res['encoding'] = strtolower(array_shift($array));
		$res['size'] = array_shift($array);
		$envelope = array();
		if ($array[0] == '(') {
			array_shift($array);
			$index = 0;
			$level = 1;
			foreach ($array as $i => $v) {
				if ($level == 0) {
					$index = $i;
					break;
				}
				$envelope[] = $v;
				if ($v == '(') {
					$level++;
				}
				if ($v == ')') {
					$level--;
				}
			}
			if ($index) {
				$array = array_splice($array, $index);
			}
		}
		$res = $this->parse_envelope($envelope, $res);
		$parts = $this->split_toplevel_result($array);
		$res['subs'] = $this->parse_multi_part($parts, $part_num.'.1', $part_num);
		return $res;
	}
	function split_toplevel_result($array) {
		if (empty($array) || $array[1] != '(') {
			return array($array);
		}
		$level = 0;
		$i = 0;
		$res = array();
		foreach ($array as $val) {
			if ($val == '(') {
				$level++;
			}
			$res[$i][] = $val;
			if ($val == ')') {
				$level--;
			}
			if ($level == 1) {
				$i++;
			}
		}
		return array_splice($res, 1, -1);
	}
	function parse_envelope_address($array) {
		$count = count($array) - 1;
		$string = '';
		$name = false;
		$mail = false;
		$domain = false;
		for ($i = 0;$i<$count;$i+= 6) {
			if (isset($array[$i + 1])) {
				$name = $array[$i + 1];
			}
			if (isset($array[$i + 3])) {
				$mail = $array[$i + 3];
			}
			if (isset($array[$i + 4])) {
				$domain = $array[$i + 4];
			}
			if ($name && strtoupper($name) != 'NIL') {
				$name = str_replace(array('"', "'"), '', $name);
				if ($string != '') {
					$string .= ', ';
				}
				if ($name != $mail.'@'.$domain) {
					$string .= '"'.$name.'" ';
				}
				if ($mail && $domain) {
					$string .= $mail.'@'.$domain;
				}
			}
			if ($mail && $domain) {
				$string .= $mail.'@'.$domain;
			}
			$name = false;
			$mail = false;
			$domain = false;
		}
		return $string;
	}
	function parse_envelope($array, $res) {
		$flds = array('date', 'subject', 'from', 'sender', 'reply-to', 'to', 'cc', 'bcc', 'in-reply-to', 'message_id');
		foreach ($flds as $val) {
			if (strtoupper($array[0]) != 'NIL') {
				if ($array[0] == '(') {
					array_shift($array);
					$parts = array();
					$index = 0;
					$level = 1;
					foreach ($array as $i => $v) {
						if ($level == 0) {
							$index = $i;
							break;
						}
						$parts[] = $v;
						if ($v == '(') {
							$level++;
						}
						if ($v == ')') {
							$level--;
						}
					}
					if ($index) {
						$array = array_splice($array, $index);
						$res[$val] = $this->parse_envelope_address($parts);
					}
				}
				else {
					$res[$val] = array_shift($array);
				}
			}
			else {
				$res[$val] = false;
			}
		}
		return $res;
	}
}
