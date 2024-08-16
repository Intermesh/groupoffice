<?php

namespace go\modules\community\calendar\model;

use go\core\data\Model;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\util\DateTime;

/**
 * @property bool $excluded
 */
class RecurrenceOverride extends Property
{
	const OverridableScalar = ['duration','title', 'location', 'status', 'description', 'priority'];
	const OverridableNonScalar = ['start', 'participants'];

	const Ignored = ['method', 'privacy', 'prodId', 'recurrenceId', 'recurrenceOverrides', 'recurrenceRule',
		'relatedTo','replyTo','sentBy', 'timeZone', 'uid'];

	protected $fk; // to event
	protected $recurrenceId; // datetime of occurrence and key of map

	protected $_start; // indexed in db for finding the first occurrence start
	protected $_end; // indexed in db for finding the last occurrence end

	protected $patch; // json encoded PatchObject for the original event

	private $props; // decoded patch object


	public function init(){
		$this->props = json_decode(trim($this->patch??'{}',"'"));
	}

	public function recurrenceId() {
		return $this->recurrenceId;
	}

	public function start() {
		return isset($this->props->start) ? new \DateTime($this->props->start) : clone $this->recurrenceId;
	}

	public function end() {
		return $this->start()->add(new \DateInterval($this->props->duration ?? $this->owner->duration))->format('Y-m-d\TH:i:s');
	}

	public function patchProps($props) {
		$event = $this->owner;
		$patch = [];
		if(isset($props->start)) {
			if(!is_string($props->start)) {
				$props->start = $props->start->format('Y-m-d\TH:i:s');
			}
			if($event->start->format('Y-m-d\TH:i:s') !== $props->start) {
				$patch['start'] = $props->start;
			}
		}
		if(isset($props->participants)) {
			//$patch['participants'] = $props->participants;
			foreach($props->participants as $pid => $participant) {
				if(isset($event->participants[$pid])) {
					$orig = $event->participants[$pid];
					foreach($participant as $property => $value) {
						if(isset($orig->$property) && $value !== $orig->$property) { // roles??
							$patch['participants/'.$pid.'/'.$property] = $value;
						}
					}
				} else {
					$patch['participants/' . $pid] = $participant;
				}

			}
		}
		foreach(self::OverridableScalar as $property) {
			if(isset($props->$property) && $event->$property !== $props->$property) {
				$patch[$property] = $props->$property;
			}
		}
		$this->props = new \stdClass;
		$this->setValues($patch);
	}

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('calendar_recurrence_override', "ro");
	}

	public function setValue(string $name, $value): Model
	{
		$this->props->$name = $value;
		$this->patch = json_encode($this->props);
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

	public function toArray(array $properties = null): array|null
	{
		if(empty($this->props)) return null;
		return json_decode($this->patch,true);
	}

	protected function internalSave(): bool
	{
		// TODO: validate what the current user can override
		if(!empty($this->recurrenceId) && is_string($this->recurrenceId)) {
			$this->recurrenceId = new DateTime(str_replace('T',' ',$this->recurrenceId));
		}
		if($this->isNew()) {
			$this->_start = $this->recurrenceId;
		}
		if($this->isModified('patch')) {
			// sanatize
			foreach (self::Ignored as $prop) {
				unset($this->props->$prop);
			}
			if(isset($this->props->start)) {
				$this->_start = str_replace('T', ' ', $this->props->start);
			}
			$this->patch = json_encode($this->props);
		}

		return parent::internalSave();
	}

}