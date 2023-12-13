<?php

namespace go\modules\community\calendar\controller;

use go\core\jmap\EntityController;
use go\modules\community\calendar\model;


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

	public function processInvite($params) {
		$account = \GO\Email\Model\Account::model()->findByPk($params['accountId']);
		$message = \GO\Email\Model\ImapMessage::model()->findByUid($account, $params['mailbox'],$params['uid']);
		$vcalendar = $message->getInvitationVcalendar();

		$from = $message->from->getAddress();

		$event = model\Scheduler::processMessage($vcalendar, $params['scheduleId'], (object)[
			'email' => $from['email'],
			'name' => $from['personal'],
		]);

		return ['success'=>$event->save(), 'validation'=>$event->getValidationErrors()];
	}
}
