<?php

namespace go\modules\community\calendar\model;

use go\core\orm\Mapping;
use go\core\orm\Property;

class DefaultAlert extends Property {

	/* relatedTo */
	const Start = 'start';
	const End = 'end';

	/* actions */
	const Email = 'email';
	const Display = 'display';

	// Omit for the default alerts (with or without time)
	protected $calendarId;

	/** @var int auto increment primary key */
	public $id;

	/** @var string ISO 8061 signed duration */
	protected $offset;

	/** @var \DateTime Time to trigger the alarm. */
	protected $when;

	/** @var string 'start' | 'end' of the startdate of the event */
	protected $relatedTo = self::Start;

	/** @var DateTime when to user has dismissed the alert or when the server has carried out sending the email */
	public $acknowledged;

	/** @var string 'email' | 'display'  */
	public $action;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('calendar_default_alert', "alert");
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
		if($v['offset']) {
			$this->offset = $v['offset'];
			$this->relatedTo = isset($v['relatedTo']) ? $v['relatedTo'] : self::Start;
		} elseif($v['when']) {
			$this->when = $v['when'];
		}
	}
}