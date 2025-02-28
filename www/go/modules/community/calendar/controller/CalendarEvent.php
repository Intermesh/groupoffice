<?php

namespace go\modules\community\calendar\controller;

use go\core\fs\Blob;
use go\core\jmap\EntityController;
use go\core\util\StringUtil;
use go\core\util\UUID;
use GO\Email\Model\Account;
use go\modules\community\addressbook\convert\VCard;
use go\modules\community\calendar\model;
use go\modules\community\calendar\model\ICalendarHelper;
use go\modules\community\calendar\Module;


/**
 * CalendarEvent controller.
 * NOTE: The extra get and set parameter in the docblocks are according to RFC and might not (yet) be implemented
 */
class CalendarEvent extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\CalendarEvent::class;
	}	
	
	/**
	 * @param array $params
	 * @param bool $expandRecurrences: Boolean (default: false) If true, the server will expand any recurring event.
	 * 	If true, the filter MUST be just a FilterCondition (not a FilterOperator) and MUST include both a before
	 * 	and after property. This ensures the server is not asked to return an infinite number of results.
	 *	@param string $timeZone: String The time zone for before/after filter conditions (default: “Etc/UTC”)
	 */
	public function query($params) {
		return $this->defaultQuery($params);
	}
	
	/**
	 * @param array $params
	 * @param ?string $recurrenceOverridesBefore: UTCDate|null If given, only recurrence overrides with a recurrence id
	 * 	before this date (when translated into UTC) will be returned.
	 *	@param ?string $recurrenceOverridesAfter: UTCDate|null If given, only recurrence overrides with a recurrence id
	 * 	on or after this date (when translated into UTC) will be returned.
	 *	@param boolean $reduceParticipants: Boolean (default: false) If true, only participants with the “owner” role
	 * 	or corresponding to the user’s participant identities will be returned in the “participants” property
	 * 	of the base event and any recurrence overrides. If false, all participants will be returned.
	 *	@param string $timeZone: String (default “Etc/UTC”) The time zone to use when calculating the utcStart/utcEnd
	 * 	property of floating events. This argument has no effect if those properties are not requested.
	 */
	public function get($params) {
		return $this->defaultGet($params);
	}
	
	/**
	 * @param array $params
	 * @param bool $sendSchedulingMessages (default: false) If true then any changes to scheduled events will be sent
	 * to all the participants (if the user is an owner of the event) or back to the owners (otherwise). If false, the
	 * changes only affect this account and no scheduling messages will be sent.
	 */
	public function set($params) {
		if(!empty($params['sendSchedulingMessages'])) {
			model\CalendarEvent::$sendSchedulingMessages = true;
		}
		model\CalendarEvent::$fromClient = true;
		return $this->defaultSet($params);
	}
	
	
	/**
	 * Handles the Foo entity's Foo/changes command
	 * 
	 * @param array $params
	 */
	public function changes($params) {
		return $this->defaultChanges($params);
	}

	/**
	 * Parse an iCalendar blobId to an CalendarEvent object
	 * @param $params [ blobId ]
	 * @return void
	 */
	public function parse($params) {

	}

	private function b64UrlEncode(string $data) : string{
		return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
	}

	public function generateJWT(array $params) : array {
		$s = Module::get()->getSettings();

		$header = $this->b64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
		$payload = $this->b64UrlEncode(json_encode([
//			"context" => [],
			'aud' => $s['videoJwtAppId'],
			'iss' => $s['videoJwtAppId'],
			'room' => $params['room'],
			'sub' => '*',
			'exp' => strtotime('+30 days'),
		]));

		$signature = hash_hmac('sha256', "$header.$payload", $s['videoJwtSecret'], true);

		return [
			'success' => true,
			'jwt' => "$header.$payload." . $this->b64UrlEncode($signature)
		];
	}


	public function loadICS( array $params) :array
	{
		if(!empty($params['fileId'])) {
			$file = \GO\Files\Model\File::model()->findByPk($params['fileId']);
			$data = $file->fsFile->getContents();
		} else {
			$account = Account::model()->findByPk($params['account_id']);
			$imap = $account->openImapConnection($params['mailbox']);
			$data = $imap->get_message_part_decoded($params['uid'], $params['number'], $params['encoding'], false, true, false);
		}

		$event = new model\CalendarEvent();
		$event->calendarId = go()->getAuthState()->getUser(['calendarPreferences'])->calendarPreferences->defaultCalendarId;
		//$event = model\ICalendarHelper::fromICal($data, $event);
		$event = ICalendarHelper::parseVObject($data, $event);
		return ['success' => true, 'data' => $event];

	}

	/**
	 * @param $params array 'blobIds' and 'calendarId', 'ignoreUid' are required
	 * @throws \Exception
	 */
	public function import($params) {
		$r = (object)[
			'saved'=>0,
			'failed'=>0,
			'skipped'=>0,
			'failureReasons'=>[]
		];
		foreach($params['blobIds'] as $blobId) {
			foreach(model\ICalendarHelper::calendarEventFromFile($blobId) as $ev) {
				if(is_array($ev)){
					$r->failureReasons[$r->failed] = 'Parse error '.$ev['vevent']->VEVENT[0]->UID. ': '. $ev['error']->getMessage();
					$r->failed++;
					continue;
				}
				$ev->calendarId = $params['calendarId'];
				if(!empty($params['ignoreUid'])) {
					$ev->uid = UUID::v4();
				} else if(model\CalendarEvent::find()->selectSingleValue('id')->where(['uid'=>$ev->uid])->single() !== null) {
					$r->skipped++;
					continue;
					// check if exists
				}
				if($ev->save()){ // will fail if UID exists. We dont want to modify existing events like this
					$r->saved++;
				} else {
					$r->failureReasons[$r->failed] = 'Validate error '.$ev->uid. ': '. var_export($ev->getValidationErrors(),true);
					$r->failed++;
				}
			}
		}
		return $r;
	}

	public function countMine(): int
	{

		//$defaultListId = go()->getAuthState()->getUser(['tasksSettings'])->tasksSettings->getDefaultTasklistId();

		$query = model\CalendarEvent::find(['id'])
			->selectSingleValue("IFNULL(count(*), 0)")
			->filter(["inbox" => true,]);

//		$query->removeJoin("tasks_task_user");
//		$query->removeJoin("pr2_hours");
		//$query->groupBy([]);

		return $query->single();
	}

//	public function processInvite($params) {
//		$account = \GO\Email\Model\Account::model()->findByPk($params['accountId']);
//		$message = \GO\Email\Model\ImapMessage::model()->findByUid($account, $params['mailbox'],$params['uid']);
//		$vcalendar = $message->getInvitationVcalendar();
//
//		$from = $message->from->getAddress();
//
//		$event = model\Scheduler::processMessage($vcalendar, $params['scheduleId'], (object)[
//			'email' => $from['email'],
//			'name' => $from['personal'],
//		]);
//
//		return ['success'=>$event->save(), 'validation'=>$event->getValidationErrors()];
//	}
}
