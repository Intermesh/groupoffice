<?php

namespace GO\Calendar\Model;

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 * @property int $group_id
 * @property int $user_id
 * @property int $acl_id
 * @property StringHelper $name
 * @property int $start_hour
 * @property int $end_hour
 * @property StringHelper $background
 * @property int $time_interval
 * @property boolean $public
 * @property boolean $shared_acl
 * @property boolean $show_bdays
 * @property boolean $show_completed_tasks
 * @property StringHelper $comment
 * @property int $project_id
 * @property int $tasklist_id
 * @property int $files_folder_id
 * @property boolean $show_holidays
 * @property boolean $enable_ics_import
 * @property StringHelper $ics_import_url
 * @property int $version the amount of updates the calendar has received (will be used a sync token)
 */
class Calendar extends \GO\Base\Model\AbstractUserDefaultModel {
	
	use \go\core\orm\CustomFieldsTrait;
	
	/**
	 * The default color to display this calendar in the view
	 * 
	 * @var StringHelper 
	 */
	public $displayColor = false;
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Calendar 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function aclField() {
		return 'acl_id';
	}

	public function tableName() {
		return 'cal_calendars';
	}
	
	public function hasFiles(){
		return true;
	}
	
	public function customfieldsModel() {
		return "GO\Calendar\Customfields\Model\Calendar";
	}
	
	static public function versionUp($id) {
		return \GO::$db->exec('UPDATE cal_calendars SET version = version + 1 WHERE id = '.(int)$id);
	}

	public function relations() {
		return array(
			'group' => array('type' => self::BELONGS_TO, 'model' => 'GO\Calendar\Model\Group', 'field' => 'group_id'),
			'events' => array('type' => self::HAS_MANY, 'model' => 'GO\Calendar\Model\Event', 'field' => 'calendar_id', 'delete' => true),
			'categories' => array('type' => self::HAS_MANY, 'model' => 'GO\Calendar\Model\Category', 'field' => 'calendar_id', 'delete' => true),
			'tasklist' => array('type' => self::BELONGS_TO, 'model' => 'GO\Tasks\Model\Tasklist', 'field' => 'tasklist_id'),
			'visible_tasklists' => array('type' => self::MANY_MANY, 'model' => 'GO\Tasks\Model\Tasklist', 'linkModel'=>'GO\Calendar\Model\CalendarTasklist', 'field'=>'calendar_id', 'linksTable' => 'cal_visible_tasklists', 'remoteField'=>'tasklist'),
			);
	}
	
	public function findDefault($userId){
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->single()
						->join("cal_settings", \GO\Base\Db\FindCriteria::newInstance()
										->addCondition('id', 's.calendar_id','=','t',true,true)
										->addCondition('user_id', $userId,'=','s'),
										's');
		
		return $this->find($findParams);
	}
	
	
	public function settingsModelName() {
		return "GO\Calendar\Model\Settings";
	}
	
	public function settingsPkAttribute() {
		return 'calendar_id';
	}	
	
	/**
	 * Get the color of this calendar from the Calendar_user_Color table.
	 * 
	 * @param int $userId
	 * @return StringHelper The color or false if no color is found 
	 */
	public function getColor($userId){
		$userColor = CalendarUserColor::model()->findByPk(array('calendar_id'=>$this->id,'user_id'=>$userId));

		if($userColor)
			return $userColor->color;
		else
			return false;
	}
	
	/**
	 * Get's a unique URI for the calendar. This is used by CalDAV
	 * 
	 * @return StringHelper
	 */
	public function getUri(){
		return preg_replace('/[^\w-]*/', '', (strtolower(str_replace(' ', '-', $this->name)))).'-'.$this->id;
	}

	/**
	 * Check if the current user may create events in this calendar. Here we deviate
	 * from the standard if the "freebusypermissions" module is installed. When a 
	 * user has access to the freebusy info he may also schedule a meeting in the user's calendar. 
	 * 
	 * @return boolean
	 */
	public function userHasCreatePermission(){
//		if(\GO\Base\Model\Acl::hasPermission($this->getPermissionLevel(),\GO\Base\Model\Acl::CREATE_PERMISSION)){
//			return true;
//		}else 
		if(\GO::modules()->isInstalled('freebusypermissions')){
			return \GO\Freebusypermissions\FreebusypermissionsModule::hasFreebusyAccess(\GO::user()->id, $this->user_id);
		}  else {
			return true;
		}
	}
	
	
// removed for ticket #201919367
// 	protected function beforeDelete() {
// 		$findParams = \GO\Base\Db\FindParams::newInstance()
// 			->select('t.id, s.user_id')
// 			->join("cal_settings", \GO\Base\Db\FindCriteria::newInstance()
// 				->addCondition('id', 's.calendar_id','=','t',true,true)
// 				,'s', 'LEFT')
// 				->group('s.user_id');
// 		$findParams->getCriteria()
// 				->addCondition('id', $this->id)
// 				->addCondition('user_id', null,'IS NOT','s');
		
// 		$defaultUserNames = array();
// 		$defaultUsers = $this->find($findParams);
// 		foreach($defaultUsers as $default) {
// 			if(!empty($default->user)) {
// 				$defaultUserNames[] = $default->user->username;
// 			}
// 		}
// 		if(!empty($defaultUserNames)) {
// 			// This is someones default calendar
// 			throw new \Exception(strtr(\GO::t("Not deleted!
// This is the default calendar of user :username", "calendar"), array(':username'=>"<br> - ".implode('<br> - ',$defaultUserNames))));
// 		}
// 		return parent::beforeDelete();
// 	}
	
	protected function afterSave($wasNew) {
		
		$file = new \GO\Base\Fs\File($this->getPublicIcsPath());
		
		if(!$this->public){
			if($file->exists())
				$file->delete ();
		} else {
			if(!$file->exists())
				$file->touch(true);
			
			$file->putContents($this->toVObject());
		}
		
		return parent::afterSave($wasNew);
	}

	/**
	 * Remove all events
	 */
	public function truncate(){
		$events = $this->events;
		
		foreach($events as $event){
			$event->delete();
		}
	}
	
	
	/**
	 * 
	 * @param \GO\Base\Model\User $user
	 * @return \GO\Tasks\Model\Tasklist
	 */
	public function getDefault(\GO\Base\Model\User $user, &$createdNew=false) {
		$default = parent::getDefault($user, $createdNew);
	
		if($createdNew){
			$pt = new PortletCalendar();
			$pt->user_id=$user->id;
			$pt->calendar_id=$default->id;
			$pt->save();
		}
	
		return $default;
	}

	public function getFreeBusyInfo($startTimeUnix,$currentModelId=0) {
		
		$free_busy = array();
		for ($i = 0; $i < 1440; $i+=15) {
			$free_busy[$i] = 0;
		}
		
		
		foreach ($free_busy as $min=>$busy) {
			
			$model = Event::model()->find(
				\GO\Base\Db\FindParams::newInstance()
					->single()
					->ignoreAcl()
					->criteria(\GO\Base\Db\FindCriteria::newInstance()
						->addCondition('calendar_id', $this->id, '=')
						->addCondition('start_time',$startTimeUnix+$min*60+15*60,'<')
						->addCondition('end_time',$startTimeUnix+$min*60,'>')
					)
			);
			
			$free_busy[$min] = !empty($model) && $model->id!=$currentModelId ? 1 : 0;
			
		}
		
		return $free_busy;
		
	}

	
	/**
	 * Get the Vobject of this calendar
	 * 
	 * @return StringHelper
	 */
	public function toVObject(){

		//$stmt = $this->events(\GO\Base\Db\FindParams::newInstance()->select("t.*"));
		$findParams = \GO\Base\Db\FindParams::newInstance()->select("t.*");
		$findParams->getCriteria()->addCondition("calendar_id", $this->id);
	
		$stmt = Event::model()->findForPeriod($findParams, \GO\Base\Util\Date::date_add(time(), 0, -1));
		
		$string = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Intermesh//NONSGML ".\GO::config()->product_name." ".\GO::config()->version."//EN\r\n";

			$t = new \GO\Base\VObject\VTimezone();
			$string .= $t->serialize();

			while($event = $stmt->fetch()){
				$v = $event->toVObject();
				$string .= $v->serialize();
			}

			$string .= "END:VCALENDAR\r\n";
			
			return $string;
	}
	
	public function getEventsForPeriod($start, $end, $categories = array()) {
		$criteria = \GO\Base\Db\FindCriteria::newInstance()->addCondition('calendar_id', $this->id);
		if(!empty($categories))
			$criteria->addInCondition('category_id', $categories);
		$params = \GO\Calendar\Model\Event::model()->findCalculatedForPeriod(
			\GO\Base\Db\FindParams::newInstance()->criteria($criteria)->select(),
			$start, 
			$end
		);
		
		return $params;
	}
	
	/**
	 * Get the url to the published ICS file.
	 * @return StringHelper
	 */
	public function getPublicIcsUrl(){
		return \GO::config()->full_url.'public/calendar/'.$this->id.'/calendar.ics';
	}
	
	/**
	 * Get the url to the published ICS file.
	 * @return StringHelper
	 */
	public function getPublicIcsPath(){
		return \GO::config()->file_storage_path.'public/calendar/'.$this->id.'/calendar.ics';
	}
}
