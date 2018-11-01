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


namespace GO\Calendar\Controller;


class PortletController extends \GO\Base\Controller\AbstractMultiSelectModelController {
	
	/**
	 * The name of the model from where the MANY_MANY relation is called
	 * @return String 
	 */
	public function modelName() {
		return 'GO\Calendar\Model\Calendar';
	}
	
	/**
	 * Returns the name of the model that handles the MANY_MANY relation.
	 * @return String 
	 */
	public function linkModelName() {
		return 'GO\Calendar\Model\PortletCalendar';
	}
	
	/**
	 * The name of the field in the linkModel where the key of the current model is defined.
	 * @return String
	 */
	public function linkModelField() {
		return 'calendar_id';
	}
	
	/**
	 * Get the data for the grid that shows all the tasks from the selected calendars.
	 * 
	 * @param Array $params
	 * @return Array The array with the data for the grid. 
	 */
	protected function actionPortletGrid($params) {
		
		$local_time = time();
		$year = date("Y", $local_time);
		$month = date("m", $local_time);
		$day = date("j", $local_time);

		$periodStartTime = mktime(0, 0, 0, $month, $day, $year);
		$periodEndTime = mktime(0, 0, 0, $month, $day+2, $year);
		
		$today_end = mktime(0, 0, 0, $month, $day+1, $year);
		
		
		$joinCriteria = \GO\Base\Db\FindCriteria::newInstance()
						->addCondition('user_id', \GO::user()->id,'=','pt')
						->addCondition('calendar_id', 'pt.calendar_id', '=', 't', true, true);
		
		$calendarJoinCriteria = \GO\Base\Db\FindCriteria::newInstance()
						->addCondition('calendar_id', 'tl.id', '=', 't', true, true);
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->select('t.*, tl.name AS calendar_name')
//						->ignoreAcl()
						->join(\GO\Calendar\Model\PortletCalendar::model()->tableName(),$joinCriteria,'pt')
						->join(\GO\Calendar\Model\Calendar::model()->tableName(), $calendarJoinCriteria,'tl');
		
			
		$events = \GO\Calendar\Model\Event::model()->findCalculatedForPeriod($findParams, $periodStartTime, $periodEndTime);

		$store = new \GO\Base\Data\ArrayStore();
		
		foreach($events as $event){
			$record = $event->getResponseData();
			$record['day']=$event->getAlternateStartTime()<$today_end ? \GO::t("Today") : \GO::t("Tomorrow");
			$record['time']=$event->getEvent()->all_day_event==1 ? '-' : $record['time'];
			$store->addRecord($record);
		}
		
		return $store->getData();
		
	}
	
}
