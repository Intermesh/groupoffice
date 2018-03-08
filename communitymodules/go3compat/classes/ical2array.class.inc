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
 * @version $Id: ical2array.class.inc 10767 2012-06-12 13:31:03Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */


/**
 * Constants used in this class
 */
define("ICAL_DELIM_EQUAL", "=");
define("ICAL_WORD_WRAP_DOS", chr(13).chr(10));
define("ICAL_WORD_WRAP_MAC", chr(13));
define("ICAL_WORD_WRAP_UNIX", chr(10));
define("ICAL_CHAR_WSP", chr(32));
define("ICAL_CHAR_HTAB", chr(9));




/**
 * builds an array from an ics file:
 *
 * Example:
 *
 * SUMMARY:object summary
 * $this->objects[$n]['SUMMARY']['value'] = 'object summary';
 *
 * RRULE:FREQ=YEARLY;INTERVAL=1;BYMONTH=3
 * $this->objects[$n]['RRULE']['value'] = 'FREQ=YEARLY;INTERVAL=1;BYMONTH=3';
 *
 * You can parse the rrule with parse_rrule() into:
 *
 * $rrule = $this->parse_rrule('FREQ=YEARLY;INTERVAL=1;BYMONTH=3');
 * $rrule['FREQ'] = 'YEARLY';
 * $rrule['INTERVAL'] = '1';
 * $rrule['BYMONTH'] = '3';
 *
 * DTSTART;VALUE=DATE:20040303
 * $this->objects[$n]['DTSTART']['params']['VALUE'] = 'DATE';
 * $this->objects[$n]['DTSTART']['value'] = '20040303';
 *
 * @copyright Copyright Intermesh
 * @version $Id: ical2array.class.inc 10767 2012-06-12 13:31:03Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.calendar
 *
 */

class ical2array {
	var $todos = array();

	var $object = array();
	var $object_name;
	var $param_name;
	var $buffer;

	var $version = 0;
	var $prodid='';
	//var $object_types = array('VTIMEZONE', 'DAYLIGHT', 'VEVENT', 'VTODO', 'STANDARD');
	var $object_types = array(
		'VCALENDAR'=>array(
			'VTIMEZONE'=>array(
				'DAYLIGHT'=>array(),
				'STANDARD'=>array()
			),
			'VEVENT'=>array(
				'VALARM'=>array()
			),
			'VTODO'=>array(
				'VALARM'=>array()
			)
		)
	);
	var $timezones = array();
	var $force_timezone;//hours
	var $continue_value=false;


	//Workaround for Nokia bug that sends GMT time without a Z at the end
	function force_timezone($timezone) {
		$this->force_timezone = $timezone;
	}

	function parse_file($ical_file) {
		if (!$fp = fopen ($ical_file, "r")) {
			return false;
		}else {
			$data = fread($fp, filesize($ical_file));
			fclose($fp);

			$vcalendar = $this->parse_icalendar_string($data);
			$this->timezones = $this->get_timezones($vcalendar);
			return $vcalendar;
		}
	}

	function parse_string($ical_string) {
		$vcalendar = $this->parse_icalendar_string($ical_string);
		$this->timezones = $this->get_timezones($vcalendar);
		return $vcalendar;
	}


	function parse_icalendar_string($data, $types=false) {
		if(!$types) {
			$types=$this->object_types;
			$greedy='';
		}else {
			$greedy='U';
		}
		/*word wrap - replace <CRLF> by <LF> (dos)*/
		//$data = str_replace(ICAL_WORD_WRAP_DOS, ICAL_WORD_WRAP_UNIX, $data);
		/*word wrap - replace <CR> by <LF> (mac)*/
		//$data = str_replace(ICAL_WORD_WRAP_MAC, ICAL_WORD_WRAP_UNIX, $data);
		/*unfolding lines ending up in '=<LF>', originally '=<CRLF>'*/
		//$regex = '/('.ICAL_DELIM_EQUAL.ICAL_WORD_WRAP_UNIX.')/i';
		//	$data = preg_replace($regex, "", $data);

		/*unfolding lines as specified in RFC2425*/
		//$regex = '/('.ICAL_WORD_WRAP_UNIX.')(['.ICAL_CHAR_WSP.'|'.ICAL_CHAR_HTAB.'])/i';
		//$data = preg_replace($regex, "", $data)."\n";

		$data = str_replace("\r", "", $data);
		
		$objects = array();

		$count=0;
		//check for all object types
		foreach($types as $type=>$children) {
			$regex ='/BEGIN:'.$type.'(.*)END:'.$type.'/s'.$greedy;
			//echo "$regex\n";

			preg_match_all($regex, $data, $matches);
			$matches=$matches[1];			
			while($match=array_shift($matches)) {
				$object = $this->parse_object_string($match, $children);

				if($type=='VCALENDAR'){
					if(isset($object['VERSION']))
						$this->version=$object['VERSION']['value'];
					
					if(isset($object['PRODID']))
						$this->prodid=$object['PRODID']['value'];
				}

				$object['type'] = $type;

				if(count($children))
					$object['objects']  = $this->parse_icalendar_string($match, $children);				

				$objects[] = $object;

				$count++;
			}
		}

		return $objects;
	}

	function parse_object_string($data, $children) {
		//get rid of nested objects
		foreach($children as $type=>$children) {
			$regex ='/BEGIN:'.$type.'(.*)END:'.$type.'/s';
			$data = preg_replace('/BEGIN:'.$type.'.*END:'.$type.'/s', '', $data);
		}

		if($this->version>1){
			$data = preg_replace("/\n[ \t]{1}/",'', $data);

			if(stripos($this->prodid, 'nexthaus')){
				$description = preg_match('/DESCRIPTION:(.*)[A-Z]+(:|;)/Us', $data, $matches);
				if($matches){
					$data=str_replace($matches[1], str_replace("\n", ' ', $matches[1])."\n", $data);
				}
			}
		}


		$this->object = array();
		$in_value = false;
		$this->buffer_type = 'object_name';
		$this->buffer = '';

		//echo $data."\n";
		$lines = explode("\n", $data);

		for($linecount=0,$count=count($lines);$linecount<$count;$linecount++) {
			$line = array_shift($lines);

			//don't process empty lines
			if (strlen($line) == 0) {
				if ($this->buffer_type == 'value') {
					$this->add_buffer();
					$this->buffer_type = 'object_name';
				}
			}else {

				/*
					If we are processing a value and the first value is a tab (chr(9)) or
					a space (chr(32)) then cut it. if it's not one of these chars
					then we are done processing a value.
				*/
				if ($this->buffer_type == 'value') {
					//Nexthaus does not indent the description with spaces. So if the next line does not match this pattern:
					// PROP:value then continue with the value
					/// ^\S{1}[^:]*:.*/

					if(isset($line[0]) && $line[0] != chr(32) && $line[0] != chr(9) && !$this->continue_value && preg_match('/^\S{1}[^:]*:.*/', $line)) {
						$this->add_buffer();
						$this->buffer_type = 'object_name';
					}else {
						if(!$this->continue_value) {
							$line = ltrim($line);
						}
					}
				}
				$this->continue_value=false;

				$line_length = strlen($line);
				for($i=0;$i<$line_length;$i++) {
					$char = $line[$i];

					switch($char) {
						case ':':
							if (strlen($this->buffer) > 0) {
								//after an : comes an:
								if ($this->buffer_type == 'value') {
									$this->buffer .= $char;
								}else {
									$this->add_buffer();
									$this->buffer_type = 'value';
								}
							}
							break;

						case ';':
							if (strlen($this->buffer) > 0) {
								//after an ; comes an:
								if ($this->buffer_type == 'value') {
									$this->buffer .= $char;
								}else {
									$this->add_buffer();
									$this->buffer_type = 'param_name';
								}
							}
							break;

						case '=':

						//if (strlen($this->buffer) > 0)
						//{
						//after an ; comes an:
							if ($this->buffer_type == 'value') {
								if($i == ($line_length-1)) {
									$this->continue_value=true;
								}else {
									$this->buffer .= $char;
								}
							}else {
								$this->add_buffer();
								$this->buffer_type = 'param_value';
							}
							//}
							break;

						default:
						//default is to create the buffer
							$this->buffer .= $char;
							break;
					}
				}
			}
		}
		$this->add_buffer();
		return $this->object;
	}

	function add_buffer() {
		$this->buffer = str_replace('\r\n', "\r\n", $this->buffer);
		$this->buffer = str_replace('\n', "\r\n", $this->buffer);
		$this->buffer = stripslashes($this->buffer);
		$this->buffer = trim($this->buffer);
		
		switch($this->buffer_type) {
			case 'object_name':
				
				if($this->buffer == 'ATTENDEE')
				{
					if(!isset($this->object['ATTENDEES']))
					{
						$this->object['ATTENDEES'] = array();
					}
					$count = count($this->object['ATTENDEES'])+1;
					$this->object_name = $this->buffer.'_'.$count;
					$this->object['ATTENDEES'][$this->object_name] = array();
				}else
				{
					if(!isset($this->object[$this->buffer]))
						$this->object[$this->buffer] = array();
					
					$this->object_name = $this->buffer;
				}
				break;

			case 'param_name':							

				if(strpos($this->object_name, 'ATTENDEE') !== false)
				{
					$this->object['ATTENDEES'][$this->object_name]['params'][$this->buffer] = '';					
				}else
				{
					$this->object[$this->object_name]['params'][$this->buffer] = '';				
				}
				$this->param_name = $this->buffer;
				break;

			case 'param_value':

				if(substr($this->buffer,0,1)=='"' && substr($this->buffer,-1,1)=='"'){
					$this->buffer = substr($this->buffer,1,-1);
				}

				if(strpos($this->object_name, 'ATTENDEE') !== false)
				{					
					$this->object['ATTENDEES'][$this->object_name]['params'][$this->param_name] = $this->buffer;
				}else
				{
					$this->object[$this->object_name]['params'][$this->param_name] = $this->buffer;
				}
				break;

			case 'value':
				if(strpos($this->object_name, 'ATTENDEE') === 0)
				{
					$this->object['ATTENDEES'][$this->object_name]['value'] = $this->buffer;
				}else
				{
					
					if($this->object_name=='EXDATE' && isset($this->object[$this->object_name]['value'])){
						$this->object[$this->object_name]['value'] .= ';'.$this->buffer;
					}else
					{
						$this->object[$this->object_name]['value'] = $this->buffer;
					}
				}

				unset($this->object_name,	$this->param_name);
				break;
		}
		$this->buffer = '';
	}

	function get_timezones($vcalendar) {
		$timezones = array();
		if(isset($vcalendar[0]['objects'])) {
			while($object = array_shift($vcalendar[0]['objects'])) {

				if($object['type'] == 'VTIMEZONE' && isset($object['TZID']) && isset($object['objects'])) {
					while($sub_object = array_shift($object['objects'])) {
						$timezones[$object['TZID']['value']][$sub_object['type']] = $this->convert_timezone( $sub_object['TZOFFSETTO']['value']);
					}
				}
			}
		}
		return $timezones;
	}

	function convert_timezone($timezone) {
		return intval(substr($timezone,0,-2));
	}

	function parse_rrule($rrule) {
		$rrule_arr = array();

		$rrule = str_replace('RRULE:', '', $rrule);

		if(strpos($rrule,'FREQ') === false) {
			//this must be a vcalendar 1.0 rrule
			//we are attempting to convert it to icalendar format

			//GO Supports only one rule everything behind the first rule is chopped
			$hek_pos = strpos($rrule, '#');
			if($hek_pos) {
				$space_pos = strpos($rrule, ' ', $hek_pos);
				if($space_pos) {
					return false;
					//$rrule = substr($rrule,0,$space_pos);
				}
			}

			$expl_rrule = explode(' ', $rrule);

			//the count or until is always in the last element
			if($until = array_pop($expl_rrule)) {
				if($until{0} == '#') {
					$count = substr($until, 1);
					if($count > 0) {
						$rrule_arr['COUNT'] = $count;
					}

					if(strlen($expl_rrule[count($expl_rrule)-1])>2){
						//this must be the end date
						$rrule_arr['UNTIL'] = array_pop($expl_rrule);
					}

				}else {
					$rrule_arr['UNTIL'] = $until;
				}
			}


			if($rrule_arr['FREQ'] = array_shift($expl_rrule)) {

				$rrule_arr['INTERVAL'] = '';

				$lastchar = substr($rrule_arr['FREQ'], -1, 1);
				while(is_numeric($lastchar)) {
					//echo $rrule_arr['FREQ'].'<br>';
					$rrule_arr['INTERVAL'] = $lastchar.$rrule_arr['INTERVAL'];
					$rrule_arr['FREQ'] = substr($rrule_arr['FREQ'], 0, strlen($rrule_arr['FREQ'])-1);
					$lastchar = substr($rrule_arr['FREQ'], -1, 1);
				}

				switch($rrule_arr['FREQ']) {
					case 'D':
						$rrule_arr['FREQ'] = 'DAILY';
						break;

					case 'W':
						$rrule_arr['FREQ'] = 'WEEKLY';
						$rrule_arr['BYDAY'] = implode(',',$expl_rrule);
						break;

					case 'MP':
						$rrule_arr['FREQ'] = 'MONTHLY';

						//GO Supports only one position in the month
						/*if(count($expl_rrule) > 1)
						{
							//return false;
						}*/	
						$month_time = array_shift($expl_rrule);
						//todo negative month times
						$rrule_arr['BYDAY'] = substr($month_time, 0, strlen($month_time)-1).array_shift($expl_rrule);
						break;

					case 'MD':
						$rrule_arr['FREQ'] = 'MONTHLY';
						//GO Supports only one position in the month
						if(count($expl_rrule) > 1) {
							return false;
						}

						$month_time = array_shift($expl_rrule);
						//todo negative month times
						//$rrule_arr['BYMONTHDAY'] = substr($month_time, 0, strlen($month_time)-1);
						//for nexthaus
						$rrule_arr['BYMONTHDAY'] = trim($month_time);//substr($month_time, 0, strlen($month_time)-1);
						break;

					case 'YM':
						$rrule_arr['FREQ'] = 'YEARLY';
						//GO Supports only one position in the month
						if(count($expl_rrule) > 1) {
							return false;
						}
						$rrule_arr['BYMONTH'] = array_shift($expl_rrule);
						break;

					case 'YD':
					//Currently not supported by GO
						return false;
						break;
				}
			}
		}else {
			$params = explode(';', $rrule);

			while($param = array_shift($params)) {
				$param_arr = explode('=', $param);

				if (isset($param_arr[0]) && isset($param_arr[1])) {
					$rrule_arr[strtoupper(trim($param_arr[0]))] = strtoupper(trim($param_arr[1]));
				}
			}

			if(empty($rrule_arr['INTERVAL'])) {
				$rrule_arr['INTERVAL']=1;
			}
		}

		return $rrule_arr;
	}

	/*function get_timezone_offset($date, $timezone_id='') {
		$year = substr($date,0,4);
		$month = substr($date,4,2);
		$day = substr($date,6,2);

		if (strpos($date, 'T') !== false) {
			$hour = substr($date,9,2);
			$min = substr($date,11,2);
			$sec = substr($date,13,2);
		}else {
			$hour = 0;
			$min = 0;
			$sec = 0;
		}


		if(strpos($date, 'Z') !== false) {
			$timezone_offset = 0;
		}else {
			$timezone_offset =  Date::get_timezone_offset(mktime($hour, $min, $sec, $month, $day , $year));

			if(isset($this->timezones[$timezone_id]) && isset($this->timezones[$timezone_id]['STANDARD'])) {
				$standard_tzoffset = $this->timezones[$timezone_id]['STANDARD'];
				$daylight_tzoffset = isset($this->timezones[$timezone_id]['DAYLIGHT']) ? $this->timezones[$timezone_id]['DAYLIGHT'] : $standard_tzoffset;

				if(date('I', mktime($hour, $min, $sec, $month, $day , $year)) > 0) {
					//event is in DST
					$timezone_offset = $daylight_tzoffset;
				}else {
					$timezone_offset = $standard_tzoffset;
				}
			}
		}

		return $timezone_offset;
	}*/

	function parse_date($date, $timezone_id='') {
		$date=trim($date);
		$year = substr($date,0,4);
		$month = substr($date,4,2);
		$day = substr($date,6,2);
		if (strpos($date, 'T') !== false) {
			$hour = substr($date,9,2);
			$min = substr($date,11,2);
			$sec = substr($date,13,2);
		}else {
			$hour = 0;
			$min = 0;
			$sec = 0;
		}
		
		if(strpos($date, 'Z') !== false) {
			return gmmktime($hour, $min, $sec, $month, $day , $year);
		}

		if(isset($this->force_timezone)) {
			$timezone_offset = $this->force_timezone;
		}else {
			if(isset($this->timezones[$timezone_id]) && isset($this->timezones[$timezone_id]['STANDARD'])) {
				//if ($this->is_standard_timezone($timezone_id)) {
				$standard_tzoffset = $this->timezones[$timezone_id]['STANDARD'];
				$daylight_tzoffset = isset($this->timezones[$timezone_id]['DAYLIGHT']) ? $this->timezones[$timezone_id]['DAYLIGHT'] : $standard_tzoffset;
				if(date('I', mktime($hour, $min, $sec, $month, $day , $year)) > 0) {
					//event is in DST
					$timezone_offset = $daylight_tzoffset;
				}else {
					$timezone_offset = $standard_tzoffset;
				}
			}				
		}


		if(isset($timezone_offset)){
			return gmmktime($hour-$timezone_offset, $min, $sec, $month, $day , $year);
		}else
		{
			return mktime($hour, $min, $sec, $month, $day , $year);
		}
	}

	function parse_duration($duration) {
		preg_match('/(-?)P([0-9]+[WD])?T?([0-9]+H)?([0-9]+M)?([0-9]+S)?/', $duration, $matches);
		//var_dump($matches);


		$negative = $matches[1]=='-' ? -1 : 1;

		$days = 0;
		$weeks = 0;
		$hours=0;
		$mins=0;
		$secs = 0;
		for($i=2;$i<count($matches);$i++){
			$d = substr($matches[$i],-1);
			switch($d){
				case 'D':
					$days += intval($matches[$i]);
					break;
				case 'W':
					$weeks += intval($matches[$i]);
					break;
				case 'H':
					$hours += intval($matches[$i]);
					break;
				case 'M':
					$mins += intval($matches[$i]);
					break;
				case 'S':
					$secs += intval($matches[$i]);
					break;
			}
		}

		return $negative*(($weeks * 60 * 60 * 24 * 7) + ($days * 60 * 60 * 24) + ($hours * 60 * 60) + ($mins * 60) + ($secs));
	}



	function add_object() {
		$this->objects[] = $this->object;
		$this->object = array();
	}
}
