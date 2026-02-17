<?php

namespace GO\Summary\Model;

class CalendarActiveRecord extends \GO\Base\Model\AbstractUserDefaultModel {
	public function aclField() {
		return 'aclId';
	}

	public function tableName() {
		return 'calendar_calendar';
	}
}