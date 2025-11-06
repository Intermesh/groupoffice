<?php

namespace go\modules\community\calendar\model;

use DateInterval;
use go\core\data\Model;
use go\core\orm\Mapping;
use go\core\orm\Property;
use DateTime;
use stdClass;

/**
 * @property bool $excluded
 * @property CalendarEvent $owner
 */
class RecurrenceOverride extends Property
{
	const OverridableScalar = ['duration', 'title', 'location', 'status', 'description', 'priority', 'excluded'];
	const OverridableNonScalar = ['start', 'participants'];

	const Ignored = ['method', 'privacy', 'prodId', 'recurrenceId', 'recurrenceOverrides', 'recurrenceRule',
		'relatedTo', 'replyTo', 'sentBy', 'timeZone', 'uid'];

	protected ?int $fk; // to event

	protected ?DateTime $recurrenceId; // datetime of occurrence and key of map


	protected mixed $patch; // json encoded PatchObject for the original event

	private ?stdClass $props; // decoded patch object


	public function init()
	{
		$this->props = json_decode(trim($this->patch ?? '{}', "'"));
	}

	public function changeRecurrenceId(DateTime $recurrenceId) {
		$this->recurrenceId = $recurrenceId;
	}

	/**
	 * Return the start of this occurrence
	 *
	 * @param string $recurrenceId As the recurrenceId unknown when this instance is new, it has to be provided
	 * @return DateTime
	 * @throws \DateMalformedStringException
	 */
	public function start(string $recurrenceId): DateTime
	{
		$tz = $this->owner->timeZone();
		$dateStr = $this->props->start ?? $recurrenceId;
		return new DateTime($dateStr, $tz);
	}

	/**
	 * Return the end of this occurrence
	 *
	 * @param string $recurrenceId As the recurrenceId unknown when this instance is new, it has to be provided
	 *
	 * @throws \DateMalformedStringException
	 * @throws \DateMalformedIntervalStringException
	 */
	public function end(string $recurrenceId): DateTime
	{
		return $this->start($recurrenceId)->add(new DateInterval($this->props->duration ?? $this->owner->duration));
	}

	/**
	 * Adds (does not reset like setValues()) values for an occurrence and computes the difference with the series and saves them
	 * to this object as a patch
	 *
	 * @param stdClass $props
	 * @return void
	 */
	public function patchProps(stdClass $props): void
	{
		$event = $this->owner;
		$patch = [];
		if (isset($props->start)) {
			if (!is_string($props->start)) {
				$props->start = $props->start->format('Y-m-d\TH:i:s');
			}
			if ($event->start->format('Y-m-d\TH:i:s') !== $props->start) {
				$patch['start'] = $props->start;
			}
		}
		if (isset($props->participants)) {
			//$patch['participants'] = $props->participants;
			foreach ($props->participants as $pid => $participant) {
				if (isset($event->participants[$pid])) {
					$orig = $event->participants[$pid];
					foreach ($participant as $property => $value) {
						if (isset($orig->$property) && $value !== $orig->$property) { // roles??
							$patch['participants/' . $pid . '/' . $property] = $value;
						}
					}
				} else {
					$patch['participants/' . $pid] = $participant;
				}

			}
		}
		foreach (self::OverridableScalar as $property) {
			if (isset($props->$property) && $event->$property !== $props->$property) {
				$patch[$property] = $props->$property;
			}
		}
		$this->props = new stdClass;
		$this->setValues($patch);
	}


	/**
	 * Override to make sure that recurrenceId becomes a DateTime
	 * even when its protected
	 */
	protected function normalizeValue(string $propName, $value): mixed {
		if($propName === 'recurrenceId')
			return static::getMapping()->getColumn($propName)->normalizeInput($value);
		return $value;
	}



	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('calendar_recurrence_override', "ro");
	}

	public function setValues(array $values, bool $checkAPIRights = true): Model
	{
		// We get into this function if the clients sends the patch object in full. ie. "recurrenceOverrides/20240901T090000/": {...}
		// we need to reset the props otherwise there's no way of removing props from this patch object.
		// see also https://jmap.io/spec-calendars.html#calendareventset 5.8.1 Patching
		$this->props = new stdClass;
		return parent::setValues($values, $checkAPIRights);
	}

	public function setValue(string $propName, $value, bool $checkAPIRights = true): Model
	{

		// todo: merge $name into patch object here?
		$parts = explode( '/', $propName);
		$target = &$this->props;
		while(!empty($parts)) {
			if(isset($target->{$parts[0]}) && is_object($target->{$parts[0]})) {
				$target = &$target->{$parts[0]};
				array_shift($parts);
			} else {
				$target->{implode('/',$parts)} = $value;
				break;
			}
		}
		$this->patch = json_encode($this->props);
//		$this->isModified = true;
		return $this;
	}

	public function __set($name, $value) {
		$this->setValue($name,$value);
	}

	public function __isset($name)
	{
		return isset($this->props->$name);
	}

	public function &__get($name) {
		return $this->props->$name;
	}

	public function toArray(array|null $properties = null): array|null
	{
		if(empty($this->props) || !isset($this->patch)) return null;
		return json_decode($this->patch,true);
	}

	protected function internalSave(): bool
	{
		// TODO: validate what the current user can override
		if($this->isModified('patch')) {
			// sanitize
			foreach (self::Ignored as $prop) {
				unset($this->props->$prop);
			}
			$this->patch = json_encode($this->props);
		}
		return parent::internalSave();
	}

}