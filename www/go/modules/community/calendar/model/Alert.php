<?php
/*
 * @copyright (c) 2016, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
namespace go\modules\community\calendar\model;

use DateTimeInterface;
use go\core\ErrorHandler;
use go\core\orm\exception\SaveException;
use go\core\orm\Mapping;
use go\core\orm\UserProperty;
use go\core\util\DateTime;

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

	/** @var ?int auto increment primary key */
	public ?int $id;

	/** @var ?string ISO 8061 signed duration */
	protected ?string $offset;

	/** Time to trigger the alarm. */
	protected DateTimeInterface|null $when;

	/** @var string 'start' | 'end' of the startdate of the event */
	protected string $relatedTo = self::Start;

	/** when to user has dismissed the alert or when the server has carried out sending the email */
	public DateTimeInterface|null $acknowledged;

	/** @var string 'email' | 'display'  */
	public string $action = 'display';

	/** @var int PK of the event this alarm is set on */
	protected int $fk;

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

	public function schedule(CalendarEvent $item) : ?\go\core\model\Alert {

		$alert = $this->buildCoreAlert($item);
		if(!$alert) {
			return null;
		}

		if (!$alert->save()) {
			throw new SaveException($alert);
		}

		return $alert;
	}

	/**
	 * Generate a Group-Office alert based on the calendar alert
	 *
	 * @param CalendarEvent|null $event
	 * @return \go\core\model\Alert|null
	 * @throws \DateMalformedIntervalStringException
	 */
	public function buildCoreAlert(?CalendarEvent $event = null) : ?\go\core\model\Alert
	{
		if(!isset($event)) {
			$event = $this->owner;
		}
		$coreAlert = new \go\core\model\Alert();
		$coreAlert->setEntity($event);
		$coreAlert->userId = go()->getUserId();
		$coreAlert->tag = $this->id;

		if (isset($this->offset)) {
			$offset = $this->offset;
			if($event->isRecurring()) {
				list($recurrenceId, $next) = $event->upcomingOccurrence();
				if(!isset($recurrenceId) || !isset($next)) {
					return null;
				}
				$coreAlert->recurrenceId = $recurrenceId->format('Y-m-d\TH:i:s');
				$date = clone $next;
				$next->add(new \DateInterval($event->duration));
				if ($this->relatedTo === self::End) {
					$date = $next;
				} else {
					$coreAlert->staleAt = (clone $next)->setTimezone(new \DateTimeZone("UTC"));
				}

			} else {
				$date = clone ($this->relatedTo === self::End ? $event->end() : $event->start());
				if($this->relatedTo === self::Start) // don't stale if event is set to trigger after the event
					$coreAlert->staleAt = (clone $event->end())->setTimezone(new \DateTimeZone("UTC"));
			}

			if($event->showWithoutTime) {
				// for now we hard code events at 9:00 in the morning
				$date->add(new \DateInterval("PT9H"));
			}

			$date->setTimezone(new \DateTimeZone("UTC"));

			try {
				if ($offset[0] == '-') {
					$date->sub(new \DateInterval(substr($offset, 1)));
				} else {
					if ($offset[0] == '+') {
						$offset = substr($offset, 1);
					}
					$date->add(new \DateInterval($offset));
				}
			} catch(\Exception $e) {
				ErrorHandler::logException($e, "Invalid alert offset " . $offset);
			}
			$coreAlert->triggerAt =  $date;
		} else if (isset($this->when)) {
			$coreAlert->triggerAt = $this->when;
		}

		// don't create alerts in the past
		if($coreAlert->triggerAt < new DateTime()) {
			return null;
		}

		return $coreAlert;
	}

}
