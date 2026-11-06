<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * The Portlet controller
 *
 * @package GO.modules.Calendar
 * @version $Id: PortletController.php 7607 2011-09-20 10:08:21Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits <wsmits@intermesh.nl>
 */


namespace GO\Summary\Controller;


use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\calendar\model\RecurrenceRule;

class CalendarController extends \GO\Base\Controller\AbstractMultiSelectModelController {

	/**
	 * The name of the model from where the MANY_MANY relation is called
	 * @return String
	 */
	public function modelName() {
		return '\GO\Summary\Model\CalendarActiveRecord';
	}

	/**
	 * Returns the name of the model that handles the MANY_MANY relation.
	 * @return String
	 */
	public function linkModelName() {
		return 'GO\Summary\Model\PortletCalendar';
	}

	/**
	 * The name of the field in the linkModel where the key of the current model is defined.
	 * @return String
	 */
	public function linkModelField() {
		return 'calendar_id';
	}

	/**
	 * The visible calendars are per user. Always use the current user, not the one from the client.
	 */
	protected function actionSelectedStore($params) {
		$params['model_id'] = \GO::user()->id;
		return parent::actionSelectedStore($params);
	}

	protected function actionSelectNewStore($params) {
		$params['model_id'] = \GO::user()->id;
		return parent::actionSelectNewStore($params);
	}

	public function actionUpdateRecord($params) {
		$params['model_id'] = \GO::user()->id;
		return parent::actionUpdateRecord($params);
	}

	/**
	 * Only calendars the user may read can be added
	 *
	 * @throws \GO\Base\Exception\AccessDenied
	 */
	protected function beforeAdd(array $params) {
		$ids = json_decode($params['add'] ?? '[]', true);
		foreach((array) $ids as $id) {
			// findByPk checks read permission
			if(!\GO\Summary\Model\CalendarActiveRecord::model()->findByPk($id)) {
				throw new \GO\Base\Exception\NotFound();
			}
		}
		return parent::beforeAdd($params);
	}

	/**
	 * Get the data for the grid that shows all the tasks from the selected calendars.
	 *
	 * @param Array $params
	 * @return Array The array with the data for the grid.
	 */
	protected function actionPortletGrid($params) {

//		$local_time = time();
//		$year = date("Y", $local_time);
//		$month = date("m", $local_time);
//		$day = date("j", $local_time);

//		$periodStartTime = mktime(0, 0, 0, $month, $day, $year);
//		$periodEndTime = mktime(0, 0, 0, $month, $day+2, $year);
//		$today_end = mktime(0, 0, 0, $month, $day+1, $year);
		$start = new \go\core\util\DateTime(date('Y-m-d'));
		$end = (new \go\core\util\DateTime(date('Y-m-d')));
		$end->modify('+2 days');
		$todayend = (new \go\core\util\DateTime(date('Y-m-d')));
		$todayend->setTime(23,59,59);

		$activeCalendars = [];
		$activeCalendarStmt = go()->getDbConnection()->select('calendar_calendar.id,calendar_calendar.name')->from('calendar_calendar', 'calendar_calendar')->join('su_visible_calendars', 'pt', 'pt.calendar_id = calendar_calendar.id AND pt.user_id = '. \GO::user()->id);
		// Calendars the user may no longer read are skipped
		\go\core\model\Acl::applyToQuery($activeCalendarStmt, 'calendar_calendar.aclId');
		foreach($activeCalendarStmt as $calendar) {
			$activeCalendars[$calendar['id']] = $calendar['name'];
		}

		$events = CalendarEvent::find()
			->join('su_visible_calendars', 'pt', 'pt.calendar_id = cce.calendarId AND pt.user_id = '. \GO::user()->id, 'LEFT')
			->filter(['hideSecret' => 1,'inCalendars'=>array_keys($activeCalendars), 'before' => $end, 'after' => $start]);


//		$events = \GO\Calendar\Model\Event::model()->findCalculatedForPeriod(FindParams::newInstance()
//			->select('t.*, tl.name AS calendar_name')
//			->join(PortletCalendar::model()->tableName(),FindCriteria::newInstance()
//				->addCondition('user_id', \GO::user()->id,'=','pt')
//				->addCondition('calendar_id', 'pt.calendar_id', '=', 't', true, true),'pt')
//			->join('calendar_calendar', FindCriteria::newInstance()
//				->addCondition('calendarId', 'tl.id', '=', 't', true, true),'tl'), $periodStartTime, $periodEndTime);

		$userTZ =  go()->getAuthState()->getUser()->timezone;
		$data = [];
		foreach($events as $event){
			if($event->isRecurring()) {
				foreach(RecurrenceRule::expand($event, $start->format('Y-m-d'), $end->format('Y-m-d')) as $instance) {
					$arr = $instance->toArray();
					$arr['calendar'] = $activeCalendars[$instance->calendarId];
					$arr['start'] = $instance->start(false, $userTZ)->format('Y-m-d H:i');
					$arr['end'] = $instance->end(false, $userTZ)->format('Y-m-d H:i');
					$arr['day'] = $instance->start(true, $userTZ) <= $todayend ? \GO::t("Today") : \GO::t("Tomorrow");
					$data[$instance->start(false, $userTZ)->format('Y-m-d\TH:i:s').'-'.$event->id] = $arr;
				}
			} else {
				$arr =$event->toArray();
				$arr['calendar'] = $activeCalendars[$event->calendarId];
				$arr['start'] = $event->start(false, $userTZ)->format('Y-m-d H:i');
				$arr['end'] = $event->end(false, $userTZ)->format('Y-m-d H:i');
				$arr['day'] = $event->start(true, $userTZ) <= $todayend ? \GO::t("Today") : \GO::t("Tomorrow");
				$data[$event->start(false, $userTZ)->format('Y-m-d\TH:i:s').'-'.$event->id] = $arr;
			}
			//$record = $event->toArray();
//			$record['day']=$event->end(true)->format('Y-m-d') > $start ? \GO::t("Today") : \GO::t("Tomorrow");
//			$record['time']=$event->showWithoutTime ? '-' : $event->start()->format('H:i');
//			$store->addRecord($record);
		}
		ksort($data);
		$store = new \GO\Base\Data\ArrayStore(false, array_values($data));
		return $store->getData();

	}

}