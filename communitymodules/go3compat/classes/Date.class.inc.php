<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: Date.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/**
 * Define some constants
 */
if(!defined('REPEAT_NONE'))
{
	define('REPEAT_NONE', '0');
	define('REPEAT_DAILY', '1');
	define('REPEAT_WEEKLY', '2');
	define('REPEAT_MONTH_DATE', '3');
	define('REPEAT_MONTH_DAY', '4');
	define('REPEAT_YEARLY', '5');
}


/**
 * This class has functions that handle dates and takes the user's date
 * preferences into account.
 *
 * @copyright Copyright Intermesh
 * @version $Id: Date.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @package go.utils
 * @since Group-Office 3.0
 */

class Date {

	/**
	 * Returns true if the time is a holiday or in the weekend
	 *
	 * @param <type> $time
	 * @param <type> $region
	 * @return <type> boolean
	 */
	public static function is_on_free_day($time, $region=false) {
		
		$weekday = date('w', $time);
		if($weekday==6 || $weekday==0) {
			return true;
		}else {

			global $GO_CONFIG, $GO_LANGUAGE;
			
			$date = getdate($time);

			$day_start = mktime(0,0,0,$date['mon'], $date['mday'], $date['year']);
			$day_end =  mktime(0,0,0,$date['mon'], $date['mday']+1, $date['year']);

			require_once($GLOBALS['GO_CONFIG']->class_path.'holidays.class.inc.php');
			$holidays = new holidays();

			$region=$region ? $region : $GLOBALS['GO_LANGUAGE']->language;

			$hd = new holidays();
			$count = $hd->get_holidays_for_period($region, $day_start, $day_end);
			if($count) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Calculate how many times the weekday has occured in the month
	 *
	 * @param <type> $time
	 * @return <type> the number of times the weekday occurred
	 */
	public static function get_occurring_number_of_day_in_month($time){
		$mday=date('j', $time);
		return ceil($mday/7);
	}
	/**
	 * Finds the difference in days between two calendar dates.
	 *
	 * @param Date $startDate
	 * @param Date $endDate
	 * @return Int
	 */
	public static function date_diff_days($start_time, $end_time) {
		// Parse dates for conversion
		$start = getdate($start_time);
		$end = getdate($end_time);

		// Convert dates to Julian Days
		$start_date = gregoriantojd($start["mon"], $start["mday"], $start["year"]);
		$end_date = gregoriantojd($end["mon"], $end["mday"], $end["year"]);
		
		return $end_date-$start_date;
		// Return difference
		//return round(($end_date - $start_date), 0);
	}


	public static function format_long_date($time,$add_time=true){
		global $lang;

		$str  = $lang['common']['full_days'][date('w', $time)].' '.date('d', $time).' '.$lang['common']['months'][date('n', $time)].' ';
		if ($add_time)
			return $str.date('Y - '.$_SESSION['GO_SESSION']['time_format'], $time);
		else
			return $str.date('Y', $time);
	}
	/**
	 * Converts a Group-Office date to unix time.
	 *
	 * A Group-Office date is formated by user preference.
	 *
	 * @param	StringHelper $date_string The date string formated in the user's preference
	 * @access public
	 * @return int unix timestamp
	 */


	public static function byday_to_days($byday)
	{
		//$days_arr = explode(',', $byday);
		
		$event=array();

		$event['sun'] = strpos($byday,'SU')!==false ? '1' : '0';
		$event['mon'] = strpos($byday,'MO')!==false ? '1' : '0';
		$event['tue'] = strpos($byday,'TU')!==false ? '1' : '0';
		$event['wed'] = strpos($byday,'WE')!==false ? '1' : '0';
		$event['thu'] = strpos($byday,'TH')!==false ? '1' : '0';
		$event['fri'] = strpos($byday,'FR')!==false ? '1' : '0';
		$event['sat'] = strpos($byday,'SA')!==false ? '1' : '0';

		/*$days['sun'] = in_array('SU', $days_arr) ? '1' : '0';
		$days['mon'] = in_array('MO', $days_arr) ? '1' : '0';
		$days['tue'] = in_array('TU', $days_arr) ? '1' : '0';
		$days['wed'] = in_array('WE', $days_arr) ? '1' : '0';
		$days['thu'] = in_array('TH', $days_arr) ? '1' : '0';
		$days['fri'] = in_array('FR', $days_arr) ? '1' : '0';
		$days['sat'] = in_array('SA', $days_arr) ? '1' : '0';*/

		return $event;
	}


	/**
	 * Calculates the next occurence of a recurring event
	 *
	 * @param int $first_occurence_time The time this events occurs for the first time.
	 * @param int $start_time The next occurence returns will happen after this time.
	 * @param StringHelper $rrule The iCalendar rrule
	 * @return int
	 */
	public static function get_next_recurrence_time($first_occurence_time, $start_time, $duration, $rrule)
	{
		global $GO_CONFIG;

		require_once($GLOBALS['GO_CONFIG']->class_path.'ical2array.class.inc');
		$ical2array = new ical2array();

		if(!$rrule = $ical2array->parse_rrule($rrule))
		{
			//Recurrence rule is not understood by GO, abort
			return false;
		}

		if(!isset($rrule['FREQ']))
		{
			return false;
		}

		//if the requested start
		if($start_time < $first_occurence_time)
			$start_time = $first_occurence_time-1;
			
		//we cannot simply return the first_occurrence_time because a recurring event
		//can start on a day that it doesn't occur on.

		if (isset($rrule['UNTIL']))
		{
			if($event['repeat_end_time'] = $ical2array->parse_date($rrule['UNTIL']))
			{
				
				$event['repeat_forever']='0';
				$event['repeat_end_time'] = mktime(0,0,0, date('n', $event['repeat_end_time']), date('j', $event['repeat_end_time'])+1, date('Y', $event['repeat_end_time']));
			}else
			{
				$event['repeat_forever'] = 1;
			}
		}elseif(isset($rrule['COUNT']))
		{
			//figure out end time later when event data is complete
			$event['repeat_forever'] = 1;
			$event_count = intval($rrule['COUNT']);
			if($event_count==0)
			{
				unset($event_count);
			}
		}else
		{
			$event['repeat_forever'] = 1;
		}

		$event['repeat_every']=isset($rrule['INTERVAL']) ? $rrule['INTERVAL'] : 1;

		if($event['repeat_every']==0)
		return false;

		$occurence_time=0;

		$event['start_time']=$first_occurence_time;



		$day_db_field[0] = 'sun';
		$day_db_field[1] = 'mon';
		$day_db_field[2] = 'tue';
		$day_db_field[3] = 'wed';
		$day_db_field[4] = 'thu';
		$day_db_field[5] = 'fri';
		$day_db_field[6] = 'sat';

		switch($rrule['FREQ'])
		{

			case 'WEEKLY':

				if(empty($rrule['BYDAY'])){
					$days=array(
						'mon'=>0,
						'tue'=>0,
						'wed'=>0,
						'thu'=>0,
						'fri'=>0,
						'sat'=>0,
						'sun'=>0
					);

					$days[$day_db_field[date('w', $event['start_time'])]]='1';
				}else
				{
					$days = Date::byday_to_days($rrule['BYDAY']);
					$days = Date::shift_days_to_local($days, date('G', $event['start_time']), Date::get_timezone_offset($event['start_time']));
				}
				

				$interval = $start_time - $first_occurence_time;

				$interval_weeks = floor($interval/604800);
				$devided = $interval_weeks/$event['repeat_every'];
				$rounded = ceil($devided);

				for ($i=0;$i<7;$i++)
				{
					if($i==0)
					{
						$last_occurence_time = $first_occurence_time+($event['repeat_every']*$rounded*604800);
						$last_occurence_time=mktime(date('H', $first_occurence_time), date('i', $first_occurence_time),0, date('m', $last_occurence_time), date('j', $last_occurence_time)-date('w', $last_occurence_time), date('Y', $last_occurence_time));
					}

					$test_time = Date::date_add($last_occurence_time, $i);
					$weekday = date("w", $test_time);
					//echo '*'.date('r', $start_time).' -> '.date('r', $test_time).' -> '.$event[$day_db_field[$weekday]]."\n";
					if ($days[$day_db_field[$weekday]] == '1' && $test_time>$start_time)
					{
						$occurence_time = $test_time;
						break;
					}

					if($i==6 && $occurence_time<$start_time)
					{
						$rounded++;
						$i=-1;
					}
				}
				break;

			case 'DAILY':
				$interval = $start_time - $first_occurence_time;

				$interval_days = floor($interval/86400);
				$devided = $interval_days/$event['repeat_every'];
				$rounded = ceil($devided);

				while($occurence_time<=$start_time)
				{
					$occurence_time = $first_occurence_time+($event['repeat_every']*$rounded*86400);
					$occurence_time=mktime(date('H', $first_occurence_time), date('i', $first_occurence_time),0, date('m', $occurence_time), date('j', $occurence_time), date('Y', $occurence_time));
					$rounded++;
				}

				break;

			case 'MONTHLY':
				

				if (!isset($rrule['BYDAY']))
				{
					$interval_years = date('Y', $start_time)-date('Y', $first_occurence_time);
					$interval_months = date('n', $start_time)-date('n', $first_occurence_time);
					$interval_months = 12*$interval_years+$interval_months;

					$devided = $interval_months/$event['repeat_every'];
					$rounded = ceil($devided);
					//go_debug('*'.$devided);
					while($occurence_time<=$start_time)
					{
						$occurence_time=mktime(date('H', $first_occurence_time), date('i', $first_occurence_time),0, date('n', $first_occurence_time)+($event['repeat_every']*$rounded), date('j', $first_occurence_time), date('Y', $first_occurence_time));
						$rounded++;
					}			
					
				}else
				{
					if(empty($rrule['BYDAY'])){
						return false;
					}
					
					//Maybe more efficient to jump to right week first....
					
					
					if(!isset($rrule['BYSETPOS'])){
						$event['month_time'] = $rrule['BYDAY'][0];
						$day = substr($rrule['BYDAY'], 1);
					}else
					{
						$event['month_time'] = $rrule['BYSETPOS'];
						$day = $rrule['BYDAY'];
					}

						
					$days = Date::byday_to_days($day);

					if(!count($days))
					return false;


					$days = Date::shift_days_to_local($days, date('G', $event['start_time']), Date::get_timezone_offset($event['start_time']));

					//go_debug('New call');

					$test_time=mktime(date('H', $first_occurence_time), date('i', $first_occurence_time),0, date('n', $start_time), date('j', $start_time), date('Y', $start_time));
					while($occurence_time==0)
					{	
						//go_debug('*'.date('r', $test_time));

						$weekday = date("w", $test_time);

						if (!empty($days[$day_db_field[$weekday]]))
						{
							$interval_years = date('Y', $test_time)-date('Y', $first_occurence_time);
							$interval_months = date('n', $test_time)-date('n', $first_occurence_time);
							$interval_months = 12*$interval_years+$interval_months;
							$devided = $interval_months/$event['repeat_every'];

							if(ceil($devided)!=$devided){
								//$test_time = Date::date_add($test_time, 23);
								$test_time = mktime(date('H', $test_time), date('i', $test_time),0, date('n', $test_time)+1, 1, date('Y', $test_time));
							}else
							{
								//go_debug('**'.ceil(date('j',$test_time)/7).' = '.$event['month_time']);
								if (ceil(date('j',$test_time)/7) == $event['month_time'] && $test_time>$start_time)
								{
									$occurence_time=$test_time;
									//go_debug('found '.date('Ymd', $occurence_time));
									break;
								}
							}
						}

						/*
						 * jump to next month if
						 */
						if(date('j',$test_time)>($event['month_time']+1)*7){
							$test_time = mktime(date('H', $test_time), date('i', $test_time),0, date('n', $test_time)+1, 1, date('Y', $test_time));
						}else
						{
							$test_time = Date::date_add($test_time, 1);
						}
					}
					
				}
				break;

			case 'YEARLY';
			$interval_years = date('Y', $start_time)-date('Y', $first_occurence_time);
			$devided = $interval_years/$event['repeat_every'];
			$rounded = ceil($devided);

			while($occurence_time<=$start_time)
			{
				$new_year = date('Y', $first_occurence_time)+($event['repeat_every']*$rounded);

				$occurence_time=mktime(
				date('H', $first_occurence_time),
				date('i', $first_occurence_time),
				0,
				date('n', $first_occurence_time),
				date('j', $first_occurence_time),
				$new_year);

				if(!$occurence_time)
				break;

				$rounded++;
			}
			break;
		}


		if ($event['repeat_forever'] == '0' && $occurence_time >= $event['repeat_end_time']-$duration)
		{
			return 0;
		}else
		{
			return $occurence_time;
		}

	}





	public static function shift_days_to_gmt($days, $local_start_hour, $timezone_offset)
	{
		$gmt_start_hour = $local_start_hour-$timezone_offset;

		if ($gmt_start_hour > 23) {
			$shift_day = 1;
		}elseif ($gmt_start_hour < 0) {
			$shift_day = -1;
		} else {
			$shift_day = 0;
		}



		if($shift_day!=0)
		{
			switch ($shift_day) {
				case 1 :
					$mon = $days['sun'];
					$tue = $days['mon'];
					$wed = $days['tue'];
					$thu = $days['wed'];
					$fri = $days['thu'];
					$sat = $days['fri'];
					$sun = $days['sat'];
					break;

				case -1 :
					$mon = $days['tue'];
					$tue = $days['wed'];
					$wed = $days['thu'];
					$thu = $days['fri'];
					$fri = $days['sat'];
					$sat = $days['sun'];
					$sun = $days['mon'];
					break;
			}
			$days['sun']=$sun;
			$days['mon']=$mon;
			$days['tue']=$tue;
			$days['wed']=$wed;
			$days['thu']=$thu;
			$days['fri']=$fri;
			$days['sat']=$sat;
		}
		return $days;
	}


	public static function shift_days_to_local($days, $local_start_hour, $timezone_offset)
	{
		//shift the selected weekdays to local time
		//var_dump($days);
		$gmt_start_hour = $local_start_hour-$timezone_offset;

		if ($gmt_start_hour > 23) {
			$shift_day = -1;
		}elseif ($gmt_start_hour < 0) {
			$shift_day = 1;
		} else {
			$shift_day = 0;
		}

		//go_debug($gmt_start_hour.' > '.$timezone_offset.' > '.$shift_day);

		if($shift_day!=0)
		{
			switch ($shift_day) {
				case 1 :
					$mon = $days['sun'];
					$tue = $days['mon'];
					$wed = $days['tue'];
					$thu = $days['wed'];
					$fri = $days['thu'];
					$sat = $days['fri'];
					$sun = $days['sat'];
					break;

				case -1 :
					$mon = $days['tue'];
					$tue = $days['wed'];
					$wed = $days['thu'];
					$thu = $days['fri'];
					$fri = $days['sat'];
					$sat = $days['sun'];
					$sun = $days['mon'];
					break;
			}

			$days['sun']=$sun;
			$days['mon']=$mon;
			$days['tue']=$tue;
			$days['wed']=$wed;
			$days['thu']=$thu;
			$days['fri']=$fri;
			$days['sat']=$sat;
		}
		return $days;
	}

	/**
	 * Reformat a date string formatted by Group-Office user preference to a string
	 * that can be read by strtotime related PHP functions
	 *
	 * @param StringHelper $date_string
	 * @param StringHelper $date_separator
	 * @param StringHelper $date_format
	 * @return StringHelper
	 */

	public static function to_input_format($date_string, $date_separator=null, $date_format=null)
	{
		if(!isset($date_format))
			$date_format=$_SESSION['GO_SESSION']['date_format'];

		if(!isset($date_separator))
			$date_separator=$_SESSION['GO_SESSION']['date_separator'];

		$date_string = trim($date_string);
		
		if ($date_string != '') {

			$datetime_array = explode(' ', $date_string);

			$date = array_shift($datetime_array);
			if(!$date)
				$date='0000'.$date_separator.'00'.$date_separator.'00';

			$date_array = explode($date_separator, $date);
			
			$format = str_replace($date_separator,'',$date_format);

			$year_pos = strpos($format, 'Y');
			$month_pos = strpos($format, 'm');
			$day_pos = strpos($format, 'd');

			$year = isset ($date_array[$year_pos]) ? $date_array[$year_pos] : date('Y');
			$month = isset ($date_array[$month_pos]) ? $date_array[$month_pos] : date('m');
			$day = isset ($date_array[$day_pos]) ? $date_array[$day_pos] : 0;

			$time = implode(' ', $datetime_array);

			$newdate=$year.'-'.$month.'-'.$day;
			
			if(!empty($time))
				$newdate .= ' '.$time;

			return $newdate;
		}
		return false;

	}

	/**
	 * Takes a date string formatted by Group-Office user preference and turns it 
	 * into a unix timestamp.
	 *
	 * @param String $date_string
	 * @return int Unix timestamp
	 */


	public static function to_unixtime($date_string) {
		if(empty($date_string) || $date_string=='0000-00-00')
		{
			return 0;
		}
		try{
			//$d = new DateTime(Date::to_input_format($date_string));
			//return $d->format('U');

			return strtotime(Date::to_input_format($date_string));
		}catch(Exception $e)
		{
			return false;
		}
	}

	/**
	 * Convert a Group-Office date to MySQL date format
	 *
	 * A Group-Office date is formated by user preference.
	 *
	 * @param	StringHelper $date_string The Group-Office date string
	 * @param	bool $with_time The output sting should contain time too
	 * @access public
	 * @return int unix timestamp
	 */

	public static function to_db_date($date_string, $with_time = false) {
		if(empty($date_string))
		{
			return '0000-00-00';
		}
		$time = Date::to_unixtime($date_string);
		if(!$time)
		{
			return '0000-00-00';
		}
		$date_format = $with_time ? 'Y-m-d H:i' : 'Y-m-d';
		return date($date_format, $time);
	}

	/**
	 * Add a period to a unix timestamp
	 *
	 * @param int $time
	 * @param int $days
	 * @param int $months
	 * @param int $years
	 * @return int
	 */


	public static function date_add($time,$days=0,$months=0,$years=0)
	{
		$date=getdate($time);
		return mktime($date['hours'],$date['minutes'], $date['seconds'],$date['mon']+$months,$date['mday']+$days,$date['year']+$years);
	}
	
	public static function clear_time($time){
		$date=getdate($time);
		return mktime(0,0,0,$date['mon'],$date['mday'],$date['year']);
	}



	/**
	 * Takes two Group-Office settings like Ymd and - and converts this into Y-m-d
	 *
	 * @param	StringHelper $format Any format accepted by php's date function
	 * @param	StringHelper $separator A separate like - / or .
	 * @access public
	 * @return int unix timestamp
	 */

	public static function get_dateformat($format, $separator)
	{
		$newformat = '';
		$end = strlen($format)-1;
		for($i=0;$i<$end;$i++)
		{
			$newformat .= $format[$i].$separator;
		}
		$newformat .= $format[$i];
		return $newformat;
	}


	/**
	 * Get the current server time in microseconds
	 *
	 * @access public
	 * @return int
	 */
	public static function getmicrotime() {
		list ($usec, $sec) = explode(" ", microtime());
		return ((float) $usec + (float) $sec);
	}

	public static function get_timestamp($utime, $with_time=true)
	{
		if(empty($utime))
			return '';
			
		return Date::format('@'.$utime, $with_time);
	}

	public static function format($time, $with_time=true)//, $timezone='GMT')
	{
		if(empty($time) || $time=='0000-00-00' || $time=='0000-00-00 00:00:00')
		{
			return '';
		}
		/*$d = new DateTime($time, new DateTimeZone($timezone));


		if($timezone!=$_SESSION['GO_SESSION']['timezone'])
		{
			$tz = new DateTimeZone(date_default_timezone_get());
			if($tz)
			{
				$d->setTimezone($tz);
			}
		}*/

		$date_format = $with_time ?  $_SESSION['GO_SESSION']['date_format'].' '.$_SESSION['GO_SESSION']['time_format'] : $_SESSION['GO_SESSION']['date_format'];

		return date($date_format, strtotime($time));
	}

	public static function get_timezone_offset($utime)
	{
		$d = new DateTime('@'.$utime, new DateTimeZone('GMT'));
		$tz = new DateTimeZone(date_default_timezone_get());
		if($tz)
		{
				$d->setTimezone($tz);
		}
		return $d->getOffset()/3600;
	}

	public static function ical_freq_to_repeat_type($rrule)
	{
		switch($rrule['FREQ'])
		{
			case 'WEEKLY':
				return REPEAT_WEEKLY;
				break;

			case 'DAILY':
				return REPEAT_DAILY;

				break;

			case 'MONTHLY':

				if (!isset($rrule['BYDAY']))
				{
					return REPEAT_MONTH_DATE;
				}else
				{
					return REPEAT_MONTH_DAY;
				}
				break;

			case 'YEARLY';
			return REPEAT_YEARLY;
			break;

			default:
				return REPEAT_NONE;
				break;
		}

	}


	public static function build_rrule($repeat_type, $interval, $repeat_end_time, $days, $month_time)
	{

		$rrule = 'RRULE:';

		switch($repeat_type)
		{
			case REPEAT_DAILY:
				$rrule .= 'FREQ=DAILY;';
				$rrule .= 'INTERVAL='.$interval.';';
				break;

			case REPEAT_WEEKLY:

				$event_days = array();

				if ($days['sun'] == '1')
				{
					$event_days[] = "SU";
				}
				if ($days['mon'] == '1')
				{
					$event_days[] = "MO";
				}
				if ($days['tue'] == '1')
				{
					$event_days[] = "TU";
				}
				if ($days['wed'] == '1')
				{
					$event_days[] = "WE";
				}
				if ($days['thu'] == '1')
				{
					$event_days[] = "TH";
				}
				if ($days['fri'] == '1')
				{
					$event_days[] = "FR";
				}
				if ($days['sat'] == '1')
				{
					$event_days[] = "SA";
				}

				$rrule .= "FREQ=WEEKLY;";
				$rrule .= 'INTERVAL='.$interval.';';
				$rrule .= "BYDAY=".implode(',', $event_days).';';
				break;

			case REPEAT_MONTH_DATE:
				$rrule = "RRULE:FREQ=MONTHLY;";
				$rrule .= 'INTERVAL='.$interval.';';
				$rrule .= 'BYMONTHDAY='.date('j').';';

				break;

			case REPEAT_MONTH_DAY:

				$event_days = array();

				if ($days['sun'] == '1')
				{
					$event_days[] = $month_time."SU";
				}
				if ($days['mon'] == '1')
				{
					$event_days[] = $month_time."MO";
				}
				if ($days['tue'] == '1')
				{
					$event_days[] = $month_time."TU";
				}
				if ($days['wed'] == '1')
				{
					$event_days[] = $month_time."WE";
				}
				if ($days['thu'] == '1')
				{
					$event_days[] = $month_time."TH";
				}
				if ($days['fri'] == '1')
				{
					$event_days[] = $month_time."FR";
				}
				if ($days['sat'] == '1')
				{
					$event_days[] = $month_time."SA";
				}
					
				$rrule = "RRULE:FREQ=MONTHLY;";
				$rrule .= "BYDAY=".implode(',', $event_days).';';
				$rrule .= 'INTERVAL='.$interval.';';
				break;

			case REPEAT_YEARLY:
				$rrule = "RRULE:FREQ=YEARLY;";
				$rrule .= 'INTERVAL='.$interval.';';
				break;
		}

		if ($repeat_end_time>0)
		{
			$rrule .= "UNTIL=".date('Ymd', $repeat_end_time).";";
		}

		return substr($rrule,0,-1);
	}
	
	
	public static function get_last_sunday($time)
	{
		$date = getdate($time);		
		return mktime(0,0,0,$date['mon'],$date['mday']-$date['wday'], $date['year']);
	}
}