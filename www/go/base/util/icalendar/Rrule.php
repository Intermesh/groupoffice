<?php
/*
 * Copyright Intermesh BV
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

/**
 * Read and build RRULE strings into a recurrence pattern object
 * 
 * @copyright Copyright Intermesh BV
 * @version $Id: Number.php 7962 2011-08-24 14:48:45Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.util.icalendar
 */

namespace GO\Base\Util\Icalendar;

/**
 * This class is legacy code
 * - It is still used for tasks
 * - It contains "readJsonArray" and "createRrule" that is used to convert Group-Office RRUle post
 * - It is used in calendar/install/updatescripts/5_revert_rrule_shifting.php to unshift the old RRules
 *
 * @deprecated since version 6.1.106
 */
class Rrule extends \GO\Base\Util\Date\RecurrencePattern
{
	/**
	 * Create a Rrule object from a Rrule string. This function automatically finds 
	 * out which Rrule version is used. 
	 *
	 * @param String $eventstarttime The time the recurrence pattern starts. This is important to calculate the correct interval.
	 * @param String $rrule 'FREQ=DAILY;UNTIL=22-02-2222;INTERVAL=2;
	 */
	public function readIcalendarRruleString($eventstarttime, $rrule, $shiftDaysToLocal=false) {
		if(!empty($rrule)){			
			$this->_eventstarttime = $eventstarttime;
			$rrule = str_replace('RRULE:', '', $rrule);
			if (strpos($rrule, 'FREQ') === false){
				if(!$this->_parseRruleIcalendarV1($rrule))
					return false;
			}else{
				if(!$this->_parseRruleIcalendar($rrule))
					return false;
			}
			
			if($this->_until){
				$this->_until = \GO\Base\Util\Date::date_add(
							\GO\Base\Util\Date::clear_time($this->_until),
							0,
							0,
							0,
							23,//date('H', $this->_eventstarttime), 
							59//date('i', $this->_eventstarttime)
							);
			}
			
			if($shiftDaysToLocal)
				$this->shiftDays(false);
		}
		
	}
	
		
	public function readJsonArray($json)
	{
		$parameters = array();

		$parameters['byday']=array();
		$parameters['interval'] = \GO\Base\Util\Number::unlocalize($json['interval']);
		$parameters['freq'] = strtoupper($json['freq']);
		if ($parameters['freq'] == 'MONTHLY_DATE') {
			$parameters['freq'] = 'MONTHLY';
		} else {

			foreach($this->_days as $day){
				if(!empty($json[$day])){
					$day = $day;
//				if(!empty($json['bysetpos']))
//					$day = $json['bysetpos'].$day;

					$parameters['byday'][]=$day;
				}
			}
		}
		$parameters['eventstarttime'] = isset($json['eventstarttime'])?\GO\Base\Util\Date::to_unixtime($json['eventstarttime']):\GO\Base\Util\Date::to_unixtime($json['start_time']);
		$parameters['until'] = !empty($json['repeat_UntilDate']) && isset($json['until']) ? \GO\Base\Util\Date::to_unixtime($json['until'].' 23:59') : 0; //date('G', $parameters['eventstarttime']).':'.date('i', $parameters['eventstarttime'])) : 0;
		$parameters['bymonth'] = isset($json['bymonth'])?$json['bymonth']:'';
		$parameters['bymonthday'] = isset($json['bymonthday'])?$json['bymonthday']:'';
		$parameters['count'] = !empty($json['repeat_count']) && isset($json['count']) ? $json['count'] : 0;

		//\GO::debug($parameters['bymonthday']);
		
		//bysetpos is not understood by old lib
		$parameters['bysetpos']=isset($json['bysetpos']) ? $json['bysetpos'] : 1;
		
		// Weekly recurrence _must_ have BYDAY set.
		if (strtolower($parameters['freq'])=='weekly' && count($parameters['byday'])<1 ) {
			$dayInt = date('w',$parameters['eventstarttime']);
			$parameters['byday'][] = $this->_days[$dayInt];
		}
		// Monthly by day recurrence _must_ have valid BYSETPOS and BYDAY.
		else if (strtolower($parameters['freq'])=='monthly' && isset($json['bysetpos'])) {
			if (count($parameters['byday'])<1)
				throw new \Exception(\GO::t("Please select a week day for the monthly recurrence."));
			else if (empty($json['bysetpos']))
				throw new \Exception(\GO::t("Please select the number of the week day for the monthly recurrence."));
		}
		
		$this->setParams($parameters);
		
		//$this->shiftDays();
	}
	
		
	/**
	 * Output a rrule
	 * 
	 * @return String $rrule eg.: 'FREQ=DAILY;UNTIL=22-02-2222;INTERVAL=2;
	 */
	public function createRrule() {
		
		if(empty($this->_freq))
			return "";
		
		$rrule = 'FREQ='.$this->_freq;
		
		if($this->_interval>1)
			$rrule .= ';INTERVAL='.$this->_interval;

		$this->_byday = is_array($this->_byday) ? $this->_byday : array($this->_byday);
		switch($this->_freq)
		{
			case 'WEEKLY':
				$rrule .= ";BYDAY=".implode(',', $this->_byday);
			break;
			case 'MONTHLY':				
				if(!empty($this->_bymonthday)){
					$this->_bymonthday = is_array($this->_bymonthday) ? $this->_bymonthday : array($this->_bymonthday);
					$rrule .= ';BYMONTHDAY='.implode(',',$this->_bymonthday); // yo date('j', $this->_eventstarttime);
				}elseif (!empty($this->_byday))
				{
					
					if(!empty($this->_bysetpos)){
						
						$self = $this;
						$byday = array_map(function($day) use($self) {
							return $self->_bysetpos . $day;
						}, $this->_byday);
					} else {
						$byday = $this->_byday;
					}
					
					$rrule .= ';BYDAY='.implode(',', $byday);
				}
			break;
		}
			
		if ($this->_until>0)
		{
			//$rrule .= ";UNTIL=".gmdate('Ymd\\THis\\Z', $this->_until);
			$rrule .= ";UNTIL=".date('Ymd\\THis', $this->_until);
		}else if($this->_count > 0 && $this->_until == 0) {
			$rrule .= ";COUNT=".$this->_count;
		}
		return $rrule;
	}
	
	
	/**
	 * Output a vcalendar 1.0 rrule
	 * 
	 * @return String $rrule eg.: 'FREQ=DAILY;UNTIL=22-02-2222;INTERVAL=2;
	 */
	public function createVCalendarRrule() {
		
		$rrule = 'RRULE:';

		switch($this->_freq)
		{
			case 'DAILY':
				$rrule .= 'D'.$this->_interval;
				break;
			case 'WEEKLY':
				$rrule .= "W".$this->_interval." ".implode(' ', $this->_byday);
			break;

			case 'MONTHLY':				
				if(empty($this->_byday)){
					$rrule .= 'MD'.$this->_interval.' '.date('j', $this->_eventstarttime);
				}else
				{
					$rrule .= 'MP'.$this->_interval.' '.$this->_bysetpos.'+ '.implode(',', $this->_byday);
				}
			break;
			
			case 'YEARLY':
				$rrule .= 'YM'.$this->_interval;
				break;
		}
			
		if ($this->_until>0)
		{			
			$rrule .= " ".date('Ymd\THis', $this->_until);
		}else
		{
			$rrule .= " #0";
		}
		return $rrule;
	}

	/**
	 * Set the values of this object from a version 1.0 Icalendar Rrule
	 * @TODO: This function is not yet changed for the new go version
	 * This must be a vcalendar 1.0 rrule
	 */
	private function _parseRruleIcalendarV1($rrule) {

		
		//we are attempting to convert it to icalendar format
		//GO Supports only one rule everything behind the first rule is chopped
		
		$hek_pos = strpos($rrule, '#');
		if ($hek_pos) {
			$space_pos = strpos($rrule, ' ', $hek_pos);
			if ($space_pos) {
				return false;
				//$rrule = substr($rrule,0,$space_pos);
			}
		}

		$expl_rrule = explode(' ', $rrule);
		
		$this->_until=0;
		//the count or until is always in the last element
		if ($until = array_pop($expl_rrule)) {
			
			if(empty($until))
				return false;
			
			if ($until{0} == '#') {
				$count = substr($until, 1);
				if ($count > 0) {
					$this->_count = $count;
				}

				if (strlen($expl_rrule[count($expl_rrule) - 1]) > 2) {
					//this must be the end date
					$this->_until = intval(\GO\Base\Util\Date::parseIcalDate(array_pop($expl_rrule)));
				}
			} else {
				$this->_until = intval(\GO\Base\Util\Date::parseIcalDate($until));
			}
		}


		if ($this->_freq = array_shift($expl_rrule)) {

			$this->_interval = '';

			$lastchar = substr($this->_freq, -1, 1);
			while (is_numeric($lastchar)) {
				$this->_interval = $lastchar . $this->_interval;
				$this->_freq = substr($this->_freq, 0, strlen($this->_freq) - 1);
				$lastchar = substr($this->_freq, -1, 1);
			}

			switch ($this->_freq) {
				case 'D':
					$this->_freq = 'DAILY';
					break;

				case 'W':
					$this->_freq = 'WEEKLY';
					$this->_byday = $expl_rrule;
					break;

				case 'MP':
					$this->_freq = 'MONTHLY';

					//GO Supports only one position in the month
					/* if(count($expl_rrule) > 1)
					  {
					  //return false;
					  } */
					$month_time = array_shift($expl_rrule);
					//todo negative month times
					$this->_byday = array(substr($month_time, 0, -1) . array_shift($expl_rrule));
					break;

				case 'MD':
					$this->_freq = 'MONTHLY';
					//GO Supports only one position in the month
					if (count($expl_rrule) > 1) {
						return false;
					}

					$month_time = array_shift($expl_rrule);
					//todo negative month times
					//$this->_bymonthday = substr($month_time, 0, strlen($month_time)-1);
					//for nexthaus
					$this->_bymonthday = trim($month_time); //substr($month_time, 0, strlen($month_time)-1);
					break;

				case 'YM':
					$this->_freq = 'YEARLY';
					//GO Supports only one position in the month
					if (count($expl_rrule) > 1) {
						return false;
					}
					$this->_bymonth = array_shift($expl_rrule);
					break;

				default:
				case 'YD':
					//Currently not supported by GO
					return false;
					break;
			}
			
			return true;
		}else
		{
			return false;
		}
	}
	
	private function _splitDaysAndSetPos(){

		foreach($this->_byday as $k => $day) {
			if(strlen($day)>2){
				if(empty($this->_bysetpos)) {
					$this->_bysetpos = substr($day,0,-2);
				}
				$this->_byday[$k] = substr($day,-2);
			}
		}
	}

	/**
	 * Convert a Rrule object from an Icalendar Rrule string.
	 * 
	 * Set the values of this object from the latest version of an Icalendar Rrule
	 */
	private function _parseRruleIcalendar($rrule) {
		$params = explode(';', $rrule);

		while ($param = array_shift($params)) {
			$param_arr = explode('=', $param);

			if (isset($param_arr[0]) && isset($param_arr[1])) {
				$rrule_arr[strtoupper(trim($param_arr[0]))] = strtoupper(trim($param_arr[1]));
			}
		}

		$this->_byday = !empty($rrule_arr['BYDAY']) ? explode(',', $rrule_arr['BYDAY']) : array();
		$this->_bymonth = !empty($rrule_arr['BYMONTH']) ? intval($rrule_arr['BYMONTH']) : 0;
		$this->_bymonthday = !empty($rrule_arr['BYMONTHDAY']) ? explode(',',str_replace(' ','',$rrule_arr['BYMONTHDAY'])) : array();
		$this->_freq = !empty($rrule_arr['FREQ']) ? $rrule_arr['FREQ'] : '';
		$this->_until = isset($rrule_arr['UNTIL']) ? intval(\GO\Base\Util\Date::parseIcalDate($rrule_arr['UNTIL'])) : 0;
		$this->_count = !empty($rrule_arr['COUNT']) ? intval($rrule_arr['COUNT']) : 0;
		$this->_interval = !empty($rrule_arr['INTERVAL']) ? intval($rrule_arr['INTERVAL']) : 1;
		$this->_bysetpos = !empty($rrule_arr['BYSETPOS']) ? intval($rrule_arr['BYSETPOS']) : 0;


		//fix for thundebird sending: RRULE:FREQ=DAILY;UNTIL=20150326T080000Z;BYDAY=MO,TU,WE,TH,FR
		//it should work as RRULE:FREQ=WEEKLY;UNTIL=20150326T080000Z;BYDAY=MO,TU,WE,TH,FR
		
		if(!empty($this->_byday) && $this->_freq == 'DAILY'){
			$this->_freq = 'WEEKLY';
		}
		
		if($this->_bysetpos<0) {
			throw new \Exception("'Last X of month' recurrence pattern currently not supported by Group-Office.");
		}
		
		
		$this->_splitDaysAndSetPos();
		
		//if rrule is passed like this: RRULE:INTERVAL=1;FREQ=WEEKLY;BYDAY=
		//then assume days should be the event start time day.
		if(isset($rrule_arr['BYDAY']) && empty($this->_byday))
			$this->_byday=array($this->_days[gmdate('w', $this->_eventstarttime)]);
		
		
//		if($this->_freq == 'YEARLY' && (!empty($this->_bymonth) || !empty($this->_bymonth))) {
//			throw new \Exception("Sorry, this recurrence pattern is not supported by Group-Office");
//		}
		
		return true;
	}
	
	/**
	 * Creates a Rrule response which can be merged with a normal JSON response.
	 * 
	 * @return array Rrule 
	 */
	public function createJSONOutput() {
		
		//$this->shiftDays(false);
		$days = $this->_byday;
		
		$response = array();
		if (isset($this->_freq)) {
			if (!empty($this->_until)){
				$response['until'] = \GO\Base\Util\Date::get_timestamp($this->_until, false);
				$response['repeat_forever'] = 0;
				$response['repeat_UntilDate'] = 1;
				$response['repeat_count'] = 0;
			}else if (!empty ($this->_count)) {
				$response['count'] = $this->_count;
				$response['repeat_forever'] = 0;
				$response['repeat_UntilDate'] = 0;
				$response['repeat_count'] = 1;
			} else {
				$response['repeat_forever'] = 1;
				$response['repeat_UntilDate'] = 0;
				$response['repeat_count'] = 0;
			}
			
			
			$response['interval'] = $this->_interval;
			$response['freq'] = $this->_freq;
			switch ($this->_freq) {

				case 'WEEKLY':
					
					foreach($days as $day)
						$response[$day]=1;
					break;

				case 'MONTHLY':
					$response['bysetpos'] = $this->bysetpos;
					if (!empty($days)) {						
						foreach($days as $day)
							$response[$day]=1;						
					} 
					
					if($this->bysetpos==0)
						$response['freq']='MONTHLY_DATE';
					break;
			}
		}
		return $response;
	}	
}
