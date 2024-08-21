<?php
/*
 * @copyright (c) 2016, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
namespace go\modules\community\calendar\model;

use go\core\orm\Mapping;
use go\core\orm\UserProperty;

/**
 * An alarms will ring a bell on a set datum / time
 * The time depends on the object it is attached to
 *
 * @property string $individualEmail emai lof the attendee that the alarm is for
 */
class Alert extends UserProperty {

	/* relatedTo */
	const Start = 'start';
	const End = 'end';

	/* actions */
	const Email = 'email';
	const Display = 'display';

	/** @var int auto increment primary key */
	public $id;

	/** @var string ISO 8061 signed duration */
	protected $offset;

	/** @var \DateTime Time to trigger the alarm. */
	protected $when;

	/** @var string 'start' | 'end' of the startdate of the event */
	protected $relatedTo = self::Start;

	/** @var \DateTime when to user has dismissed the alert or when the server has carried out sending the email */
	public $acknowledged;

	/** @var string 'email' | 'display'  */
	public $action;

	/** @var int PK of the event this alarm is set on */
	protected $fk;

	/** @var int */
	protected $userId;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable("calendar_event_alert", "alert");
	}

	public function getTrigger() {
		return isset($this->offset) ? [
			'offset' => $this->offset,
			'relatedTo' => $this->relatedTo
		] : [
			'when' => $this->when
		];
	}

	public function setTrigger($v) {
		if(isset($v['offset'])) {
			$this->offset = $v['offset'];
			$this->relatedTo = isset($v['relatedTo']) ? $v['relatedTo'] : self::Start;
		} elseif(isset($v['when'])) {
			$this->when = $v['when'];
		}
	}

	public function schedule($item) {

		$alert = new \go\core\model\Alert();
		$alert->setEntity($item);
		$alert->userId = go()->getUserId();
		$alert->tag = $this->id;

		$this->applyTime($alert, $item);

		if (!$alert->save()) {
			throw new \Exception(var_export($alert->getValidationErrors(), true));
		}
	}

	/**
	 * @param \go\core\model\Alert $coreAlert
	 * @param CalendarEvent $event
	 * @throws \Exception
	 */
	private function applyTime($coreAlert, $event)
	{
		if (isset($this->offset)) {
			$offset = $this->offset;
			if($event->isRecurring()) {
				list($recurrenceId, $next) = $event->upcomingOccurrence();
				$coreAlert->recurrenceId = $recurrenceId->format('Y-m-d\TH:i:s');
				$date = clone $next;
				$next->add(new \DateInterval($event->duration));
				if($this->relatedTo === self::End){
					$date = $next;
				} else {
					$coreAlert->staleAt = (clone $next)->setTimezone(new \DateTimeZone("UTC"));
				}
			} else {
				$date = clone ($this->relatedTo === self::End ? $event->end() : $event->start());
				if($this->relatedTo === self::Start) // don't stale if event is set to trigger after the event
					$coreAlert->staleAt = (clone $event->end())->setTimezone(new \DateTimeZone("UTC"));
			}
			$date->setTimezone(new \DateTimeZone("UTC"));
			if ($offset[0] == '-') {
				$date->sub(new \DateInterval(substr($offset, 1)));
				$coreAlert->triggerAt = $date;
				return;
			}
			if ($offset[0] == '+') {
				$offset = substr($offset, 1);
			}
			$date->add(new \DateInterval($offset));
			$coreAlert->triggerAt =  $date;
		} else if (isset($this->when)) {
			$coreAlert->triggerAt = $this->when;
		}
	}

}
