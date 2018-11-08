<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

namespace GO\Tasks\Model;
use Sabre;

/**
 * The Task model
 *
 * @package GO.modules.Tasks
 * @version $Id: Task.php 7607 2011-09-20 10:05:23Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 *
 * @property int $id
 * @property String $uuid
 * @property int $tasklist_id
 * @property int $user_id
 * @property int $ctime
 * @property int $mtime
 * @property int $muser_id
 * @property int $start_time
 * @property int $due_time
 * @property int $completion_time
 * @property String $name
 * @property String $description
 * @property String $status
 * @property int $repeat_end_time
 * @property int $reminder
 * @property String $rrule
 * @property int $files_folder_id
 * @property int $category_id
 * @property int $priority
 * @property int $project_id
 * @property int $percentage_complete
 */
class Task extends \GO\Base\Db\ActiveRecord {
	
	const STATUS_NEEDS_ACTION = "NEEDS-ACTION";
	const STATUS_COMPLETED = "COMPLETED";
	const STATUS_ACCEPTED = "ACCEPTED";
	const STATUS_DECLINED = "DECLINED";
	const STATUS_TENTATIVE = "TENTATIVE";
	const STATUS_DELEGATED = "DELEGATED";
	const STATUS_IN_PROCESS = "IN-PROCESS";
	
	const PRIORITY_LOW = 0;
	const PRIORITY_NORMAL = 1;
	const PRIORITY_HIGH = 2;
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Task
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	protected function init() {
		$this->columns['name']['required']=true;
		$this->columns['tasklist_id']['required']=true;
		
		$this->columns['start_time']['gotype']='unixdate';
		$this->columns['due_time']['gotype']='unixdate';
		
		$this->columns['due_time']['greaterorequal']='start_time';
		
		$this->columns['completion_time']['gotype']='unixdate';
		$this->columns['repeat_end_time']['gotype']='unixdate';
		$this->columns['reminder']['gotype']='unixtimestamp';
		parent::init();
	}
	
	
	public function getUri() {
		if(isset($this->_setUri)) {
			return $this->_setUri;
		}
		
		return str_replace('/','+',$this->uuid).'-'.$this->id;
	}
	
	private $_setUri;
	
	public function setUri($uri) {
		$this->_setUri = $uri;					
	}
	
	public function getETag() {
		return '"' . date('Ymd H:i:s', $this->mtime). '-'.$this->id.'"';
	}
	
	protected function getLocalizedName() {
		return \GO::t("Task", "tasks");
	}

	public function tableName() {
		return 'ta_tasks';
	}
	
	public function aclField() {
		return 'tasklist.acl_id';
	}

	public function hasFiles(){
		return true;
	}
	
	public function hasLinks() {
		return true;
	}
	
	public function customfieldsModel(){
		return "GO\Tasks\Customfields\Model\Task";
	}
	
	public function relations() {
		return array(
				'tasklist' => array('type' => self::BELONGS_TO, 'model' => 'GO\Tasks\Model\Tasklist', 'field' => 'tasklist_id', 'delete' => false),
				'category' => array('type' => self::BELONGS_TO, 'model' => 'GO\Tasks\Model\Category', 'field' => 'category_id', 'delete' => false),
				'project2' => array('type' => self::BELONGS_TO, 'model' => 'GO\Projects2\Model\Project', 'field' => 'project_id', 'delete' => false)
				);
	}
	
	protected function getCacheAttributes() {
		return array('name'=>$this->name, 'description'=>$this->description);
	}
		
	public function beforeSave() {		
		if($this->isModified('status'))
			$this->setCompleted($this->status==Task::STATUS_COMPLETED, false);
		
		return parent::beforeSave();
	}
	
	public function afterSave($wasNew) {
		
		// task is done
		if($this->isModified('status') && $this->status == 'COMPLETED') {
			$this->deleteReminders();
		}elseif($this->isModified('reminder')) {
			$this->deleteReminders();
			if($this->reminder>0) {
				if($this->reminder>time() && $this->status!='COMPLETED')
					$this->addReminder($this->name, $this->reminder, $this->tasklist->user_id);
			}	
		}elseif($this->isModified('user_id')) {
			// other user id
			$this->deleteReminders();
			$this->addReminder($this->name, $this->reminder, $this->tasklist->user_id);
		}
			
		
		if($this->isModified('project_id') && !empty($this->project2))
			$this->link($this->project2);
		
		if($this->isModified()) {
			Tasklist::versionUp($this->tasklist_id);
		}
		
		return parent::afterSave($wasNew);
	}
	
//	public function afterLink(\GO\Base\Db\ActiveRecord $model, $isSearchCacheModel, $description = '', $this_folder_id = 0, $model_folder_id = 0, $linkBack = true) {
//		throw new \Exception();
//		$modelName = $isSearchCacheModel ? $model->model_name : $model->className;
//		$modelId = $isSearchCacheModel ? $model->model_id : $model->id;
//		echo $modelName;
//		if($modelName=="GO\Projects\Model\Project")
//		{
//			$this->project_id=$modelId;
//			$this->save();
//		}
//		
//		
//		return parent::afterLink($model, $isSearchCacheModel, $description, $this_folder_id, $model_folder_id, $linkBack);
//	}
	
	protected function afterDelete() {
		$this->deleteReminders();

		Tasklist::versionUp($this->tasklist_id);
		return parent::afterDelete();
	}
	
	
	protected function afterDbInsert() {
		if(empty($this->uuid)){
			$this->uuid = \GO\Base\Util\UUID::create('task', $this->id);
			return true;
		}else
		{
			return false;
		}
	}
	
	/**
	 * Find all tasks that you are going to work on today
	 * @param $date unix timestamp
	 * @param $tasklist_id the task list to search in
	 * @return ActiveStatement
	 */
	static public function findByDate($date, $tasklist_id=null) {
		$date = \GO\Base\Util\Date::clear_time($date);
		$criteria = \GO\Base\Db\FindCriteria::newInstance();
		if(!empty($tasklist_id))
			$criteria->addCondition('tasklist_id', $tasklist_id);
		$criteria1 = \GO\Base\Db\FindCriteria::newInstance()
				->addCondition('start_time', $date+24*3600, '<')
				->addCondition('start_time', $date, '>=');
		$criteria2 = \GO\Base\Db\FindCriteria::newInstance()
				->addCondition('due_time', $date+24*3600, '<')
				->addCondition('due_time', $date, '>=');
		$tasks = \GO\Tasks\Model\Task::model()->find(\GO\Base\Db\FindParams::newInstance()->criteria(
				$criteria->mergeWith($criteria1->mergeWith($criteria2, false), true))
		);
		return $tasks;
	}
	
	/**
	 * Set the task to completed or not completed.
	 * 
	 * @param Boolean $complete 
	 * @param Boolean $save 
	 */
	public function setCompleted($complete=true, $save=true) {
		if($complete) {
			$this->completion_time = time();
			$this->status=Task::STATUS_COMPLETED;
			$this->percentage_complete=100;
			$this->_recur();
			$this->rrule='';			
		} else {
			
			if($this->percentage_complete==100)
				$this->percentage_complete=0;
			
			$this->completion_time = 0;
			
			if($this->status==Task::STATUS_COMPLETED)
				$this->status=Task::STATUS_NEEDS_ACTION;
		}
		
		if($save)
			$this->save();
	}
	
	/**
	 * Creates the new Recurring task when the rrule is not empty
	 */
	private function _recur(){
		if(!empty($this->rrule)) {

			$rrule = new \GO\Base\Util\Icalendar\Rrule();
			$rrule->readIcalendarRruleString($this->due_time, $this->rrule);
		
			$nextDueTime = $rrule->getNextRecurrence($this->due_time+1);
			
			if($nextDueTime){
				
				$data = array(
					'completion_time'=>0,
					'start_time'=>$nextDueTime-$this->due_time+$this->start_time,
					'due_time'=>$nextDueTime,
					'status'=>Task::STATUS_NEEDS_ACTION,
					'percentage_complete'=>0
				);
				
				// If a reminder is set, then calculate the difference between the start dates of the old and the new task.
				// Then add that difference to the reminder time for the new event. (So the reminder will also move forward)
				if(!empty($this->reminder)){
					$diff = $data['start_time'] - $this->start_time;
					$data['reminder'] = $this->reminder + $diff;
				}

				$dup = $this->duplicate($data);
				
				$this->copyLinks($dup);
			}
		}
	}
	
	/**
	 * The files module will use this function.
	 */
	public function buildFilesPath() {

		return 'tasks/' . \GO\Base\Fs\Base::stripInvalidChars($this->tasklist->name) . '/' . date('Y', $this->due_time) . '/' . \GO\Base\Fs\Base::stripInvalidChars($this->name).' ('.$this->id.')';
	}
	
	public function defaultAttributes() {
		$settings = Settings::model()->getDefault(\GO::user());
		$defaultTasklist = Tasklist::model()->findByPk($settings->default_tasklist_id);
		if(empty($defaultTasklist)) {
			$oldPermissions = \GO::setIgnoreAclPermissions(true);
			$defaultTasklist = new Tasklist();
			$defaultTasklist->name = \GO::user()->name;
			$defaultTasklist->user_id = \GO::user()->id;
			if($defaultTasklist->save()) {
				$settings->default_tasklist_id=$defaultTasklist->id;
				$settings->save();
			}
			\GO::setIgnoreAclPermissions($oldPermissions);
		}
		
		$defaults = array(
				'status' => Task::STATUS_NEEDS_ACTION,
				//'remind' => $settings->remind,
				'start_time'=> time(),
				'due_time'=> time(),
				'tasklist_id'=>$defaultTasklist->id,
				//'reminder' =>$this->getDefaultReminder(time())
		);
		$defaults['reminder']=$this->getDefaultReminder(time());
		
		return $defaults;
	}
	
	public function getDefaultReminder($startTime){
		$settings = Settings::model()->getDefault(\GO::user());
		
		if(!$settings->remind){
			return 0;
		}
		
		$tmp = \GO\Base\Util\Date::date_add($startTime, - $settings->reminder_days);
		
		// Set default to 8:00 when reminder_time is not set.
		$rtime = empty($settings->reminder_time) ? "08:00" : $settings->reminder_time;
		$dateString = date('Y-m-d', $tmp).' '.$rtime;
		
		$time = strtotime($dateString);
		return $time;
	}
	
	
	/**
	 * Get vcalendar data for an *.ics file.
	 * 
	 * @return StringHelper 
	 */
	public function toICS() {		
		
		$c = new \GO\Base\VObject\VCalendar();		
		$c->add(new \GO\Base\VObject\VTimezone());
		$c->add($this->toVObject());		
		return $c->serialize();		
	}
	
	public function toVCS(){
		$c = new \GO\Base\VObject\VCalendar();		
		$vobject = $this->toVObject('');
		$c->add($vobject);		
		
		\GO\Base\VObject\Reader::convertICalendarToVCalendar($c);
		
		return $c->serialize();		
	}
	
	
	/**
	 * Get this task as a VObject. This can be turned into a vcalendar file data.
	 * 
	 * @return Sabre\VObject\Component 
	 */
	public function toVObject(){
		
		$calendar = new Sabre\VObject\Component\VCalendar();
		$e=$calendar->createComponent('VTODO');
		
		$e->uid=$this->uuid;	
		
		$e->add('dtstamp', new \DateTime("now", new \DateTimeZone('UTC')));
		
		$mtimeDateTime = new \DateTime('@'.$this->mtime);
		$mtimeDateTime->setTimezone(new \DateTimeZone('UTC'));		
		$e->add('LAST-MODIFIED', $mtimeDateTime);
				
		$ctimeDateTime = new \DateTime('@'.$this->mtime);
		$ctimeDateTime->setTimezone(new \DateTimeZone('UTC'));
		$e->add('created', $ctimeDateTime);
		
		$e->summary = $this->name;
		
		$e->status = $this->status;
		
		$dateType = "DATE";
		
		if(!empty($this->start_time)) {
			$e->add('dtstart', \GO\Base\Util\Date\DateTime::fromUnixtime($this->start_time), array('VALUE'=>$dateType));
		}
		
		$e->add('due', \GO\Base\Util\Date\DateTime::fromUnixtime($this->due_time), array('VALUE'=>$dateType));
		
		
		
		if($this->completion_time>0){
			$e->add('completed', \GO\Base\Util\Date\DateTime::fromUnixtime($this->completion_time), array('VALUE'=>$dateType));
		}
		
		if(!empty($this->percentage_complete))
			$e->add('percent-complete',$this->percentage_complete);
		
		if(!empty($this->description))
			$e->description=$this->description;
		
		//todo exceptions
		if(!empty($this->rrule)){
			$e->rrule=str_replace('RRULE:','',$this->rrule);					
		}
		
		switch($this->priority) {
			case self::PRIORITY_LOW:
				$e->priority = 9; break;
			case self::PRIORITY_HIGH:
				$e->priority = 1; break;
			default: $e->priority = 5;
		}
		
		if($this->reminder>0){
			
			$a=$calendar->createComponent('VALARM');
			
//			BEGIN:VALARM
//ACTION:DISPLAY
//TRIGGER;VALUE=DURATION:-PT5M
//DESCRIPTION:Default Mozilla Description
//END:VALARM
			
			$a->action='DISPLAY';			
			$a->add('trigger',date('Ymd\THis', $this->reminder), array('value'=>'DATE-TIME'));			
			$a->description="Alarm";			
		
						
			//for funambol compatibility, the \GO\Base\VObject\Reader class use this to convert it to a vcalendar 1.0 aalarm tag.
			$e->{"X-GO-REMINDER-TIME"}=date('Ymd\THis', $this->reminder);
			$e->add($a);
		}
		
		return $e;
	}
	
	
	/**
	 * Import a task from a VObject 
	 * 
	 * @param Sabre\VObject\Component $vobject
	 * @param array $attributes Extra attributes to apply to the task. Raw values should be past. No input formatting is applied.
	 * @return Task 
	 */
	public function importVObject(Sabre\VObject\Component $vobject, $attributes=array()){
		//$event = new \GO\Calendar\Model\Event();
		
		$this->uuid = (string) $vobject->uid;
		$this->name = (string) $vobject->summary;
		$this->description = (string) $vobject->description;
	
		if(!empty($vobject->dtstart))
			$this->start_time = $vobject->dtstart->getDateTime()->format('U');
		
		if(!empty($vobject->dtend)){
			$this->due_time = $vobject->dtend->getDateTime()->format('U');
			
			if(empty($vobject->dtstart))
				$this->start_time=$this->due_time;
		}
		
		if(!empty($vobject->due)){
			$this->due_time = $vobject->due->getDateTime()->format('U');
		}
		if(empty($vobject->dtstart)){
			$this->start_time = 0;
		}
				
		if($vobject->dtstamp)
			$this->mtime=$vobject->dtstamp->getDateTime()->format('U');
		
		if(empty($this->due_time))
			$this->due_time=time();
		
		if($vobject->rrule){			
			$rrule = new \GO\Base\Util\Icalendar\Rrule();
			$rrule->readIcalendarRruleString($this->start_time, (string) $vobject->rrule);	
			$rrule->shiftDays(false);
			$this->rrule = $rrule->createRrule();
			
			if(isset($rrule->until))
				$this->repeat_end_time = $rrule->until;
		}		
		
		//var_dump($vobject->status);
		if($vobject->status)
			$this->status=(string) $vobject->status;
		
		if($vobject->duration){
			$duration = \GO\Base\VObject\Reader::parseDuration($vobject->duration);
			$this->due_time = $this->start_time+$duration;
		}
		
		if(!empty($vobject->priority))
		{			
			if((string) $vobject->priority>5)
			{
				$this->priority=self::PRIORITY_LOW;
			}elseif((string) $vobject->priority<3)
			{
				$this->priority=self::PRIORITY_HIGH;				
			}else
			{
				$this->priority=self::PRIORITY_NORMAL;
			}
		}
		
		if(!empty($vobject->completed)){
			$this->completion_time=$vobject->completed->getDateTime()->format('U');
			$this->status='COMPLETED';
		}else
		{
			if(empty($vobject->status)) {
				$this->status = self::STATUS_NEEDS_ACTION;
			}
			$this->completion_time=0;
		}
		
		if(!empty($vobject->{"percent-complete"}))
			$this->percentage_complete=(string) $vobject->{"percent-complete"};
		
		
		if($this->status=='COMPLETED' && empty($this->completion_time))
			$this->completion_time=time();
		
		$this->reminder=0;
		if($vobject->valarm && $vobject->valarm->trigger){
			$date = $vobject->valarm->getEffectiveTriggerTime();
			if($date) {
				$this->reminder = $date->format('U');
			}
		}		
		
		$this->setAttributes($attributes, false);
		$this->cutAttributeLengths();
		if($this->due_time < $this->start_time)
			$this->due_time = $this->start_time;
		$this->save();
		
		return $this;
	}	
	
	/**
	 * Check is this task is over due.
	 * 
	 * @return boolean 
	 */
	public function isLate(){
		$today = date("Ymd");
		return $this->status!='COMPLETED' && date("Ymd",$this->due_time) < $today;
	}
	
	public function isActive() {
		$today = date("Ymd");
		return (date("Ymd",$this->start_time) <= $today && date("Ymd",$this->due_time) >= $today);
	}
	
	public function getProjectName() {
		if(!$this->project2) {
			return null;
		}
		$parts = explode('/', $this->project2->path);
		
		$name = array_pop($parts);
		
		$next = array_pop($parts); 
		
		return $next ? $next . '/' . $name : $name;
	}
}
