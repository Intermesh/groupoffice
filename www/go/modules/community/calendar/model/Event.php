<?php
/*
 * @copyright (c) 2016, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
namespace go\modules\community\calendar\model;

use go\core\jmap\Entity;

/**
 * This holds information about a single event. The event can occur one-time or
 * can be a recurring event.
 *
 * @property-read string $uri a URI the event can be seen.
 * @property-read bool $isRecurring does this event occurs more then ones
 * @property-read bool $isException is this instance an exception of a recurring event
 * 
 * @property RecurrenceRule $recurrenceRule the rule that describe when an how this event is recurring
 * @property Attendee $organizer the attendee that created the event
 * @property Attendee[] $attendees all attendees that are added including organizer
 * @property EventAttachment[] $attachments File attachments
 */
class Event extends Entity {



	public static function tableName() {
		return 'calendar_event';
	}

	// ATTRIBUTES

	public function getStartAt() {
		if(is_string($this->startAt)) {
			$this->startAt = new DateTime($this->startAt);
		}
		return empty($this->startAt) ? $this->recurrenceId : $this->startAt;
	}

	public function getEndAt() {
		if(!empty($this->endAt)) {
			if(is_string($this->endAt)) {
				$this->endAt = new DateTime($this->endAt);
			}
			return $this->endAt;
		}
		$endAt = clone $this->getStartAt();
		$endAt->add($this->duration);
		return $endAt;
	}

	public function setStartAt($val) {
		$this->startAt = $val;
	}

	/**
	 * Set the date of the startAt attribute
	 * @param string $date in Y-m-d format
	 */
	public function setStartDate($date) {
		$arr = explode('-', $date);
		$this->startAt->setDate($arr[0], $arr[1], $arr[2]);
	}

	public function getIsException() {
		return $this->recurrenceId !== null;
	}

	// OVERRIDES

	protected function internalValidate() {

		$success = parent::internalValidate();

		if($this->isModified('allDay') && $this->getIsInstance()) {
			$this->setValidationError('allDay', \IFW\Validate\ErrorCode::INVALID_INPUT, 'Cannot change allDay for single instance');
		}

		if($this->startAt > $this->endAt) {
			$this->setValidationError('startAt', \IFW\Validate\ErrorCode::MALFORMED, 'Start date is greater than end date');
			$success = false;
		}
		return $success;
	}

	protected function internalSave() : bool {
		if($this->savedBy !== null && $this->savedBy instanceof CalendarEvent) {
			foreach($this->attendees as $key => $attendee) {
				if($this->savedBy->calendarId == $attendee->calendarId){
					unset($this->attendees[$key]); // don't save self twice
				}
			}
		}
		return parent::internalSave();
	}

	// OPERATIONS

	/**
	 * Create a patch object for instance
	 */
	public function createInstance($recurrenceId) {
		if(!$this->getIsRecurring()) {
			throw new \Exception('Cannot create instance for none recurring event');
		}
		$instance = new EventInstance();
		$instance->eventId = $this->id;
		$instance->serie = $this;
		$instance->recurrenceId = $recurrenceId;
		return $instance;
	}

	/**
	 * Confirm that the event is really happening
	 */
	public function confirm() {
		$this->status = EventStatus::Confirmed;
		return $this;
	}

	/**
	 * Changed instances of a recurring event
	 * @param DateTime $start
	 * @param DateTime $end
	 * @return EventInstance store
	 */
	public function overrides(DateTime $start, DateTime $end) {
		return EventInstance::find((new Query)
				//->joinRelation('event', ['sequence', 'startAt', 'endAt', 'createdAt', 'modifiedAt', 'description', 'location', 'status'])
				->where('eventId = :id AND recurrenceId > :start AND recurrenceId < :end')
				->bind([':id'=>$this->id, ':start'=>$start->format('Y-m-d'), ':end'=>$end->format('Y-m-d')])
		);
	}

	/**
	 * Make a copy of the event for exceptions or a new series
	 * The organizer is the only one cloning and his attendense is therefor skipped
	 * @return \self
	 */
	public function cloneMe() {
		$event = new self();
		$properties = $this->toArray();
		unset($properties['recurrenceId']);
		unset($properties['id']);
		$event->setValues($properties);
		$event->parent = $this->parent;
		foreach($this->attendees as $attendee) {
			$new = new Attendee();
			$new->setValues($attendee->toArray());
			$event->attendees[] = $new;
		}
		foreach($this->attachments as $attachment) {
			$new = new EventAttachment();
			$new->setValues($attachment->toArray());
			$event->attachments[] = $new;
		}
		
		return $event;
	}

	/**
	 * Cancel the event
	 * @return \GO\Modules\GroupOffice\Calendar\Model\Event
	 */
	public function cancel() {
		$this->status = EventStatus::Cancelled;
		return $this;
	}
	
	public function inTimeRange($from, $till) {
		return ($this->startAt < $till && $this->endAt > $from);
	}
}
