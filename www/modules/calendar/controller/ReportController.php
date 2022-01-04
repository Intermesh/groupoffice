<?php

namespace GO\Calendar\Controller;


/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: ReportController.php 20499 2016-10-06 11:38:46Z mschering $
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
class ReportController extends \GO\Base\Controller\AbstractJsonController {
	
	public function actionWeek($date, $calendars) {
		
		$date = \GO\Base\Util\Date::clear_time($date);
		$calendarIds = json_decode($calendars);
		
		$weekday =date('w',$date);
		if($weekday===0)
			$weekday=7;
		$weekday--;
		
		$start = $date-3600*24*($weekday);
		$end = $date+3600*24*(7-$weekday);
		
		$report = new \GO\Calendar\Reports\Week();
		foreach($calendarIds as $id) {
			
			$calendar = \GO\Calendar\Model\Calendar::model()->findByPk($id);
			$events = $calendar->getEventsForPeriod($start, $end);

			$report->day = $start;
			$report->setEvents($events);
			$report->render($date);
			$report->calendarName = $calendar->name;
		}
		$report->Output('week.pdf');
	}
	
	public function actionWorkWeek($date, $calendars) {
		$date = \GO\Base\Util\Date::clear_time($date);
		$calendarIds = json_decode($calendars);
		
		$weekday =date('w',$date);
		if($weekday===0)
			$weekday=7;
		$weekday--;
		
		$start = $date-3600*24*($weekday);
		$end = $date+3600*24*(5-$weekday);
		
		$report = new \GO\Calendar\Reports\WorkWeek();
		foreach($calendarIds as $id) {
			
			$calendar = \GO\Calendar\Model\Calendar::model()->findByPk($id);
			$events = $calendar->getEventsForPeriod($start, $end);

			$report->day = $start;
			$report->setEvents($events);
			$report->render($date);
			$report->calendarName = $calendar->name;
		}
		$report->Output('week.pdf');
	}
	
	public function actionMonth($date, $calendars) {
		$calendarIds = json_decode($calendars);
		$date = \GO\Base\Util\Date::clear_time($date);
		$start = strtotime(date('Y-m-01', $date));
		$end = strtotime(date('Y-m-t', $date).' 23:59:59');

		$report = new \GO\Calendar\Reports\Month();
		foreach($calendarIds as $id) {
			
			$calendar = \GO\Calendar\Model\Calendar::model()->findByPk($id);
			$events = $calendar->getEventsForPeriod($start, $end);

			$report->day = $start;
			$report->render($events);
			$report->calendarName = $calendar->name;
		}
		$report->Output('month.pdf');
	}
	
	public function actionDay($date, $calendars) {
		$calendarIds = json_decode($calendars);
		$date = \GO\Base\Util\Date::clear_time($date);
		
		$start = $date-1;
		$end = $date+24*3600;

		$report = new \GO\Calendar\Reports\Day();
		foreach($calendarIds as $id) {
			
			$calendar = \GO\Calendar\Model\Calendar::model()->findByPk($id);
			$events = $calendar->getEventsForPeriod($start, $end);

//			if(!empty($calendar->tasklist)) {
//				$tasklistId = $calendar->tasklist->id;
//				$report->tasks = \GO\Tasks\Model\Task::model()->findByDate($date,$tasklistId)->fetchAll();
//			}
			
			$report->setEvents($events);
			$report->render($date);
			$report->calendarName = $calendar->name;
		}
		$report->Output('day.pdf');
	}
	
}
