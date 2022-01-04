<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */
namespace GO\Calendar\Model;

use go\core\orm\Mapping;
use go\core\orm\Property;
use GO\Calendar\Controller\EventController;
/**
 * The Settings model
 *
 * @package GO.modules.Addressbook
 * @version $Id$
 * @copyright Copyright Intermesh BV.
 * @author Michael de Hart mdhart@intermesh.nl
 *
 * @property int $user_id
 */
class UserSettings extends Property {

	public $calendar_id;
	public $background;
	public $reminder;
	public $show_statuses;
	public $check_conflict;
	public $user_id;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable("cal_settings", "cals");
	}
	
	// public function getReminder() {
	// 	$response = EventController::reminderSecondsToForm($this->reminder);
		
	// 	if(!$response['data']['enable_reminder']){
	// 		$response['data']['reminder_value'] = null;
	// 	}
	// 	return $response;
	// }
	
	// public function setReminder($params) {
	// 	if(isset($params['reminder_value'])){
	// 		if($params['reminder_value'] !== ''){
	// 			$this->reminder = $params['reminder_multiplier'] * $params['reminder_value'];
	// 		} else {
	// 			$this->reminder = null;
	// 		}
	// 	}
	// }

}
