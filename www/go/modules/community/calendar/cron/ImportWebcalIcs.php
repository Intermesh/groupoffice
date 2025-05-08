<?php

namespace go\modules\community\calendar\cron;

use go\core\model\CronJob;
use go\core\model\CronJobSchedule;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\CalendarEvent;

class ImportWebcalIcs extends CronJob {
	/**
	 * Return true or false to enable the selection for users and groups for
	 * this cronjob.
	 *
	 * CAUTION: This will give the run() function a different behaviour.
	 *					Please see the documentation of the run() function
	 *					to see what is different.
	 */
	public function enableUserAndGroupSupport(){
		return false;
	}

	/**
	 * Get the unique name of the Cronjob
	 *
	 * @return String
	 */
	public function getLabel(){
		return go()->t("Import calendar from ICS file.", 'calendar', 'community');
	}

	/**
	 * Get the unique name of the Cronjob
	 *
	 * @return String
	 */
	public function getDescription(){
		return go()->t("Make exact copy of external ICS file calendar to the selected Group-Office calendars. Select the calendars to import to, in the calendar's administration settings.", 'calendar', 'community');
	}

	/**
	 * The code that needs to be called when the cron is running
	 *
	 * If $this->enableUserAndGroupSupport() returns TRUE then the run function
	 * will be called for each $user. (The $user parameter will be given)
	 *
	 * If $this->enableUserAndGroupSupport() returns FALSE then the
	 * $user parameter is null and the run function will be called only once.
	 *
	 * @param CronJobSchedule $cronJob
	 */
	public function run(CronJobSchedule $schedule) {

		$calendars = Calendar::find()->where('webcalUri','IS NOT', null);


		foreach ($calendars as $calendar) {

			go()->log('Checking calendar "'.$calendar->name.'" for ICS import...');

			//$calendar->truncate();

			$calendar->importWebcal();



		}

	}

}
