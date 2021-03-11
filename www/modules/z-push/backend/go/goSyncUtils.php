<?php

class GoSyncUtils {

	/**
	 * Get the Group-Office Sync settings for the given user
	 * If no user is given then it will take the settings for the current user
	 *
	 * @return \GO\Sync\Model\Settings
	 */
	public static function getUserSettings($user = false) {

		if (empty($user))
			$user = \GO::user();

		return \GO\Sync\Model\Settings::model()->findForUser($user);
	}
	
//	public static function getEntityModSeq($entity) {
//		$entityModSeq = go()->getDbConnection()
//						->selectSingleValue("MAX(modSeq)")
//						->from("core_change")
//						->where([
//								"entityTypeId" => $entity->getType()->getId(), 
//								"entityId" => $entity->id
//										])
//						->single();
//		
//		if(empty($entityModSeq)) {
//			return false;
//		}
//		
//		$userModSeq = go()->getDbConnection()
//						->selectSingleValue("MAX(modSeq)")
//						->from("core_change_user")
//						->where([
//								"entityTypeId" => $entity->getType()->getId(), 
//								"entityId" => $entity->id,  
//								"userId" => go()->getUserId()
//										])
//						->single();
//		
//		if(empty($entityModSeq)) {
//			return false;
//		}
//		
//		return $entityModSeq.':'.$userModSeq;	
//		
//	}

	/**
	 * Returns the best match of preferred body preference types.
	 *
	 * @param array $bpTypes
	 *
	 * @access private
	 * @return int
	 */
	public static function getBodyPreferenceMatch($bpTypes, $supported = array(SYNC_BODYPREFERENCE_PLAIN, SYNC_BODYPREFERENCE_HTML)) {

		ZLog::Write(LOGLEVEL_DEBUG, 'GoSyncUtils->getBodyPreferenceMatch() ~~ bpTypes = ' . var_export($bpTypes, true));

		if (is_array($bpTypes)) {

			// The best choice is RTF, then HTML and then MIME in order to save bandwidth
			// because MIME is a complete message including the headers and attachments
			if (in_array(SYNC_BODYPREFERENCE_RTF, $bpTypes) && in_array(SYNC_BODYPREFERENCE_RTF, $supported))
				return SYNC_BODYPREFERENCE_RTF;
			if (in_array(SYNC_BODYPREFERENCE_HTML, $bpTypes) && in_array(SYNC_BODYPREFERENCE_HTML, $supported))
				return SYNC_BODYPREFERENCE_HTML;
			if (in_array(SYNC_BODYPREFERENCE_MIME, $bpTypes) && in_array(SYNC_BODYPREFERENCE_MIME, $supported))
				return SYNC_BODYPREFERENCE_MIME;
		}

		if (defined("BACKEND_GO_DEFAULT_BODY_PREFENCE")) {
			return BACKEND_GO_DEFAULT_BODY_PREFENCE;
		}

		return SYNC_BODYPREFERENCE_PLAIN;
	}

	/**
	 * Get the correct formatted \SyncBaseBody from an attribute of a model from GO.
	 *
	 * @param \GO\Base\Db\ActiveRecord $model
	 * @param StringHelper $attribute
	 * @param int $sbReturnType
	 * @return \SyncBaseBody
	 */
	public static function createASBodyForMessage($model, $attribute, $sbReturnType = SYNC_BODYPREFERENCE_HTML) {

		$sbBody = new SyncBaseBody();
		
		$asBodyData = \GO\Base\Util\StringHelper::normalizeCrlf($model->$attribute);

		if(!isset($asBodyData)) {
			$asBodyData = "";
		}

		if ($sbReturnType == SYNC_BODYPREFERENCE_HTML) {

			ZLog::Write(LOGLEVEL_DEBUG, 'SYNCUTILS HTML');
			
			$sbBody->type = SYNC_BODYPREFERENCE_HTML;

			$asBodyData = \GO\Base\Util\StringHelper::text_to_html($model->$attribute);
		} else {
			
			$sbBody->type = SYNC_BODYPREFERENCE_PLAIN;
			$asBodyData = \GO\Base\Util\StringHelper::normalizeCrlf($model->$attribute);
		}
		ZLog::Write(LOGLEVEL_DEBUG, $asBodyData);

		ZLog::Write(LOGLEVEL_DEBUG, 'SYNCUTILS END');

		$sbBody->estimatedDataSize = strlen($asBodyData);
		$sbBody->data = StringStreamWrapper::Open($asBodyData);
		$sbBody->truncated = 0;

		return $sbBody;
	}

	/**
	 * Get the body text of the message
	 *
	 * @param \SyncObject $message
	 * @return StringHelper
	 */
	public static function getBodyFromMessage($message) {

		if (Request::GetProtocolVersion() >= 12.0) {
			return isset($message->asbody) && isset($message->asbody->data) ? stream_get_contents($message->asbody->data) : "";
		} else {
			if (!empty($message->body))
				return $message->body;

			if (isset($message->rtf)) {
				$rtfParser = new \GO\Base\Util\Rtf();
				$rtfParser->output('ascii');
				$rtfParser->loadrtf(base64_decode($message->rtf));
				$rtfParser->parse();
				return (string) $rtfParser->out;
			}
		}
		return "";
	}

	/* Translates recurrence information in ActiveSync format to the rrule field
	 * for the tasks table or calendar event table.
	 */

	public static function importRecurrence($recur, $eventStartTime) {
		$rrule = '';
		$freq = "";
		switch ($recur->type) {
			case 0:
				$freq = "DAILY";
				break;
			case 1:
				$freq = "WEEKLY";
				break;
			case 2:
				$freq = "MONTHLY";
				break;
			case 3:
				$freq = "MONTHLY";
				break;
			case 5:
			case 6:
				$freq = "YEARLY";
				break;
		}

		if ($freq) {

			$rrule = new \GO\Base\Util\Icalendar\Rrule();
			$rrule->eventStartTime = $eventStartTime;
			$rrule->freq = $freq;
			$rrule->interval = $recur->interval;
			if (!empty($recur->until))
				$rrule->until = $recur->until;

			$rrule->byday = self::aSync2weekday($recur->dayofweek);
			if (!empty($recur->weekofmonth))
				$rrule->bysetpos = $recur->weekofmonth;

//			$rrule->shiftDays(true);

			return $rrule->createRrule();
		}else {
			return false;
		}
	}

	public static function aSync2weekday($number) {
		$weekdays = array();
		if ($number >= 128 || $number < 0) {
			throw new \Exception('The way the recurrence days were coded, is corrupted!');
		}
		if ($number >= 64) {
			$number -=64;
			$weekdays[] = 'SA';
		}
		if ($number >= 32) {
			$number -=32;
			$weekdays[] = 'FR';
		}
		if ($number >= 16) {
			$number -=16;
			$weekdays[] = 'TH';
		}
		if ($number >= 8) {
			$number -=8;
			$weekdays[] = 'WE';
		}
		if ($number >= 4) {
			$number -=4;
			$weekdays[] = 'TU';
		}
		if ($number >= 2) {
			$number -=2;
			$weekdays[] = 'MO';
		}
		if ($number >= 1) {
			$number -=1;
			$weekdays[] = 'SU';
		}
		return $weekdays;
	}

	public static function weekday2ASync($weekdays) {
		//ZLog::Write(LOGLEVEL_DEBUG, var_export($weekdays, true));
		$ASyncDay = 0;
		foreach ($weekdays as $weekday) {
			switch ($weekday) {
				case 'MO':
					$ASyncDay += 2;
					break;
				case 'TU':
					$ASyncDay += 4;
					break;
				case 'WE':
					$ASyncDay += 8;
					break;
				case 'TH':
					$ASyncDay += 16;
					break;
				case 'FR':
					$ASyncDay += 32;
					break;
				case 'SA':
					$ASyncDay += 64;
					break;
				case 'SU':
					$ASyncDay += 1;
					break;
			}
		}
		return $ASyncDay;
	}

	public static function getTimeZoneForClient() {

		if (!isset(\GO::session()->values['activesync_timezone'])) {
			$old = date_default_timezone_get();
			date_default_timezone_set(\GO::user()->timezone);
			
			$tz = new DateTimeZone(\GO::user()->timezone);
			$transitions = $tz->getTransitions();
			$start_of_year = mktime(0, 0, 0, 1, 1);

			for ($i = 0, $max = count($transitions); $i < $max; $i++) {
				if ($transitions[$i]['ts'] > $start_of_year) {
					$dst_end = $transitions[$i];
					$dst_start = $transitions[$i + 1];
					break;
				}
			}

			if (!isset($dst_end)) {
				$astz['format'] = "la64vvvvvvvv" . "la64vvvvvvvv" . "l";
				$astz['bias'] = 0;
				$astz['stdname'] = $tz->getName();
				$astz['stdyear'] = 0;
				$astz['stdmonth'] = 0;
				$astz['stdday'] = 0;

				$astz['stdweek'] = 0;
				$astz['stdhour'] = 0;
				$astz['stdminute'] = 0;
				$astz['stdmillis'] = 0;
				$astz['stdsecond'] = 0;
				$astz['stdbias'] = 0;

				$astz['dstname'] = "";
				$astz['dstyear'] = 0;
				$astz['dstmonth'] = 0;
				$astz['dstday'] = 0;
				$astz['dstweek'] = 0;
				$astz['dsthour'] = 0;
				$astz['dstminute'] = 0;
				$astz['dstsecond'] = 0;
				$astz['dstdmillis'] = 0;
				$astz['dstbias'] = 0;
			} else {
				$astz['format'] = "la64vvvvvvvv" . "la64vvvvvvvv" . "l";
				$astz['bias'] = $dst_start['offset'] / -60;
				$astz['stdname'] = $tz->getName();
				$astz['stdyear'] = 0;
				$astz['stdmonth'] = date('n', $dst_start['ts']);
				$astz['stdday'] = date('w', $dst_start['ts']);
				$stdweek = \GO\Base\Util\Date::get_occurring_number_of_day_in_month($dst_start['ts']);
				if ($stdweek == 4) {
					$stdweek = 5;
				}

				$astz['stdweek'] = $stdweek;
				$astz['stdhour'] = date('G', $dst_start['ts']);
				$astz['stdminute'] = intval(date('i', $dst_start['ts']));
				$astz['stdmillis'] = 0;
				$astz['stdsecond'] = 0;
				$astz['stdbias'] = 0;

				$astz['dstname'] = "";
				$astz['dstyear'] = 0;
				$astz['dstmonth'] = date('n', $dst_end['ts']);
				$astz['dstday'] = date('w', $dst_end['ts']);
				$dstweek = \GO\Base\Util\Date::get_occurring_number_of_day_in_month($dst_end['ts']);
				if ($dstweek == 4) {
					$dstweek = 5;
				}
				$astz['dstweek'] = $dstweek;
				$astz['dsthour'] = date('G', $dst_end['ts']);
				$astz['dstminute'] = intval(date('i', $dst_end['ts']));
				$astz['dstsecond'] = 0;
				$astz['dstdmillis'] = 0;
				$astz['dstbias'] = ($dst_end['offset'] / -60) - $astz['bias'];
			}
			date_default_timezone_set($old);
			\GO::session()->values['activesync_timezone'] = base64_encode(call_user_func_array('pack', $astz));
		}

		/* $timezone = base64_encode(
		  pack("la64vvvvvvvv" . "la64vvvvvvvv" . "l",
		  -60, //bias, the standard timezone UTC offset in minutes, in this case +2 hour

		  "Europe/Amsterdam", //stdname, we could give this timezone a name, like EET
		  0, //stdyear, the year off the timezone, 0 means every year
		  10, //stdmonth, the month the dst ends, 10 equals october
		  0, //stdday, the day the dst ends, 0 equeals sunday
		  5, //stdweek, weeknumber in the month the dst ends, where 1 will give the first dstendday of dstendmonth, 5 is the last dstendday of dstendmonth
		  2, //stdhour, the hour the dst ends
		  0, //stdminute
		  0, //stdsecond
		  0, //stdmillis
		  0, //stdbias, the difference between timezone and std in minutes usually 0

		  "", //dstname, name of dst version, like EEST
		  0, //dstyear, the year off the timezone, 0 means every year
		  3, //dstmonth, the month the dst start, 3 equals march
		  0, //dstday, the day the dst starts, 0 equeals sunday
		  5, //dstweek, weeknumber in the month the dst starts, where 1 will give the first dstendday of dstendmonth, 5 is the last dstendday of dstendmonth
		  3, //dsthour, the hour the dst starts
		  0, //dstminute
		  0, //dstsecond
		  0, //dstmillis
		  -60 //dstbias, the difference between timezone and dst in minutes usually -60
		  ));
		  return $timezone; */

		//test n900
		//return 'xP///0UAdQByAG8AcABlAC8AQQBtAHMAdABlAHIAZABhAG0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAoAAAAFAAIAAAAAAAAAAAAAAEUAdQByAG8AcABlAC8AQQBtAHMAdABlAHIAZABhAG0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMAAAAFAAMAAAAAAAAAxP///w==';
		//GMT
		/* return base64_encode(
		  pack("la64vvvvvvvv" . "la64vvvvvvvv" . "l",
		  0, "", 0, 0, 0, 0, 0, 0, 0, 0,
		  0, "", 0, 0, 0, 0, 0, 0, 0, 0,
		  0
		  )); */

		return \GO::session()->values['activesync_timezone'];
	}

	/* Translates rrule field, repeat_end_time field, and start_time field from
	 * the calendar events table to a format understandable for ActiveSync.
	 */

	public static function exportRecurrence($model) {

		$old = date_default_timezone_get();
		date_default_timezone_set($model->timezone ?? \GO::user()->timezone);

		if ($model instanceof \GO\Tasks\Model\Task)
			$recur = new SyncTaskRecurrence();
		else
			$recur = new SyncRecurrence();

		$rrule = new \GO\Base\Util\Icalendar\Rrule();
		$rrule->readIcalendarRruleString($model->start_time, $model->rrule, false);

		$recur->interval = $rrule->interval;
		if ($model->repeat_end_time > 0) {
			$recur->until = $rrule->until; //\GO\Base\Util\Date::date_add($model->repeat_end_time,1)-1; // add one day (minus 1 sec) to the end time to make sure the last occurrence is covered
		}
		
		if(!empty($rrule->count)) {
			$recur->occurrences = $rrule->count;
		}
		switch ($rrule->freq) {
			case 'DAILY':
				$recur->type = 0;
				break;
			case 'WEEKLY':
				$recur->type = 1;
				$recur->dayofweek = self::weekday2ASync($rrule->byday);
				break;
			case 'MONTHLY':
				if (isset($rrule->byday[0])) {
					$recur->type = 3;
					$recur->weekofmonth = $rrule->bysetpos;
					$recur->dayofweek = self::weekday2ASync($rrule->byday);
				} else {
					$recur->dayofmonth = date('j', $model->start_time);
					$recur->type = 2;
				}
				break;
			case 'YEARLY':
				$recur->type = 5;
				$recur->monthofyear = date('n', $model->start_time);
				$recur->dayofmonth = date('j', $model->start_time);
				break;
		}

		date_default_timezone_set($old);

		return $recur;
	}

	/**
	 * Parse a RRULE
	 * @param string $rrulestr
	 * @param string $type "task" or "event"
	 */
	public static function ParseRecurrence($rrulestr, $type) {
		$recurrence = new SyncRecurrence();
		if ($type == "task") {
			$recurrence = new SyncTaskRecurrence();
		}
		$rrules = explode(";", $rrulestr);
		foreach ($rrules as $rrule) {
			$rule = explode("=", $rrule);
			switch ($rule[0]) {
				case "FREQ":
					switch ($rule[1]) {
						case "DAILY":
							$recurrence->type = "0";
							break;
						case "WEEKLY":
							$recurrence->type = "1";
							break;
						case "MONTHLY":
							$recurrence->type = "2";
							break;
						case "YEARLY":
							$recurrence->type = "5";
					}
					break;

				case "UNTIL":
					$recurrence->until = TimezoneUtil::MakeUTCDate($rule[1]);
					break;

				case "COUNT":
					$recurrence->occurrences = $rule[1];
					break;

				case "INTERVAL":
					$recurrence->interval = $rule[1];
					break;

				case "BYDAY":
					$dval = 0;
					$days = explode(",", $rule[1]);
					foreach ($days as $day) {
						if ($recurrence->type == "2") {
							if (strlen($day) > 2) {
								$recurrence->weekofmonth = intval($day);
								$day = substr($day,-2);
							}
							else {
								$recurrence->weekofmonth = 1;
							}
							$recurrence->type = "3";
						}
						switch ($day) {
							case "SU":$dval += 1;
								break;
							case "MO":$dval += 2;
								break;
							case "TU":$dval += 4;
								break;
							case "WE":$dval += 8;
								break;
							case "TH":$dval += 16;
								break;
							case "FR":$dval += 32;
								break;
							case "SA":$dval += 64;
								break;
						}
					}
					$recurrence->dayofweek = $dval;
					break;

				//Only 1 BYMONTHDAY is supported, so BYMONTHDAY=2,3 will only include 2
				case "BYMONTHDAY":
					$days = explode(",", $rule[1]);
					$recurrence->dayofmonth = $days[0];
					break;

				case "BYMONTH":
					$recurrence->monthofyear = $rule[1];
					break;

				default:
					ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->_ParseRecurrence(): '%s' is not yet supported.", $rule[0]));
			}
		}
		return $recurrence;
	}

	/**
	 * @param object $rec ActiveSync format rrule
	 * @param bool $allday
	 * @return array JMAP format array of rrule
	 */
	public static function GenerateRecurrence($rec, $allday = true) {
		$rrule = [];
		if (isset($rec->type)) {
			$freq = "";
			switch ($rec->type) {
				case "0":
					$freq = "DAILY";
					break;
				case "1":
					$freq = "WEEKLY";
					break;
				case "2":
				case "3":
					$freq = "MONTHLY";
					break;
				case "5":
					$freq = "YEARLY";
					break;
			}
			$rrule['frequency'] = $freq;
		}
		if (isset($rec->until)) {
			$rrule['until'] = gmdate($allday ? "Ymd" : "Ymd\THis\Z", $rec->until);
		}
		if (isset($rec->occurrences)) {
			$rrule['count'] = $rec->occurrences;
		}
		if (isset($rec->interval)) {
			$rrule['interval'] = $rec->interval;
		}
		if (isset($rec->dayofweek)) {
			$week = '';
			if (isset($rec->weekofmonth)) {
				$week = $rec->weekofmonth;
			}
			$days = [];
			if (($rec->dayofweek & 1) == 1)
				$days[] = $week . "SU";
			if (($rec->dayofweek & 2) == 2)
				$days[] = $week . "MO";
			if (($rec->dayofweek & 4) == 4)
				$days[] = $week . "TU";
			if (($rec->dayofweek & 8) == 8)
				$days[] = $week . "WE";
			if (($rec->dayofweek & 16) == 16)
				$days[] = $week . "TH";
			if (($rec->dayofweek & 32) == 32)
				$days[] = $week . "FR";
			if (($rec->dayofweek & 64) == 64)
				$days[] = $week . "SA";
			$rrule['byDay'] = $days;
		}
		if (isset($rec->dayofmonth)) {
			$rrule['byMonthDay'] = explode(',',$rec->dayofmonth);
		}
		if (isset($rec->monthofyear)) {
			$rrule['byMonth'] = explode(',',$rec->monthofyear);
		}
		return $rrule;
	}

}
