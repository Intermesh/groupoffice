<?php /** @noinspection PhpDeprecationInspection */

use GO\Base\Db\ActiveRecord;
use GO\Base\Model\User;
use GO\Base\Util\Date;
use GO\Base\Util\Icalendar\Rrule;
use GO\Base\Util\Rtf as RtfAlias;
use GO\Base\Util\StringHelper;
use GO\Calendar\Model\Event;
use go\core\orm\Entity;
use go\core\util\StringUtil;
use GO\Sync\Model\Settings as SyncSettings;

class GoSyncUtils {


	/**
	 * Get the Group-Office Sync settings for the given user
	 * If no user is given then it will take the settings for the current user
	 *
	 * @param ?User $user
	 * @return SyncSettings
	 */
	public static function getUserSettings(?User $user = null): SyncSettings
	{

		if (empty($user))
			$user = GO::user();

		/** @noinspection PhpUndefinedMethodInspection */
		return SyncSettings::model()->findForUser($user);
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
	 * @param ?array|false $bpTypes
	 * @param array $supported
	 * @return int
	 */
	public static function getBodyPreferenceMatch($bpTypes, array $supported = array(SYNC_BODYPREFERENCE_PLAIN, SYNC_BODYPREFERENCE_HTML)): int
	{

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
	 * @param ActiveRecord|Entity $model
	 * @param string $attribute
	 * @param int $sbReturnType
	 * @return SyncBaseBody
	 */
	public static function createASBodyForMessage($model, string $attribute, int $sbReturnType = SYNC_BODYPREFERENCE_HTML): SyncBaseBody
	{
		$sbBody = new SyncBaseBody();

		$asBodyData = StringUtil::normalizeCrlf($model->$attribute);

		if(!isset($asBodyData)) {
			$asBodyData = "";
		}

		if ($sbReturnType == SYNC_BODYPREFERENCE_HTML) {

			ZLog::Write(LOGLEVEL_DEBUG, 'SYNCUTILS HTML');
			
			$sbBody->type = SYNC_BODYPREFERENCE_HTML;

			$asBodyData = StringHelper::text_to_html($asBodyData);
		} else {
			
			$sbBody->type = SYNC_BODYPREFERENCE_PLAIN;
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
	 * @param SyncObject $message
	 * @return string
	 */
	public static function getBodyFromMessage(SyncObject $message): string
	{

		if (Request::GetProtocolVersion() >= 12.0) {

			if (!isset($message->asbody->data)) {
				return "";
			}

			if (isset($message->asbody->type) && $message->asbody->type == SYNC_BODYPREFERENCE_RTF) {
				$rtfparser = new z_RTF();
				$rtfparser->loadrtf(base64_decode(stream_get_contents($message->asbody->data)));
				$rtfparser->output("ascii");
				$rtfparser->parse();
				return $rtfparser->out;
			} else if (isset($message->asbody->type) && $message->asbody->type == SYNC_BODYPREFERENCE_HTML) {
				$html = (string) stream_get_contents($message->asbody->data);
				return StringUtil::htmlToText($html);
			} else {
				return stream_get_contents($message->asbody->data);
			}

		} else {
			if (!empty($message->body))
				return $message->body;

			if (isset($message->rtf)) {
				$rtfParser = new RtfAlias();
				$rtfParser->output('ascii');
				$rtfParser->loadrtf(base64_decode($message->rtf));
				$rtfParser->parse();
				return $rtfParser->out;
			}
		}
		return "";
	}

	/* Translates recurrence information in ActiveSync format to the rrule field
	 * for the tasks table or calendar event table.
	 */

	/**
	 * @param SyncRecurrence|SyncTaskRecurrence $recur
	 * @param int $eventStartTime
	 * @return false|string
	 * @throws Exception
	 */
	public static function importRecurrence($recur, int $eventStartTime) {
		$freq = "";
		switch ($recur->type) {
			case 0:
				$freq = "DAILY";
				break;
			case 1:
				$freq = "WEEKLY";
				break;
			case 2:
			case 3:
				$freq = "MONTHLY";
				break;
			case 5:
			case 6:
				$freq = "YEARLY";
				break;
		}

		if ($freq) {
			$rrule = new Rrule();
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

	/**
	 * @throws Exception
	 */
	public static function aSync2weekday(?int $number): array
	{
		$weekdays = array();
		if ($number >= 128 || $number < 0) {
			throw new Exception('The way the recurrence days were coded, is corrupted!');
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
			$weekdays[] = 'SU';
		}
		return $weekdays;
	}

	public static function weekday2ASync(array $weekdays): int
	{
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

	private static $knownMSTZS = array(
		"-780/-60/0/0/0/0/0/0/0/0"=>"Pacific/Enderbury"
	,"-720/-60/4/1/0/3/9/5/0/2"=>"Pacific/Auckland"
	,"-660/-60/0/0/0/0/0/0/0/0"=>"Antarctica/Casey"
	,"-600/-60/4/1/0/3/10/1/0/2"=>"Australia/Melbourne"
	,"-600/-60/0/0/0/0/0/0/0/0"=>"Australia/Brisbane"
	,"-570/-60/4/1/0/3/10/1/0/2"=>"Australia/Adelaide"
	,"-570/-60/0/0/0/0/0/0/0/0"=>"Australia/Darwin"
	,"-540/-60/0/0/0/0/0/0/0/0"=>"Asia/Chita"
	,"-480/-60/0/0/0/0/0/0/0/0"=>"Asia/Brunei"
	,"-420/-60/0/0/0/0/0/0/0/0"=>"Antarctica/Davis"
	,"-390/-60/0/0/0/0/0/0/0/0"=>"Asia/Yangon"
	,"-360/-60/0/0/0/0/0/0/0/0"=>"Antarctica/Vostok"
	,"-345/-60/0/0/0/0/0/0/0/0"=>"Asia/Kathmandu"
	,"-330/-60/0/0/0/0/0/0/0/0"=>"Asia/Colombo"
	,"-300/-60/0/0/0/0/0/0/0/0"=>"Antarctica/Mawson"
	,"-270/-60/0/0/0/0/0/0/0/0"=>"Asia/Kabul"
	,"-210/-60/9/3/4/22/3/3/3/22"=>"Asia/Tehran"
	,"-180/-60/0/0/0/0/0/0/0/0"=>"Africa/Addis_Ababa"
	,"-120/-60/10/5/0/4/3/5/0/3"=>"Europe/Helsinki"
	,"-120/-60/0/0/0/0/0/0/0/0"=>"Africa/Blantyre"
	,"-60/-60/10/5/0/3/3/5/0/2"=>"Europe/Berlin"
	,"-60/-60/10/4/0/3/3/5/0/2"=>"Europe/Berlin"
	,"-60/-60/0/0/0/0/0/0/0/0"=>"Africa/Algiers"
	,"0/-60/10/5/0/2/3/5/0/1"=>"Europe/Dublin"
	,"0/-60/10/4/0/2/3/5/0/1"=>"Europe/Dublin"
	,"0/-60/0/0/0/0/0/0/0/0"=>"Africa/Abidjan"
	,"60/-60/0/0/0/0/0/0/0/0"=>"Atlantic/Cape_Verde"
	,"180/-60/2/4/6/23/10/3/6/23"=>"America/Sao_Paulo"
	,"180/-60/10/5/6/23/3/4/6/22"=>"America/Godthab"
	,"180/-60/0/0/0/0/0/0/0/0"=>"America/Araguaina"
	,"240/-60/11/1/0/2/3/2/0/2"=>"America/Barbados"
	,"240/-60/0/0/0/0/0/0/0/0"=>"America/Anguilla"
	,"270/-60/0/0/0/0/0/0/0/0"=>"America/Caracas"
	,"300/-60/11/1/0/2/3/2/0/2"=>"America/New_York"
	,"300/-60/0/0/0/0/0/0/0/0"=>"America/Atikokan"
	,"360/-60/11/1/0/2/3/2/0/2"=>"America/Chicago"
	,"360/-60/0/0/0/0/0/0/0/0"=>"America/Belize"
	,"420/-60/10/5/0/2/4/1/0/2"=>"America/Chihuahua"
	,"420/-60/11/1/0/2/3/2/0/2"=>"America/Denver"
	,"420/-60/0/0/0/0/0/0/0/0"=>"America/Creston"
	,"480/-60/11/1/0/2/3/2/0/2"=>"America/Los_Angeles"
	,"540/-60/11/1/0/2/3/2/0/2"=>"America/Anchorage"
	,"600/-60/0/0/0/0/0/0/0/0"=>"Pacific/Honolulu"
	);

	/**
	 * Given the MS timezone find a matching tzid, for the year the event starts in.
	 * @param string $mstz
	 * @param int $eventstart
	 * @return string
	 */
	public static function tzidFromMSTZ(string $mstz, int $eventstart) : ?string {
		// 1. Check known MS time zones
		$mstz_parts = unpack("lbias/Z64tzname/vdstendyear/vdstendmonth/vdstendday/vdstendweek/vdstendhour/"
			."vdstendminute/vdstendsecond/vdstendmillis/lstdbias/Z64tznamedst/vdststartyear/"
			."vdststartmonth/vdststartday/vdststartweek/vdststarthour/vdststartminute/"
			."vdststartsecond/vdststartmillis/ldstbias", base64_decode($mstz));
		$mstz = $mstz_parts['bias']
			."/".$mstz_parts['dstbias']
			."/".$mstz_parts['dstendmonth']
			."/".$mstz_parts['dstendweek']
			."/".$mstz_parts['dstendday']
			."/".$mstz_parts['dstendhour']
			."/".$mstz_parts['dststartmonth']
			."/".$mstz_parts['dststartweek']
			."/".$mstz_parts['dststartday']
			."/".$mstz_parts['dststarthour'];
		if (isset(self::$knownMSTZS[$mstz])) {
			$tzid = self::$knownMSTZS[$mstz];
			ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->tzidFromMSTZ(): Found tzid in known list: '%s'.", $tzid));
			return $tzid;
		}

		// 2. Loop all time zones to find a match on offset and transition date
		$year = date("Y", $eventstart);
		$offset_std = -($mstz_parts["bias"] * 60);
		$offset_dst = -(($mstz_parts["bias"] + $mstz_parts["dstbias"]) * 60);
		$dststart_timestamp = self::timestampFromMSTZ($mstz_parts, "dststart", $mstz_parts["bias"], $year);
		$dstend_timestamp = self::timestampFromMSTZ($mstz_parts, "dstend", $mstz_parts["bias"] + $mstz_parts["dstbias"], $year);

		$tzids = DateTimeZone::listIdentifiers();
		foreach ($tzids as $tzid) {
			$timezone = new DateTimeZone($tzid);
			$transitions = $timezone->getTransitions(date("U", strtotime($year."0101T000000Z")), date("U", strtotime($year."1231T235959Z")));

			$tno = count($transitions);
			if ($tno == 1 && $dststart_timestamp == 0) {
				if ($transitions[0]['offset'] == $offset_std) {
					ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->tzidFromMSTZ(): Found tzid: '%s'.", $tzid));
					ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->tzidFromMSTZ(): Add tzid to knownMSTZS array for better performance: '%s'.", ',"'.$mstz.'"=>"'.$tzid.'"'));
					return $tzid;
				}
			}
			else if (($tno == 3 || $tno == 5) && $dststart_timestamp != 0) {
				if ($dststart_timestamp < $dstend_timestamp) {
					if(
						$transitions[1]['isdst'] == 1 &&
						$transitions[1]['ts'] == $dststart_timestamp &&
						$transitions[1]['offset'] == $offset_dst &&
						$transitions[2]['isdst'] == 0 &&
						$transitions[2]['ts'] == $dstend_timestamp &&
						$transitions[2]['offset'] == $offset_std)
					{
						ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->tzidFromMSTZ(): Found tzid: '%s'.", $tzid));
						ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->tzidFromMSTZ(): Add tzid to knownMSTZS array for better performance: '%s'.", ',"'.$mstz.'"=>"'.$tzid.'"'));
						return $tzid;
					}
				}
				else {
					if (
						$transitions[1]['isdst'] == 0 &&
						$transitions[1]['ts'] == $dstend_timestamp &&
						$transitions[1]['offset'] == $offset_std &&
						$transitions[2]['isdst'] == 1 &&
						$transitions[2]['ts'] == $dststart_timestamp &&
						$transitions[2]['offset'] == $offset_dst)
					{
						ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->tzidFromMSTZ(): Found tzid: '%s'.", $tzid));
						ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->tzidFromMSTZ(): Add tzid to knownMSTZS array for better performance: '%s'.", ',"'.$mstz.'"=>"'.$tzid.'"'));
						return $tzid;
					}
				}
			}
		}

		// 3. Give up, use Zulu
		ZLog::Write(LOGLEVEL_WARN, sprintf("BackendCalDAV->tzidFromMSTZ(): Failed to find tzid, defaulting to UTC. MS time zone: '%s'.", join('/', $mstz_parts)));
		return null;
	}

	public static function getTimeZoneForClient() {

		if (!isset(GO::session()->values['activesync_timezone'])) {
			$old = date_default_timezone_get();
			date_default_timezone_set(GO::user()->timezone);
			
			$tz = new DateTimeZone(GO::user()->timezone);
			$transitions = $tz->getTransitions();
			$start_of_year = mktime(0, 0, 0, 1, 1);

			for ($i = 0, $max = count($transitions); $i < $max; $i++) {
				if ($transitions[$i]['ts'] > $start_of_year) {
					$dst_end = $transitions[$i];
					$dst_start = $transitions[$i + 1];
					break;
				}
			}

			$astz['format'] = "la64vvvvvvvv" . "la64vvvvvvvv" . "l";
			if (!isset($dst_end) || !isset($dst_start)) {
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
				$astz['bias'] = $dst_start['offset'] / -60;
				$astz['stdname'] = $tz->getName();
				$astz['stdyear'] = 0;
				$astz['stdmonth'] = date('n', $dst_start['ts']);
				$astz['stdday'] = date('w', $dst_start['ts']);
				$stdweek = Date::get_occurring_number_of_day_in_month($dst_start['ts']);
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
				$dstweek = Date::get_occurring_number_of_day_in_month($dst_end['ts']);
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
			GO::session()->values['activesync_timezone'] = base64_encode(call_user_func_array('pack', $astz));
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

		return GO::session()->values['activesync_timezone'];
	}

	/**
	 * Translates rrule field, repeat_end_time field, and start_time field from
	 * the calendar events table to a format understandable for ActiveSync.
	 * @param Event $model
	 * @return SyncRecurrence
	 */
	public static function exportRecurrence(Event $model): SyncRecurrence
	{
		$old = date_default_timezone_get();
		date_default_timezone_set($model->timezone ?? GO::user()->timezone);

		$recur = new SyncRecurrence();

		$rrule = new Rrule();
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
	 * @return SyncRecurrence|SyncTaskRecurrence
	 */
	public static function ParseRecurrence(string $rrulestr, string $type) {
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
	 * @param SyncRecurrence|SyncTaskRecurrence $rec ActiveSync format rrule
	 * @param bool $allday
	 * @return array JMAP format array of rrule
	 */
	public static function GenerateRecurrence($rec, bool $allday = true): array
	{
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
