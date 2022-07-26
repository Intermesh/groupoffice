<?php
/*
 * @copyright (c) 2016, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

namespace GO\Modules\GroupOffice\Calendar\Model;

use GO\Core\Orm\Record;
use IFW\Util\DateTime;

/**
 * A calendar resource. Like a beamer of a room.
 * Add this to the attendees to keep availability schedual
 *
 * @property int $eventId FK to event
 * @property int $recurrenceId PK and FK to recurrence
 * @property int $patchEventId FK to replacement event
 * @property Event $patch The patch for this instance
 * @property Event $serie The serie this is an instance of
 */
class EventInstance extends Record {

	/**
	 * FK to event this is an exception for
	 * @var int
	 */
	public $eventId;

	/**
	 * The Date(time) that the original event would have occurred
	 * If the Recurrence-ID is on an instance. This is an exception (EXDATE)
	 * If not. This is an extra occurrence (RDATE)
	 * @var DateTime
	 */
	public $recurrenceId;

	/**
	 * If empty the exception is remove or the occurrence is the same as the origional
	 * @var type
	 */
	public $patchEventId;

	public static function tableName() {
		return 'calendar_event_instance';
	}

	public static function getPrimaryKey() {
		return ['eventId', 'recurrenceId'];
	}

	protected static function defineRelations() {
		self::hasOne('serie', Event::class, ['eventId' => 'id']);
		self::hasOne('patch', Event::class, ['patchEventId' => 'id']);
	}

	public function applyException() {
		if(!empty($this->event)) {
			$this->event->markDeleted = true;
		}
	}

	public function isPatched() {
		return !empty($this->patchEventId);
	}

	public function getStartAt() {
		if(!empty($this->patch)) {
			return $this->patch->startAt;
		}
		return $this->recurrenceId;
	}
	public function getEndAt() {
		if(!empty($this->patch)) {
			return $this->patch->endAt;
		}
		$start = clone $this->getStartAt();
		return $start->add($this->getDuration());
	}

	/**
	 * Duration for 4 override cases
	 * @return \DateInterval
	 */
	public function getDuration() {
		if(empty($this->patch)) {
			return $this->serie->getDuration();
		}
		if($this->patch->getStartAt() === null && $this->patch->getEndAt() === null) {
			return $this->serie->getDuration();
		}
		if($this->patch->getStartAt() !== null && $this->patch->getEndAt() !== null) {
			$test = $this->patch->getStartAt()->diff($this->patch->getEndAt());
			return $test;
		}
		if($this->patch->getStartAt() !== null) { // patched start time
			$end = $this->patch->getStartAt().add($this->serie->getDuration());
			return $this->patch->getStartAt()->diff($end); // leading duration
		}
		if($this->patch->getEndAt() !== null) {
			$start = $this->recurrenceId;
			return $start->diff($this->patch->getEndAt());
		}
	}

	static private function allowedAttributes() {
		// @todo: allow attendees overrride
		return ['sequence', 'startAt', 'endAt', 'title', 'description', 'location',
			'status', 'busy', 'attendees', 'attachments'];
	}

	/**
	 * Add a patch to this instance
	 * Compare with series and only set properties that have changed
	 */
	public function applyPatch(Event $modfiedSerie) {
		
		$data = [];
		foreach ($modfiedSerie->getModified() as $colName) {
			if(!in_array($colName, self::allowedAttributes())) {
				continue;
			}
			$data[$colName] = $modfiedSerie->$colName;
		}
		$modfiedSerie->clearModified();
		$patch = $this->patch;
		if(empty($patch)) {
			$patch = new Event();
			$patch->startAt = null;
			$patch->endAt = null;
			$patch->allDay = null;
			$patch->status = null;
			$patch->busy = null;
			$patch->visibility = null;
		}
		$patch->setValues($data);
		// uid and recurrenceId must be the same
		$patch->recurrenceId = $this->recurrenceId;
		$patch->uid = $modfiedSerie->uid;
		$this->patch = $patch;
	}

	/**
	 * Create an Event from the series with patched applied
	 * @return Event The patched event merged with series
	 */
	public function patch() {
		$event = clone $this->serie;
		$event->startAt = clone $this->recurrenceId;
		$event->endAt = clone $this->recurrenceId;
		$event->endAt->add($this->getDuration());
		foreach($this->patch->toArray() as $col => $val) {
			if($val !== null)
				$event->$col = $val; // will set start + end again when overridden
		}
		$event->id = $this->serie->id; // always
		return $event;
	}

	public function toVEVENT() {
		$props = [
			'RECURRENCE-ID' => $this->recurrenceId,
			'UID' => $this->event->uid
		];
		empty($this->startAt) ?: $props['DTSTART'] = $this->startAt;
		empty($this->endAt) ?: $props['DTEND'] = $this->endAt;
		empty($this->title) ?: $props['TITLE'] = $this->title;
		empty($this->description) ?: $props['SUMMARY'] = $this->description;
		empty($this->location) ?: $props['LOCATION'] = $this->location;
		empty($this->status) ?: $props['STATUS'] = EventStatus::$text[$this->status];
		empty($this->classification) ?: $props['CLASS'] = Visibility::$text[$this->classification];
	}

	protected function internalDelete($hard) {

		if($this->patch) {
			$this->patch->delete();
		}
		return parent::internalDelete($hard);
	}

}
