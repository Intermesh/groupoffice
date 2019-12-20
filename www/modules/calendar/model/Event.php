<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * 
 * @property int $reminder The number of seconds prior to the start of the event.
 * @property int $exception_event_id If this event is an exception it holds the id of the original event
 * @property int $recurrence_id If this event is an exception it holds the date (not the time) of the original recurring instance. It can be used to identity it with an vcalendar file.
 * @property boolean $is_organizer True if the owner of this event is also the organizer.
 * @property string $owner_status The status of the owner of this event if this was an invitation
 * @property int $exception_for_event_id
 * @property int $sequence
 * @property int $category_id
 * @property boolean $read_only
 * @property boolean $is_virtual Events that are not in the event table: (holidays, birthdays, leavedays, tasks) will have this TRUE use it to disable contextmenu
 * @property int $files_folder_id
 * @property string $background eg. "EBF1E2"
 * @property string $rrule
 * @property boolean $private
 * @property int $resource_event_id Set this for a resource event. This is the personal event this resource belongs to.
 * @property boolean $busy
 * @property int $mtime
 * @property int $ctime
 * @property int $repeat_end_time
 * @property string $location
 * @property string $description
 * @property string $name
 * @property string $status
 * @property boolean $all_day_event
 * @property int $end_time
 * @property string $timezone
 * @property int $start_time
 * @property int $user_id
 * @property int $calendar_id
 * @property string $uuid
 * 
 * @property Participant $participants
 * @property int $muser_id
 * 
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

namespace GO\Calendar\Model;

use DateInterval;
use DateTime;
use DateTimeZone;
use GO\Calendar\Model\Exception;
use GO;
use GO\Base\Util\StringHelper;
use Sabre;
use Swift_Attachment;
use Swift_Mime_ContentEncoder_PlainContentEncoder;

class Event extends \GO\Base\Db\ActiveRecord {
	
	use \go\core\orm\CustomFieldsTrait;

	const STATUS_TENTATIVE = 'TENTATIVE';
//	const STATUS_DECLINED = 'DECLINED';
//	const STATUS_ACCEPTED = 'ACCEPTED';
	const STATUS_CANCELLED = 'CANCELLED';
	const STATUS_CONFIRMED = 'CONFIRMED';
	const STATUS_NEEDS_ACTION = 'NEEDS-ACTION';
	const STATUS_DELEGATED = 'DELEGATED';

	/**
	 * The date where the exception needs to be created. If this is set on a new event
	 * an exception will automatically be created for the recurring series. exception_for_event_id needs to be set too.
	 * 
	 * @var timestamp 
	 */
	public $exception_date;

	public $dontSendEmails=false;
	
	public $sequence;
	
	
	/**
	 * Indicating that this is an update for a related event.
	 * eg. The organizer modifies the event and all events for invitees.
	 * 
	 * @var boolean
	 */
	public $updatingRelatedEvent=false;
	
	/**
	 * Flag used when importing. On import we allow participant events to be 
	 * modified even when they are not the organizer. Because a meeting request
	 * coming from the organizer must be procesed by the participant.
	 * 
	 * @var boolean 
	 */
	private $_isImport=false;
	
	
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
	
	protected function init() {

		$this->columns['calendar_id']['required']=true;
		$this->columns['start_time']['gotype'] = 'unixtimestamp';
		$this->columns['end_time']['greater'] = 'start_time';
		$this->columns['end_time']['gotype'] = 'unixtimestamp';
		$this->columns['repeat_end_time']['gotype'] = 'unixtimestamp';		
		$this->columns['repeat_end_time']['greater'] = 'start_time';
		//$this->columns['category_id']['required'] = \GO\Calendar\CalendarModule::commentsRequired();
		
		parent::init();
	}
	
	public function isValidStatus($status){
		return ($status==self::STATUS_CANCELLED || $status==self::STATUS_CONFIRMED || $status==self::STATUS_DELEGATED || $status==self::STATUS_TENTATIVE || $status==self::STATUS_NEEDS_ACTION);			
	}

	public function aclField() {
		return 'calendar.acl_id';
	}

	public function tableName() {
		return 'cal_events';
	}

	public function hasFiles() {
		return true;
	}
	
	public function hasLinks() {
		return true;
	}
//
//	public function countLinks() {
//		$sql = "SELECT count(*) FROM `go_links_$table` WHERE ".
//			"`id`=".intval($this_id).";";
//		$stmt = $this->getDbConnection()->query($sql);
//		return !empty($stmt) ? $stmt->rowCount() : 0;
//	}
	
	public function countReminders() {
		
		$modelTypeModel = \GO\Base\Model\ModelType::model()->findSingleByAttribute('model_name',$this->className());
		
		$stmt = \GO\Base\Model\Reminder::model()->findByAttributes(array(
			'model_id' => $this->id,
			'model_type_id'=> $modelTypeModel->id
		));
		
		return !empty($stmt) ? $stmt->rowCount() : 0;
		
	}
	
	
	public function defaultAttributes() {
		
		
		$defaults = array(
			'status' => self::STATUS_CONFIRMED,
			'start_time'=> \GO\Base\Util\Date::roundQuarters(time()), 
			'end_time' => \GO\Base\Util\Date::roundQuarters(time()+3600),
			'timezone' => \GO::user()->timezone
		);
	
		
		if($this->isResource()) {
			$defaults['status'] = self::STATUS_NEEDS_ACTION;
		}
		
		$settings = Settings::model()->getDefault(\GO::user());
		if($settings){		
			$defaults = array_merge($defaults, array(
				'reminder' => $settings->reminder,
				'calendar_id'=>$settings->calendar_id,
				'background'=>$settings->background
						));
		}
		
		return $defaults;
	}
	
	public function customfieldsModel() {
		return "GO\Calendar\Customfields\Model\Event";
	}


	public function relations() {
		return array(
				'_exceptionEvent'=>array('type' => self::BELONGS_TO, 'model' => 'GO\Calendar\Model\Event', 'field' => 'exception_for_event_id'),
				'recurringEventException'=>array('type' => self::HAS_ONE, 'model' => 'GO\Calendar\Model\Exception', 'field' => 'exception_event_id'),//If this event is an exception for a recurring series. This relation points to the exception of the recurring series.
				'calendar' => array('type' => self::BELONGS_TO, 'model' => 'GO\Calendar\Model\Calendar', 'field' => 'calendar_id','labelAttribute'=>function($model){return $model->calendar->name;}),
				'category' => array('type' => self::BELONGS_TO, 'model' => 'GO\Calendar\Model\Category', 'field' => 'category_id'),
				'participants' => array('type' => self::HAS_MANY, 'model' => 'GO\Calendar\Model\Participant', 'field' => 'event_id', 'delete' => true),
				'exceptions' => array('type' => self::HAS_MANY, 'model' => 'GO\Calendar\Model\Exception', 'field' => 'event_id', 'delete' => true),
				'exceptionEvents' => array('type' => self::HAS_MANY, 'model' => 'GO\Calendar\Model\Event', 'field' => 'exception_for_event_id', 'delete' => true),
				'resources' => array('type' => self::HAS_MANY, 'model' => 'GO\Calendar\Model\Event', 'field' => 'resource_event_id', 'delete' => true)
		);
	}
	
	protected function log($action, $save = true, $modifiedCustomfieldAttrs=false) {
		if(!$this->updatingRelatedEvent) {
			return parent::log($action, $save, $modifiedCustomfieldAttrs);
		} else
		{
			return true;
		}
	}

	protected function getCacheAttributes() {
		
		if(GO::router()->getControllerAction()!='buildsearchcache' && !$this->isDeleted() && !$this->isModified(["calendar_id", "description", "private", "start_time", "name"])) {
			return false;
		}
		
		$calendarName = empty($this->calendar) ? '' :$this->calendar->name;

		$description = $calendarName;

		 if(!$this->private && !empty($this->description) ){
			$description .= ', ' . $this->description;
		 }

		return array(
				'name' => $this->private ?  \GO::t("Private", "calendar") : $this->name,
				'description' =>  $description,
				'mtime'=>$this->start_time
		);
	}

	protected function getLocalizedName() {
		return \GO::t("Event", "calendar");
	}

	/**
	 * The files module will use this function.
	 */
	public function buildFilesPath() {

		return 'calendar/' . \GO\Base\Fs\Base::stripInvalidChars($this->calendar->name) . '/' . date('Y', $this->start_time) . '/' . \GO\Base\Fs\Base::stripInvalidChars($this->name).' ('.$this->id.')';
	}

	/**
	 * Get the color for the current status of this event
	 * 
	 * @return StringHelper 
	 */
	public function getStatusColor(){
		
		switch($this->status){
			case Event::STATUS_TENTATIVE:
				$color = 'FFFF00'; //Yellow
			break;
			case Event::STATUS_CANCELLED:
				$color = 'FF0000'; //Red			
			break;
//			case Event::STATUS_ACCEPTED:
//				$color = '00FF00'; //Lime
//			break;
			case Event::STATUS_CONFIRMED:
				$color = '32CD32'; //LimeGreen
			break;
			case Event::STATUS_DELEGATED:
				$color = '0000CD'; //MediumBlue
			break;
		
			default:
			case Event::STATUS_NEEDS_ACTION:
				$color = 'FF8C00'; //DarkOrange
			break;
		}
		
		return $color;
	}
	
	/**
	 * Get the date interval for the event.
	 * 
	 * @return array 
	 */
	public function getDiff() {
		$startDateTime = new \GO\Base\Util\Date\DateTime(date('c', $this->start_time));
		$endDateTime = new \GO\Base\Util\Date\DateTime(date('c', $this->end_time));

		return $startDateTime->diff($endDateTime);
	}

	/**
	 * Add an Exception for the Event if it is recurring
	 * 
	 * @param Unix Timestamp $date The date where the exception belongs to
	 * @param Int $for_event_id The event id of the event where the exception belongs to
	 */
	public function addException($date, $exception_event_id=0) {
		
		\GO::debug('Add exception '.$this->id.' '.date('c', $date));
		
		if(!$this->isRecurring())
			throw new \Exception("Can't add exception to non recurring event ".$this->id);
		
		if(!($exception = $this->hasException($date))){
			$exception = new \GO\Calendar\Model\Exception();						
		}
		
		$exception->event_id = $this->id;
		$exception->time = mktime(date('G',$this->start_time),date('i',$this->start_time),0,date('n',$date),date('j',$date),date('Y',$date)); // Needs to be a unix timestamp		
		$exception->exception_event_id=$exception_event_id;

	
		if(!$exception->save()){
			throw new Exception("Event exception not saved: ".var_export($exception->getValidationErrors(), true));
		}
			
		
	}

	/**
	 * This Event needs to be reinitialized to become an Exception of its own on the given Unix timestamp.
	 * It will not save the event and doesn't copy participants. Use createExcetionEvent for that.
	 * 
	 * @param int $exceptionDate Unix timestamp
	 */
	public function getExceptionEvent($exceptionDate) {
		

		$att['rrule'] = '';
		$att['repeat_end_time']=0;
		$att['exception_for_event_id'] = $this->id;
		$att['exception_date'] = $exceptionDate;
		
		$diff = $this->getDiff();

		$d = date('Y-m-d', $exceptionDate);
		$t = date('G:i', $this->start_time);

		$att['start_time'] = strtotime($d . ' ' . $t);

		$endTime = new \GO\Base\Util\Date\DateTime(date('c', $att['start_time']));
		$endTime->add($diff);
		$att['end_time'] = $endTime->format('U');
		
		
		
		$duplicate =  $this->duplicate($att, false);
		
//		$this->copyLinks($duplicate);
		
		return $duplicate;
	}
	

	public function createException($exceptionDate){
		$stmt = $this->getRelatedParticipantEvents(true);//A meeting can be multiple related events sharing the same uuid
		foreach($stmt as $event){			
			$event->addException($exceptionDate, 0);
		}
	}
	
	/**
	 * Check if this model has resource conflicts.
	 * This only works with existing events and NOT with new events.(Will allways return: false)
	 * 
	 * @return mixed (false, Array with resource events)
	 */
	public function hasResourceConflicts(){
		
		$hasConflict = false;
		$foundConflicts = array();				
		
		if($this->isNew || $this->isResource()){
			// Not possible to determine this when having a new model.
			// Because the resources are not created yet.
			return false;
		} else {
		
			$resources = $this->resources;

			foreach($resources as $resource){
				
				$resource->start_time = $this->start_time;
				$resource->end_time = $this->end_time;

				$conflicts = $resource->getConflictingEvents();
				
				if(count($conflicts) > 0){
					$foundConflicts[] = $resource;
					$hasConflict = true;
				}
			}
			
			if($hasConflict){
				return $foundConflicts;
			} else {
				return false;
			}
		}
	}
	
	/**
	 * Create an exception for a recurring series.
	 * 
	 * @param int $exceptionDate
	 * @return Event
	 */
	public function createExceptionEvent($exceptionDate, $attributes=array(), $dontSendEmails=false){
		
		
		if(!$this->isRecurring()){
			throw new \Exception("Can't create exception event for non recurring event ".$this->id);
		}
		
		$oldIgnore = \GO::setIgnoreAclPermissions();
		$returnEvent = false;
		if($this->isResource())
			$stmt = array($this); //resource is never a group of events
		else
			$stmt = $this->getRelatedParticipantEvents(true);//A meeting can be multiple related events sharing the same uuid
		
		$resources = array();
		
		$freeBusyInstalled = \GO::modules()->isInstalled("freebusypermissions");
		
		foreach($stmt as $event){
			
			if($freeBusyInstalled && !$event->calendar->checkPermissionlevel(\GO\Base\Model\Acl::WRITE_PERMISSION) && !\GO\Freebusypermissions\FreebusypermissionsModule::hasFreebusyAccess(\GO::user()->id, $event->calendar->user_id)) {
				//no permission to update
				continue;
			}
			
			//workaround for old events that don't have the exception ID set. In this case
			//getRelatedParticipantEvents fails. This won't happen with new events
			if(!$event->isRecurring())
				continue;
			
			\GO::debug("Creating exception for related participant event ".$event->name." (".$event->id.") ".date('c', $exceptionDate));
			
			$exceptionEvent = $event->getExceptionEvent($exceptionDate);
			$exceptionEvent->dontSendEmails = $dontSendEmails;
			$exceptionEvent->setAttributes($attributes);
			if(!$exceptionEvent->save())
				throw new \Exception("Could not create exception: ".var_export($exceptionEvent->getValidationErrors(), true));
			

			$event->copyLinks($exceptionEvent);
			
			$event->addException($exceptionDate, $exceptionEvent->id);

			
			
			
			
			
			$event->duplicateRelation('participants', $exceptionEvent, array('dontCreateEvent' => true));
			
			
			if(!$event->isResource() && $event->is_organizer){
				$stmt = $event->resources();		
				foreach($stmt as $resource){
					$resources[]=$resource;
				}
				$resourceExceptionEvent = $exceptionEvent;
			}
			
			if($event->id==$this->id)
				$returnEvent=$exceptionEvent;
		}
		
		foreach($resources as $resource){
			\GO::debug("Creating exception for resource: ".$resource->name);
			$resource->createExceptionEvent($exceptionDate, array('resource_event_id'=>$resourceExceptionEvent->id), $dontSendEmails);
		}		

		\GO::setIgnoreAclPermissions($oldIgnore);
		return $returnEvent;
	}

	public function attributeLabels() {
		$attr = parent::attributeLabels();
		$attr['repeat_end_time']=\GO::t("Repeat until", "calendar");
		$attr['start_time']=\GO::t("Starts at", "calendar");
		$attr['end_time']=\GO::t("Ends at", "calendar");
		return $attr;
	}
	
	public function validate() {
		if($this->rrule != ""){			
			$rrule = new \GO\Base\Util\Icalendar\Rrule();
			$rrule->readIcalendarRruleString($this->start_time, $this->rrule);						
			$this->repeat_end_time = $rrule->until;
		}
		
		//ignore reminders longer than 90 days.
		if($this->reminder > 86400 * 90){
			\GO::debug("WARNING: Ignoring reminder that is longer than 90 days before event start");
			$this->reminder = null;
		}	
		
		
		if($this->exception_for_event_id != 0 && $this->exception_for_event_id == $this->id){
			throw new \Exception("Exception event ID can't be set to ID");
		}
		
		if($this->exception_for_event_id != 0 && !empty($this->rrule)) {
			throw new \Exception("Can't create exception with RRULE");
		}

		$resourceConflicts = $this->hasResourceConflicts();

		if($resourceConflicts !== false){


			$errorMessage = GO::t("Could not move event because the following resources are not available:", "calendar");

			foreach ($resourceConflicts as $rc){
				$errorMessage .= '<br />- '.$rc->calendar->name;
			}

			$this->setValidationError('start_time', $errorMessage);
		}
		return parent::validate();
	}
	
		public function getRelevantMeetingAttributes(){
		return array("name","start_time","end_time","location","description","calendar_id","rrule","repeat_end_time");
	}

	
	public function setAttributes($attributes, $format=null){
		parent::setAttributes($attributes, $format);
		
		if($this->getIsNew() ) {
			$this->reevaluateStatus();
		}
		
	}

	private function getResourceAdminIds() {
		$adminUserIds=array();
		if($this->isResource()){ 
			
			$groupAdminsStmt= $this->calendar->group->admins;
			while($adminUser = $groupAdminsStmt->fetch()){
				$adminUserIds[] = $adminUser->id;
			}
			
		} 
		return $adminUserIds;
	}

	
	private function isCurrentUserResourceAdmin() {
		return in_array(\GO::user()->id, $this->getResourceAdminIds());
	}


	private function reevaluateStatus() {
		
		// if is it is a resource and non resource admin set status to neet action
		if($this->isResource()) {
			
			if (!$this->isCurrentUserResourceAdmin()) {
				$this->status =  self::STATUS_NEEDS_ACTION;
			} 
		}
		
	}
	
	protected function beforeSave() {
		
		GO::debug("#### EVENT BEFORE SAVE ####");
		
		if($this->rrule != ""){			
			$rrule = new \GO\Base\Util\Icalendar\Rrule();
			$rrule->readIcalendarRruleString($this->start_time, $this->rrule);						
			$this->repeat_end_time = intval($rrule->until);
		}
		
		
		if(!GO::user()->isAdmin()) {		
			//if this is not the organizer event it may only be modified by the organizer
			if(!$this->is_organizer && !$this->updatingRelatedEvent && !$this->_isImport && !$this->isNew && $this->isModified($this->getRelevantMeetingAttributes())){		
	//			$organizerEvent = $this->getOrganizerEvent();
	//			if($organizerEvent && !$organizerEvent->checkPermissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION) || !$organizerEvent && !$this->is_organizer){
	//				\GO::debug($this->getModifiedAttributes());
	//				\GO::debug($this->_attributes);
					throw new \GO\Base\Exception\AccessDenied();
	//			}			
			}
		}
		
//		//Don't set reminders for the superadmin
//		if($this->calendar->user_id==1 && \GO::user()->id!=1 && !\GO::config()->debug)
//			$this->reminder=0;
		
		
		$this->reevaluateStatus();
		
		if($this->isResource()){
			
			if($this->status == self::STATUS_CONFIRMED){
				$this->background='CCFFCC';
			}else
			{
				$this->background='FF6666';
			}			
		}	
		
		return parent::beforeSave();
	}

	protected function afterDbInsert() {
		if(empty($this->uuid)){
			$this->uuid = \GO\Base\Util\UUID::create('event', $this->id);
			return true;
		}else
		{
			return false;
		}
	}
	
	
	protected function afterDelete() {

		$this->deleteReminders();		
		
		if($this->is_organizer){
//
//	This is dangerous: when you first import the same ICS into two different calendars,
//	and the delete one of the calendars, all the imported events in the other calendars
//	would be deleted also.
//	
//			$stmt = $this->getRelatedParticipantEvents();
//			
//			foreach($stmt as $event){
//				//prevent loop for invalid is_organizer flag
//				$event->is_organizer=false;
//				$event->delete(true);
//			}
		}else
		{
			$participants = $this->getParticipantsForUser();
			
			foreach($participants as $participant){
				$participant->updateRelatedParticipants = false;
				$participant->dontCreateEvent = true;
				$participant->status=Participant::STATUS_DECLINED;
				$participant->save(true);
			}
		}
		
		//for sync update master series
		if($this->isException()) {
			if($this->_exceptionEvent) {
				$this->_exceptionEvent->mtime = time();
				$this->_exceptionEvent->save(true);
			}
		}
		
		return parent::afterDelete();
	}
	
	public static function reminderDismissed($reminder, $userId){		
		
		//this listener function is added in \GO\Calendar\CalendarModule
		
		if($reminder->model_type_id==Event::model()->modelTypeId()){			
			$event = Event::model()->findByPk($reminder->model_id);
			if($event && ($nextTime = $event->getNextReminderTime($reminder->time+$event->reminder))){
				$event->addReminder($event->name, $nextTime, $userId, $nextTime+$event->reminder);								
			}			
		}
	}
	
	/**
	 * Get the next reminder time of this event
	 * 
	 * @return int
	 */
	public function getNextReminderTime($lastReminderTime = 0){
		
		if($this->reminder===null)
			return false;

		if($this->isRecurring()){
			$next = time()+$this->reminder;
			if($next > $lastReminderTime) {
				$lastReminderTime = $next;
			}



			$rRule = $this->getRecurrencePattern();
			$rRule->fastForward(new \DateTime('@'.$lastReminderTime));
			$nextTime = $rRule->current();
			while($nextTime && $this->hasException($nextTime->getTimeStamp())){
				$rRule->next();
				$nextTime = $rRule->current();
			}
			

			if($nextTime && $nextTime->getTimeStamp()>time()){
				return $nextTime->getTimeStamp()-$this->reminder;
			}else
				return false;
				
		}  else {
			$nextTime = $this->start_time-$this->reminder;
			if($nextTime>time())
				return $nextTime;
			else
				return false;
		}
	}
	
	/**
	 * Check if this event is recurring
	 * 
	 * @return boolean 
	 */
	public function isRecurring(){
		return $this->rrule!="";
	}

	/**
	 * Check if this event is a fullday event
	 * 
	 * @return boolean 
	 */
	public function isFullDay() {
		return !empty($this->all_day_event);
	}
	
	public function hasReminders() {
		return !is_null($this->reminder);
	}
	
	public function isException() {
		return $this->exception_for_event_id != 0;
	}
	
	protected function afterSave($wasNew) {
				
		//move exceptions if this event was moved in time
		if(!$wasNew && !empty($this->rrule) && $this->isModified('start_time')){
			$diffSeconds = $this->start_time-$this->getOldAttributeValue('start_time');
			$stmt = $this->exceptions();
			while($exception = $stmt->fetch()){
				$exception->time+=$diffSeconds;
				$exception->save();
			}
		}
	
		if($this->isResource()){
			
			if ((! $this->isCurrentUserResourceAdmin() || $this->isModified('status'))&& $this->end_time > time()) {
				$this->_sendResourceNotification($wasNew);
			}
		}else
		{
			if(!$wasNew && $this->hasModificationsForParticipants())
				$this->_updateResourceEvents();
		}

		$this->setReminder();

		//update events that belong to this organizer event
		if($this->is_organizer && !$wasNew && !$this->isResource()){
			$updateAttr = array(
					'name'=>$this->name,
					'start_time'=>$this->start_time, 
					'end_time'=>$this->end_time, 
					'location'=>$this->location,
					'description'=>$this->description,
					'rrule'=>$this->rrule,
					'status'=>$this->status,
					'repeat_end_time'=>$this->repeat_end_time
							);
			
			if($this->isModified(array_keys($updateAttr))){

				$events = $this->getRelatedParticipantEvents();
				$freeBusyInstalled = \GO::modules()->isInstalled("freebusypermissions");
				foreach($events as $event){
					\GO::debug("updating related event: ".$event->id);

					if($event->id!=$this->id && $this->is_organizer!=$event->is_organizer){ //this should never happen but to prevent an endless loop it's here.
						
						if($freeBusyInstalled && ! \GO\Freebusypermissions\FreebusypermissionsModule::hasFreebusyAccess(\GO::user()->id, $event->calendar->user_id)) {
							//no permission to update
							continue;
						}
						
						$event->setAttributes($updateAttr, false);
						$event->updatingRelatedEvent=true;
						$event->save(true);
						
//						$stmt = $event->participants;
//						$stmt->callOnEach('delete');
//	
//						$this->duplicateRelation('participants', $event);
					}
				}
			}
		}
		if($this->isModified()) {
			Calendar::versionUp($this->calendar_id);
		}
		
		//for sync update master series
		if($this->isException() && $this->_exceptionEvent) {
			$this->_exceptionEvent->mtime = time();
			$this->_exceptionEvent->save(true);
			
		}
		return parent::afterSave($wasNew);
	}
	
	public function setReminder(){
		
		if($this->reminder !== null){
			$remindTime = $this->getNextReminderTime();
		
			if($remindTime){
				$this->deleteReminders();
				$this->addReminder($this->name, $remindTime, $this->calendar->user_id, $remindTime+$this->reminder);
			}
		} else {
			$this->deleteReminders();
		}
	}
	
	/**
	 * Get's all related events that are in the participant's calendars.
	 * 
	 * @return Event
	 */
	public function getRelatedParticipantEvents($includeThisEvent=false){
		$findParams = \GO\Base\Db\FindParams::newInstance()->ignoreAcl();
		
		$start_time = $this->isModified('start_time') ? $this->getOldAttributeValue('start_time') : $this->start_time;
		
		$findParams->getCriteria()
						->addCondition("uuid", $this->uuid) //recurring series and participants all share the same uuid
						->addCondition('start_time', $start_time) //make sure start time matches for recurring series
						->addCondition("exception_for_event_id", 0, $this->exception_for_event_id==0 ? '=' : '!='); //the master event or a single occurrence can start at the same time. Therefore we must check if exception event has a value or is 0.
		
		if(!$includeThisEvent)
			$findParams->getCriteria()->addCondition('id', $this->id, '!=');
		
						
		$stmt = Event::model()->find($findParams);
		
		return $stmt;
	}
	
	
	
	/**
	 * If this is a resource of the current user ignore ACL permissions when deleting 
	 */
	public function delete($ignoreAcl=false)
	{
		if(!empty($this->resource_event_id) && $this->user_id == \GO::user()->id)
			$success = parent::delete(true);
		else
			$success = parent::delete($ignoreAcl);
		if($success)
			Calendar::versionUp($this->calendar_id);
		return $success;
	}
	
	public function hasModificationsForParticipants(){
		return $this->isModified("start_time") || $this->isModified("end_time") || $this->isModified("name") || $this->isModified("location") || $this->isModified('status');
	}
	
	/**
	 * Is this a private event for the current user. If the event or the calendar
	 * is owned by the current user it will not be displayed as private.
	 * 
	 * @param \GO\Base\Model\User $user
	 */
	public function isPrivate(\GO\Base\Model\User $user=null){
		if(!isset($user))
			$user=\GO::user();
		
		return $this->private && 
			($user->id != $this->user_id) && 
			$user->id!=$this->calendar->user_id;	
	}
	
	/**
	 * Events may have related resource events that must be updated aftersave
	 */
	private function _updateResourceEvents(){
		$stmt = $this->resources();
		
		while($resourceEvent = $stmt->fetch()){
			
			$resourceEvent->name=$this->name;
			$resourceEvent->start_time=$this->start_time;
			$resourceEvent->end_time=$this->end_time;
			$resourceEvent->rrule=$this->rrule;
			$resourceEvent->repeat_end_time=$this->repeat_end_time;				
			$resourceEvent->status="NEEDS-ACTION";
			$resourceEvent->user_id=$this->user_id;
			$resourceEvent->save(true);
		}
	}
		
	private function _sendResourceNotification($wasNew){
		
		if(!$this->dontSendEmails && $this->hasModificationsForParticipants()){			
			$url = \GO::createExternalUrl('calendar', 'showEventDialog', array('event_id' => $this->id));		

			//send updates to the resource admins
			$adminUserIds=array();
			$stmt = $this->calendar->group->admins;

			while($adminUser = $stmt->fetch()){
				$adminUserIds[] = $adminUser->id;
				if($adminUser->id!=\GO::user()->id){
					
				
					if($wasNew){

						if ($this->status==Event::STATUS_CONFIRMED) {
							$body = sprintf(\GO::t("%s has made a booking for the resource '%s' and confirmed the booking. You are the maintainer of this resource. Use the link below if you want to decline the booking.", "calendar"),$this->user->name,$this->calendar->name).'<br /><br />'
											. $this->toHtml()
											. '<br /><a href="'.$url.'">'.\GO::t("Open booking", "calendar").'</a>';

							$subject = sprintf(\GO::t("Resource '%s' booked for '%s' on '%s'", "calendar"),$this->calendar->name, $this->name, \GO\Base\Util\Date::get_timestamp($this->start_time,false));
						} else {
							$body = sprintf(\GO::t("%s has made a booking for the resource '%s'. You are the maintainer of this resource. Please open the booking to decline or approve it.", "calendar"),$this->user->name,$this->calendar->name).'<br /><br />'
											. $this->toHtml()
											. '<br /><a href="'.$url.'">'.\GO::t("Open booking", "calendar").'</a>';

							$subject = sprintf(\GO::t("Resource '%s' booked for '%s' on '%s'", "calendar"),$this->calendar->name, $this->name, \GO\Base\Util\Date::get_timestamp($this->start_time,false));
						}
					}else
					{
						$body = sprintf(\GO::t("%s has modified a booking for the resource '%s'. You are the maintainer of this resource. Please open the booking to decline or approve it.", "calendar"),$this->user->name,$this->calendar->name).'<br /><br />'
										. $this->toHtml()
										. '<br /><a href="'.$url.'">'.\GO::t("Open booking", "calendar").'</a>';

						$subject = sprintf(\GO::t("Resource '%s' booking for '%s' on '%s' modified", "calendar"),$this->calendar->name, $this->name, \GO\Base\Util\Date::get_timestamp($this->start_time,false));
					}

					$message = \GO\Base\Mail\Message::newInstance(
										$subject
										)->setFrom(\GO::user()->email, \GO::user()->name)
										->addTo($adminUser->email, $adminUser->name);

					$message->setHtmlAlternateBody($body);					

					\GO\Base\Mail\Mailer::newGoInstance()->send($message);
				}
			}
			

			//send update to user that booked the resource
			if($this->user_id!=\GO::user()->id
						&& in_array(\GO::user()->id,$adminUserIds)
				) {
				if($this->isModified('status')){				
					if($this->status==Event::STATUS_CONFIRMED){
						$body = sprintf(\GO::t("%s has accepted your booking for the resource '%s'.", "calendar"),\GO::user()->name,$this->calendar->name).'<br /><br />'
								. $this->toHtml();
								//. '<br /><a href="'.$url.'">'.\GO::t("Open booking", "calendar").'</a>';

						$subject = sprintf(\GO::t("Your booking for '%s' on '%s' is accepted", "calendar"),$this->calendar->name, $this->name, \GO\Base\Util\Date::get_timestamp($this->start_time,false));
					}else
					{
						$body = sprintf(\GO::t("%s has declined your booking for the resource '%s'.", "calendar"),\GO::user()->name,$this->calendar->name).'<br /><br />'
								. $this->toHtml();
								//. '<br /><a href="'.$url.'">'.\GO::t("Open booking", "calendar").'</a>';

						$subject = sprintf(\GO::t("Your booking for '%s' on '%s' is declined", "calendar"),$this->calendar->name, $this->name, \GO\Base\Util\Date::get_timestamp($this->start_time,false));
					}
				}else
				{
					$body = sprintf(\GO::t("%s has modified your booking for the resource '%s'.", "calendar"),\GO::user()->name,$this->calendar->name).'<br /><br />'
								. $this->toHtml();
//								. '<br /><a href="'.$url.'">'.\GO::t("Open booking", "calendar").'</a>';
					$subject = sprintf(\GO::t("Your booking for '%s' on '%s' in status '%s' is modified", "calendar"),$this->calendar->name, $this->name, \GO\Base\Util\Date::get_timestamp($this->start_time,false));
				}
				
				$url = \GO::createExternalUrl('calendar', 'openCalendar', array(
					'unixtime'=>$this->start_time
				));
		
				$body .= '<br /><a href="'.$url.'">'.\GO::t("Open calendar", "calendar").'</a>';

				$message = \GO\Base\Mail\Message::newInstance(
									$subject
									)->setFrom(\GO::user()->email, \GO::user()->name)
									->addTo($this->user->email, $this->user->name);

				$message->setHtmlAlternateBody($body);					

				\GO\Base\Mail\Mailer::newGoInstance()->send($message);
			}

		}
	}

	/**
	 *
	 * @var LocalEvent
	 */
	private $_calculatedEvents;
	
	
	/**
	 * Finds a specific occurence for a date.
	 * 
	 * @param int $exceptionDate
	 * @return Event
	 * @throws Exception
	 */
	public function findException($exceptionDate) {

		if ($this->exception_for_event_id != 0)
			throw new \Exception("This is not a master event");

		$startOfDay = \GO\Base\Util\Date::clear_time($exceptionDate);
		$endOfDay = \GO\Base\Util\Date::date_add($startOfDay, 1);

		$findParams = \GO\Base\Db\FindParams::newInstance();



		//must be an exception and start on the must start on the exceptionTime
		$exceptionJoinCriteria = \GO\Base\Db\FindCriteria::newInstance()
						->addCondition('id', 'e.exception_event_id', '=', 't', true, true);

		$findParams->join(Exception::model()->tableName(), $exceptionJoinCriteria, 'e');

//			$dayStart = \GO\Base\Util\Date::clear_time($exceptionDate);
//			$dayEnd = \GO\Base\Util\Date::date_add($dayStart,1);	
		$whereCriteria = \GO\Base\Db\FindCriteria::newInstance()
						->addCondition('exception_for_event_id', $this->id)
						->addCondition('time', $startOfDay, '>=', 'e')
						->addCondition('time', $endOfDay, '<', 'e');
		$findParams->criteria($whereCriteria);
//		$findParams->getCriteria()
//						->addCondition('exception_for_event_id', $this->id)
//						->addCondition('start_time', $startOfDay,'>=')
//						->addCondition('end_time', $endOfDay,'<=');
		
		$event = Event::model()->findSingle($findParams);

		return $event;
	}
	
	/**
	 * 
	 * @param int $exception_for_event_id
	 * @return LocalEvent
	 */
	public function getConflictingEvents($exception_for_event_id=0){
		
		$conflictEvents=array();
		
		
		
		
		$settings = Settings::model()->getDefault(GO::user());
		if(!$settings->check_conflict) {
			return $conflictEvents;
		}
		
		$findParams = \GO\Base\Db\FindParams::newInstance();
		$findParams->getCriteria()->addCondition("calendar_id", $this->calendar_id);
		if(!$this->isNew)
			$findParams->getCriteria()->addCondition("resource_event_id", $this->id, '<>');
		
		//find all events including repeating events that occur on that day.
		$conflictingEvents = Event::model()->findCalculatedForPeriod($findParams, 
						$this->start_time, 
						$this->end_time,
						true);
		
		while($conflictEvent = array_shift($conflictingEvents)) {			
			//\GO::debug("Conflict: ".$conflictEvent->getEvent()->id." ".$conflictEvent->getName()." ".\GO\Base\Util\Date::get_timestamp($conflictEvent->getAlternateStartTime())." - ".\GO\Base\Util\Date::get_timestamp($conflictEvent->getAlternateEndTime()));
			if($conflictEvent->getEvent()->id!=$this->id && (empty($exception_for_event_id) || $exception_for_event_id!=$conflictEvent->getEvent()->id)){
				$conflictEvents[]=$conflictEvent;
			}
		}
		
		return $conflictEvents;
	}

	/**
	 * Find events that occur in a given time period. They will be sorted on 
	 * start_time and name. Recurring events are calculated and added to the array.
	 * 
	 * @param \GO\Base\Db\FindParams $findParams
	 * @param int $periodStartTime
	 * @param int $periodEndTime
	 * @param boolean $onlyBusyEvents
	 * 
	 * @return LocalEvent  
	 */
	public function findCalculatedForPeriod($findParams, $periodStartTime, $periodEndTime, $onlyBusyEvents=false) {
		
		$stmt = $this->findForPeriod($findParams, $periodStartTime, $periodEndTime, $onlyBusyEvents);

		$this->_calculatedEvents = array();

		while ($event = $stmt->fetch()) {
			$this->_calculateRecurrences($event, $periodStartTime, $periodEndTime);
		}
		
		ksort($this->_calculatedEvents);

		return array_values($this->_calculatedEvents);
	}
	
	/**
	 * Find events that occur in a given time period. 
	 * 
	 * Recurring events are not calculated. If you need recurring events use
	 * findCalculatedForPeriod.
	 * 
	 * @param \GO\Base\Db\FindParams $findParams extra findparmas
	 * @param int $periodStartTime Start time as Unix timestamp
	 * @param int $periodEndTime Latest start time for the selected event as Unix timestamp
	 * @param \GO\Base\Db\FindParams $findParams 

	 * @param boolean $onlyBusyEvents
	 * @return \GO\Base\Db\ActiveStatement
	 */
	public function findForPeriod($findParams, $periodStartTime, $periodEndTime=0, $onlyBusyEvents=false){
		if (!$findParams)
			$findParams = \GO\Base\Db\FindParams::newInstance();

		// Make regular events exportable.
		$findParams->export('events');
		
		$findParams->select("t.*");
		
//		if($periodEndTime)
//			$findParams->getCriteria()->addCondition('start_time', $periodEndTime, '<');
		
		$findParams->getCriteria()->addModel(Event::model(), "t");
		
		if ($onlyBusyEvents)
			$findParams->getCriteria()->addCondition('busy', 1);
		
		$normalEventsCriteria = \GO\Base\Db\FindCriteria::newInstance()
					->addModel(Event::model())					
					->addCondition('end_time', $periodStartTime, '>');
		
		if($periodEndTime)
			$normalEventsCriteria->addCondition('start_time', $periodEndTime, '<');
		
		$recurringEventsCriteria = \GO\Base\Db\FindCriteria::newInstance()
					->addModel(Event::model())
					->addCondition('rrule', "", '!=')
					->mergeWith(
									\GO\Base\Db\FindCriteria::newInstance()
										->addModel(Event::model())					
//										->addCondition('repeat_end_time', $periodStartTime, '>=')
										->addRawCondition('`t`.`repeat_end_time`', '('.intval($periodStartTime).'-(`t`.`end_time`-`t`.`start_time`))', '>=', true)
										->addCondition('repeat_end_time', 0,'=','t',false))
					->addCondition('start_time', $periodStartTime, '<');
		
		$normalEventsCriteria->mergeWith($recurringEventsCriteria, false);
		
		$findParams->getCriteria()->mergeWith($normalEventsCriteria);

		

		return $this->find($findParams);
	}

	private function _calculateRecurrences($event, $periodStartTime, $periodEndTime) {
		
		$origPeriodStartTime=$periodStartTime;
		$origPeriodEndTime=$periodEndTime;
		
		//recurrences can only be calculated correctly if we use the start of the day and the end of the day.
		//we'll use the original times later to check if they really overlap.
		$periodStartTime= \GO\Base\Util\Date::clear_time($periodStartTime)-1;
		$periodEndTime= \GO\Base\Util\Date::clear_time(\GO\Base\Util\Date::date_add($periodEndTime,1));

		$localEvent = new LocalEvent($event, $origPeriodStartTime, $origPeriodEndTime);
		
		if(!$localEvent->isRepeating()){
			$this->_calculatedEvents[$event->start_time.'-'.$event->name.'-'.$event->id] = $localEvent;
		} else {
			
			//$rrule = new \GO\Base\Util\Icalendar\Rrule();
			try{
				$startDateTime = new \DateTime('@'.$localEvent->getEvent()->start_time, new \DateTimeZone($localEvent->getEvent()->timezone));
				$startDateTime->setTimezone(new \DateTimeZone($localEvent->getEvent()->timezone)); //iterate in local timezone for DST issues
				$rrule = new \GO\Base\Util\Icalendar\RRuleIterator($localEvent->getEvent()->rrule, $startDateTime);
				$rrule->fastForward(new \DateTime('@'.\GO\Base\Util\Date::date_add($periodStartTime,-ceil(( ($event->end_time-$event->start_time) /86400)))));
			}catch(\Exception $e) {
				
				GO::debug($e->getMessage()." Event ID:".$event->id);
					
//					trigger_error($e->getMessage()." Event ID:".$event->id." Cleared rrule!");
				return;
			}
			$origEventAttr = $localEvent->getEvent()->getAttributes('formatted');
			while ($occurenceStartTime = $rrule->nextRecurrence($periodEndTime)) {
				
//				var_dump(\GO\Base\Util\Date::get_timestamp($occurenceStartTime));
				
				if ($occurenceStartTime > $localEvent->getPeriodEndTime())
					break;

				$localEvent->setAlternateStartTime($occurenceStartTime);

				$diff = $event->getDiff();

				$endTime = new \GO\Base\Util\Date\DateTime('@'.$occurenceStartTime);
				$endTime->setTimezone(new \DateTimeZone($localEvent->getEvent()->timezone));
				$endTime->add($diff);
				
				$localEvent->setAlternateEndTime($endTime->format('U'));

				if($localEvent->getAlternateStartTime()<$origPeriodEndTime && $localEvent->getAlternateEndTime()>$origPeriodStartTime){
					if(!$event->hasException($occurenceStartTime))
						$this->_calculatedEvents[$occurenceStartTime.'-'.$origEventAttr['name'].'-'.$origEventAttr['id']] = $localEvent;
				}
				
				$localEvent = new LocalEvent($event, $periodStartTime, $periodEndTime);
			}
		}

	}
	
	/**
	 * Check if this event has an exception for a given day.
	 * 
	 * @param int $time
	 * @return Exception
	 */
	public function hasException($time){
		$startDay = \GO\Base\Util\Date::clear_time($time);
		$endDay = \GO\Base\Util\Date::date_add($startDay, 1);

		$findParams = \GO\Base\Db\FindParams::newInstance();
		$findParams->getCriteria()
						->addCondition('event_id', $this->id)
						->addCondition('time', $startDay,'>=')
						->addCondition('time', $endDay, '<');

		return Exception::model()->findSingle($findParams);

	}
	
	/**
	 * Create a localEvent model from this event model
	 * 
	 * @param Event $event
	 * @param StringHelper $periodStartTime
	 * @param StringHelper $periodEndTime
	 * @return LocalEvent 
	 */
	public function getLocalEvent($event, $periodStartTime, $periodEndTime){
		$localEvent = new LocalEvent($event, $periodStartTime, $periodEndTime);
		
		return $localEvent;
	}
	
	/**
	 * Find an event based on uuid field for a user. Either user_id or calendar_id
	 * must be supplied.
	 * 
	 * Optionally exceptionDate can be specified to find a specific exception.
	 * 
	 * @param StringHelper $uuid
	 * @param int $user_id
	 * @param int $calendar_id
	 * @param int $exceptionDate
	 * @return Event 
	 */
	public function findByUuid($uuid, $user_id, $calendar_id=0, $exceptionDate=false){
		
		$whereCriteria = \GO\Base\Db\FindCriteria::newInstance()												
										->addCondition('uuid', $uuid);

		$params = \GO\Base\Db\FindParams::newInstance()
						->ignoreAcl()
						->single();							
		
		if(!$calendar_id){
			$joinCriteria = \GO\Base\Db\FindCriteria::newInstance()
							->addCondition('calendar_id', 'c.id','=','t',true, true)
							->addCondition('user_id', $user_id,'=','c');
			
			$params->join(Calendar::model()->tableName(), $joinCriteria, 'c');
		}else
		{
			$whereCriteria->addCondition('calendar_id', $calendar_id);
		}
		
		if($exceptionDate){
			//must be an exception and start on the must start on the exceptionTime
			$exceptionJoinCriteria = \GO\Base\Db\FindCriteria::newInstance()
							->addCondition('id', 'e.exception_event_id','=','t',true,true);
			
			$params->join(Exception::model()->tableName(),$exceptionJoinCriteria,'e');
			
			$dayStart = \GO\Base\Util\Date::clear_time($exceptionDate);
			$dayEnd = \GO\Base\Util\Date::date_add($dayStart,1);	
			
			$dateCriteria = \GO\Base\Db\FindCriteria::newInstance() 
							->addCondition('time', $dayStart, '>=','e')
							->addCondition('time', $dayEnd, '<','e');
			
			$whereCriteria->mergeWith($dateCriteria);
			
//			//the code below only find exceptions on the same day which is wrong
//			$whereCriteria->addCondition('exception_for_event_id', 0,'>');
//			
//			$dayStart = \GO\Base\Util\Date::clear_time($exceptionDate);
//			$dayEnd = \GO\Base\Util\Date::date_add($dayStart,1);
//			
//			$dateCriteria = \GO\Base\Db\FindCriteria::newInstance()
//							->addCondition('start_time', $dayStart, '>=')
//							->addCondition('start_time', $dayEnd, '<','t',false);
//			
//			$whereCriteria->mergeWith($dateCriteria);
			
		}else
		{
			$whereCriteria->addCondition('exception_for_event_id', 0);
		}

		$params->criteria($whereCriteria);

		return $this->find($params);			
	}

//	/**
//	 * Find an event that belongs to a group of participant events. They all share the same uuid field.
//	 * 
//	 * @param int $calendar_id
//	 * @param string $uuid
//	 * @return Event 
//	 */
//	public function findParticipantEvent($calendar_id, $uuid) {
//		return $this->findSingleByAttributes(array('uuid' => $event->uuid, 'calendar_id' => $calendar->id));
//	}
	
	/**
	 * Find the resource booking that belongs to this event
	 * 
	 * @param int $event_id
	 * @param int $resource_calendar_id
	 * @return Event 
	 */
	public function findResourceForEvent($event_id, $resource_calendar_id){
		return $this->findSingleByAttributes(array('resource_event_id' => $event_id, 'calendar_id' => $resource_calendar_id));
	}
	
	/**
	 * Get the status translated into the current language setting
	 * @return StringHelper 
	 */
	public function getLocalizedStatus(){
		$statuses = \GO::t("statuses", "calendar");
		
		return isset($statuses[$this->status]) ? $statuses[$this->status] : $this->status;
						
	}

	/**
	 * Get the event in HTML markup
	 * 
	 * @todo Add recurrence info
	 * @return StringHelper 
	 */
	public function toHtml() {
		$html = '<table id="event-'.$this->uuid.'">' .
						'<tr><td>' . \GO::t("Subject", "calendar") . ':</td>' .
						'<td>' . $this->name . '</td></tr>';
		
		if($this->calendar){
			$html .= '<tr><td>' . \GO::t("Calendar", "calendar") . ':</td>' .
						'<td>' . $this->calendar->name . '</td></tr>';
		}
		
		$html .= '<tr><td>' . \GO::t("Starts at", "calendar") . ':</td>' .
						'<td>' . \GO\Base\Util\Date::get_timestamp($this->start_time, empty($this->all_day_event)) . '</td></tr>' .
						'<tr><td>' . \GO::t("Ends at", "calendar") . ':</td>' .
						'<td>' . \GO\Base\Util\Date::get_timestamp($this->end_time, empty($this->all_day_event)) . '</td></tr>';

		$html .= '<tr><td>' . \GO::t("Status", "calendar") . ':</td>' .
						'<td>' . $this->getLocalizedStatus() . '</td></tr>';


		if (!empty($this->location)) {
			$html .= '<tr><td style="vertical-align:top">' . \GO::t("Location", "calendar") . ':</td>' .
							'<td>' . \GO\Base\Util\StringHelper::text_to_html($this->location) . '</td></tr>';
		}
		
		if(!empty($this->description)){
			$html .= '<tr><td style="vertical-align:top">' . \GO::t("Description") . ':</td>' .
							'<td>' . \GO\Base\Util\StringHelper::text_to_html($this->description) . '</td></tr>';
		}
		
		if($this->isRecurring()){
			$html .= '<tr><td colspan="2">' .$this->getRecurrencePattern()->getAsText().'</td></tr>';;
		}

		//don't calculate timezone offset for all day events
//		$timezone_offset_string = \GO\Base\Util\Date::get_timezone_offset($this->start_time);
//
//		if ($timezone_offset_string > 0) {
//			$gmt_string = '(\G\M\T +' . $timezone_offset_string . ')';
//		} elseif ($timezone_offset_string < 0) {
//			$gmt_string = '(\G\M\T -' . $timezone_offset_string . ')';
//		} else {
//			$gmt_string = '(\G\M\T)';
//		}

		//$html .= '<tr><td colspan="2">&nbsp;</td></tr>';

		$cfRecord = $this->getCustomFields();
		
		if (!empty($cfRecord)) {
		$fieldsets = \go\core\model\FieldSet::find()->filter(['entities' => ['Event']]);
		
			foreach($fieldsets as $fieldset) {
				$html .= '<tr><td colspan="2"><b>'.($fieldset->name).'</td></tr>';

				$fields = \go\core\model\Field::find()->where(['fieldSetId' => $fieldset->id]);
				
				foreach($fields as $field) {
					
					if(empty($cfRecord[$field->databaseName])) {
						continue;
					}
					
					$html .= '<tr><td style="vertical-align:top">'.($field->name).'</td>'.
										'<td>'.$cfRecord[$field->databaseName].'</td></tr>';
				}				
			}
		}		
	
		$html .= '</table>';
		
		$stmt = $this->participants();
		
		if($stmt->rowCount()){
			
			$html .= '<table>';
			
			$html .= '<tr><td colspan="3"><br /></td></tr>';
			$html .= '<tr><td><b>'.\GO::t("Participant", "calendar").'</b></td><td><b>'.\GO::t("Status", "calendar").'</b></td><td><b>'.\GO::t("Organizer", "calendar").'</b></td></tr>';
			while($participant = $stmt->fetch()){
				$html .= '<tr><td>'.$participant->name.'&nbsp;</td><td>'.$participant->statusName.'&nbsp;</td><td>'.($participant->is_organizer ? \GO::t("Yes") : '').'</td></tr>';
			}
			$html .='</table>';
		}
		

		return $html;
	}
	
	/**
	 * Get the recurrence pattern object
	 * 
	 * @return \GO\Base\Util\Icalendar\Rrule
	 */
	public function getRecurrencePattern(){
		
		if(!$this->isRecurring())
			return false;

		$startDateTime = new \DateTime('@'.$this->start_time, new \DateTimeZone($this->timezone));
		$startDateTime->setTimezone(new \DateTimeZone($this->timezone)); //iterate in local timezone for DST issues
		$rRule = new \GO\Base\Util\Icalendar\RRuleIterator($this->rrule, $startDateTime);

		return $rRule;
	}
	
	
	/**
	 * Get this event as a VObject. This can be turned into a vcalendar file data.
	 * 
	 * @param StringHelper $method REQUEST, REPLY or CANCEL
	 * @param Participant $updateByParticipant The participant that is generating this ICS for a response.
	 * @param int $recurrenceTime Export for a specific recurrence time for the recurrence-id. 
	 * @param boolean $includeExdatesForMovedEvents Funambol need EXDATE lines even for appointments that have been moved. CalDAV doesn't need those lines.
	 * 
	 * If this event is an occurence and has a exception_for_event_id it will automatically determine this value. 
	 * This option is only useful for cancelling a single occurence. Because in that case there is no event model for the occurrence. There's just an exception.
	 * 
	 * @return Sabre\VObject\Component 
	 */
	public function toVObject($method='REQUEST', $updateByParticipant=false, $recurrenceTime=false,$includeExdatesForMovedEvents=false){
		
		$calendar = new Sabre\VObject\Component\VCalendar();
		 
		$e=$calendar->createComponent('VEVENT');
		
		if(empty($this->uuid)){
			$this->uuid = \GO\Base\Util\UUID::create('event', $this->id);
			$this->save(true);
		}
			
		$e->uid=$this->uuid;		
		
		if(isset($this->sequence))
			$e->sequence=$this->sequence;
		
		
		$mtimeDateTime = new \DateTime('@'.$this->mtime);
		$mtimeDateTime->setTimezone(new \DateTimeZone('UTC'));		
		$e->add('LAST-MODIFIED', $mtimeDateTime);
				
		$ctimeDateTime = new \DateTime('@'.$this->mtime);
		$ctimeDateTime->setTimezone(new \DateTimeZone('UTC'));
		$e->add('created', $ctimeDateTime);
	
    $e->summary = (string) $this->name;
		
		if($this->status == "NEEDS-ACTION"){
			$e->status = "TENTATIVE";
		}else{
			$e->status = $this->status;
		}	
		
		
		$dateType = $this->all_day_event ? "DATE" : "DATETIME";
		
//		if($this->all_day_event){
//			$e->{"X-FUNAMBOL-ALLDAY"}=1;
//		}
		
		if($this->exception_for_event_id>0){
			//this is an exception
			
			$exception = $this->recurringEventException(); //get master event from relation
			if($exception){
				$recurrenceTime=$exception->getStartTime();				
			}
		}
		if($recurrenceTime){
			$dt = \GO\Base\Util\Date\DateTime::fromUnixtime($recurrenceTime);			
			$rId = $e->add('recurrence-id', $dt);
			if($this->_exceptionEvent->all_day_event){
				$rId['VALUE']='DATE';
			}
		}
	
		
		$dtstart = $e->add('dtstart', \GO\Base\Util\Date\DateTime::fromUnixtime($this->start_time));
		if($this->all_day_event){
			$dtstart['VALUE'] = 'DATE';
		}
		
		if($this->all_day_event){
			$end_time = \GO\Base\Util\Date::clear_time($this->end_time);						
			$end_time = \GO\Base\Util\Date::date_add($end_time,1);			
		}else{
			$end_time = $this->end_time;
		}
		
		$dtend = $e->add('dtend', \GO\Base\Util\Date\DateTime::fromUnixtime($end_time));
		
		if($this->all_day_event){
			$dtend['VALUE'] = 'DATE';
		}

		if(!empty($this->description))
			$e->description=$this->description;
		
		if(!empty($this->location))
			$e->location=$this->location;

		$rrule = str_replace('RRULE:','',$this->rrule);
		if(!empty($rrule)){
			
//			$rRule = $this->getRecurrencePattern();
//			$rRule->shiftDays(false);
			$e->add('rrule',$rrule);
			
			$findParams = \GO\Base\Db\FindParams::newInstance();
			
			if(!$includeExdatesForMovedEvents)
				$findParams->getCriteria()->addCondition('exception_event_id', 0);
			
			$stmt = $this->exceptions($findParams);
			while($exception = $stmt->fetch()){
				$dt = \GO\Base\Util\Date\DateTime::fromUnixtime($exception->getStartTime());	
				$exdate = $e->add('exdate',$dt);
				if($this->all_day_event){
					$exdate['VALUE'] = 'DATE';
				}
				
			}
		}
		
		
		$stmt = $this->participants();
		while($participant=$stmt->fetch()){
			
			if($participant->is_organizer || $method=='REQUEST' || ($updateByParticipant && $updateByParticipant->id==$participant->id)){
				//If this is a meeting REQUEST then we must send all participants.
				//For a CANCEL or REPLY we must send the organizer and the current user.
				$e->add($participant->is_organizer ? 'organizer' : 'attendee', 'mailto:'.$participant->email, array(
						'cn'=>$participant->name,
						'rsvp'=>'true',
						'partstat'=>$this->_exportVObjectStatus($participant->status)
				));
			}
		}
		
		if($this->category){
			$e->categories=$this->category->name;
		}
		
		
				
		
		if($this->reminder !== null){
			
			$a=$calendar->createComponent('VALARM');
//			BEGIN:VALARM
//ACTION:DISPLAY
//TRIGGER;VALUE=DURATION:-PT5M
//DESCRIPTION:Default Mozilla Description
//END:VALARM
			
			$a->action='DISPLAY';
			
			if(empty($this->reminder)){
				$a->add('trigger','P0D', array('value'=>'DURATION'));
			} else {
				$a->add('trigger','-PT'.($this->reminder/60).'M', array('value'=>'DURATION'));
			}
			
			$a->description="Alarm";			
		
						
			//for funambol compatibility, the \GO\Base\VObject\Reader class use this to convert it to a vcalendar 1.0 aalarm tag.
			$e->{"X-GO-REMINDER-TIME"}=date('Ymd\THis', $this->start_time-$this->reminder);
			$e->add($a);
		}
		
		
		if($this->private) {
			$e->class='PRIVATE';
		}
		
		
		return $e;
	}
	


	/**
	 * Get vcalendar data for an *.ics file.
	 * 
	 * @param StringHelper $method REQUEST, REPLY or CANCEL
	 * @param Participant $updateByParticipant The participant that is generating this ICS for a response.
	 * @param int $recurrenceTime Export for a specific recurrence time for the recurrence-id. 
	 * If this event is an occurence and has a exception_for_event_id it will automatically determine this value. 
	 * This option is only useful for cancelling a single occurence. Because in that case there is no event model for the occurrence. There's just an exception.
	 * 
	 * Set this to a unix timestamp of the start of an occurence if it's an update
	 * for a particular recurrence date.
	 * 
	 * @return type 
	 */
	
	public function toICS($method='REQUEST', $updateByParticipant=false, $recurrenceTime=false) {		
		
		$c = new \GO\Base\VObject\VCalendar();		
		$c->method=$method;
		
		$c->add(new \GO\Base\VObject\VTimezone());
		
		$c->add($this->toVObject($method, $updateByParticipant, $recurrenceTime));		
		return $c->serialize();		
	}
	
	public function toVCS(){
		$c = new \GO\Base\VObject\VCalendar();		
		$vobject = $this->toVObject('',false,false,true);
		$c->add($vobject);		
		
		\GO\Base\VObject\Reader::convertICalendarToVCalendar($c);
		
		return $c->serialize();		
	}
	
	/**
	 * Check if this event is a resource booking;
	 * 
	 * @return boolean
	 */
	public function isResource(){
		return $this->calendar && $this->calendar->group_id>1;
	}
	
	
	public $importedParticiants=array();
	
	
	private function _utcToLocal($date){
		//DateTime from SabreDav is date without time in UTC timezone. We store it in the users timezone so we must
		//add the timezone offset.
		$timezone = new DateTimeZone(\GO::user()->timezone);

		$offset = $timezone->getOffset($date);		
		
\GO::debug("Offset: ".$offset);

$sub = $offset>0;
		if(!$sub)
			$offset *= -1;

		$interval = new DateInterval('PT'.$offset.'S');	
		if(!$sub){
			$date->add($interval);
		}else{
			$date->sub($interval);		

		}
	}
	
	
	/**
	 * Import an event from a VObject 
	 * 
	 * @param Sabre\VObject\Component $vobject
	 * @param array $attributes Extra attributes to apply to the event. Raw values should be past. No input formatting is applied.
	 * @param boolean $dontSave. Don't save the event. WARNING. Event can't be fully imported this way because participants and exceptions need an ID. This option is useful if you want to display info about an ICS file.
	 * @param boolean $importExternal This should be switched on if importing happens from external ICS calendar.
	 * @return Event 
	 */
	public function importVObject(Sabre\VObject\Component $vobject, $attributes=array(), $dontSave=false, $makeSureUserParticipantExists=false, $importExternal=false, $withCategories = true){

		$uid = (string) $vobject->uid;
		if(!empty($uid))
			$this->uuid = $uid;
		
		$this->name = (string) $vobject->summary;
		if(empty($this->name))
			$this->name = \GO::t("Unnamed");
		
		$dtstart = $vobject->dtstart ? $vobject->dtstart->getDateTime() : new \DateTime();
		$dtend = $vobject->dtend ? $vobject->dtend->getDateTime() : new \DateTime();

		//turn DateTimeImmutable into DateTime
		$dtstart = new \DateTime($dtstart->format('Y-m-d H:i'), $dtstart->getTimezone());
		$dtend = new \DateTime($dtend->format('Y-m-d H:i'), $dtend->getTimezone());
		
		$this->all_day_event = isset($vobject->dtstart['VALUE']) && $vobject->dtstart['VALUE']=='DATE' ? 1 : 0;

		//ios sends start and end date at 00:00 hour
		//DTEND;TZID=Europe/Amsterdam:20140121T000000
		//DTSTART;TZID=Europe/Amsterdam:20140120T000000

		if($dtstart->format('Hi') == "0000" && $dtend->format('Hi') == "0000" ){
			$this->all_day_event=true;
		}
		
		if($this->all_day_event){
// This broke all day events in thunderbird. It messed up the times.
//			if($dtstart->getTimezone()->getName()=='UTC' || $dtend->getTimezone()->getName()=='UTC'){
//				$this->timezone = 'UTC';
//			}

			$this->start_time = \GO\Base\Util\Date::clear_time($dtstart->format('U'));	
			$this->end_time = \GO\Base\Util\Date::clear_time($dtend->format('U')) - 60;

		}else
		{
			$this->start_time =intval($dtstart->format('U'));	
			$this->end_time = intval($dtend->format('U'));
		}
		
		
		if($vobject->duration){
			$duration = \GO\Base\VObject\Reader::parseDuration($vobject->duration);
			$this->end_time = $this->start_time+$duration;
		}
		if($this->end_time<=$this->start_time)
			$this->end_time=$this->start_time+3600;
				
		
		if($vobject->description)
			$this->description = (string) $vobject->description;
		
		
		if((string) $vobject->rrule != ""){
			// Use the RRULe as provided by the iCalendar object
			// When using RRuleIterator that implements all rules
			$this->rrule = (string) $vobject->rrule;
			$this->repeat_end_time = !empty($vobject->rrule->until) ? $vobject->rrule->until : null;
		}else
		{
			$this->rrule="";
			$this->repeat_end_time = 0;
		}
			
		if($vobject->{"last-modified"})
			$this->mtime=intval($vobject->{"last-modified"}->getDateTime()->format('U'));
		
		if($vobject->location)
			$this->location=(string) $vobject->location;
		
		//var_dump($vobject->status);
		if($vobject->status){
			$status = (string) $vobject->status;
			if($this->isValidStatus($status))
				$this->status=$status;			
		}
		
		if(isset($vobject->class)){
			$this->private = strtoupper($vobject->class)!='PUBLIC';
		}
		
		$this->reminder=null;
		
//		if($vobject->valarm && $vobject->valarm->trigger){
//			
//			$type = (string) $vobject->valarm->trigger["value"];
//			
//			
//			if($type == "DURATION") {
//				$duration = \GO\Base\VObject\Reader::parseDuration($vobject->valarm->trigger);
//				if($duration>0){
//					$this->reminder = $duration*-1;
//				}
//			}else
//			{
//				\GO::debug("WARNING: Ignoring unsupported reminder value of type: ".$type);			
//			}
//	
		// if($vobject->valarm && $vobject->valarm->trigger) {
		// 	$date = false;
		// 	try {
		// 		$date = $vobject->valarm->getEffectiveTriggerTime();
		// 	}
		// 	catch(\Exception $e) {
		// 		//invalid trigger.
		// 	}
		// 	if($date) {
		// 		if($this->all_day_event)
		// 			$this->_utcToLocal($date);
		// 		$this->reminder = $this->start_time-$date->format('U');
		// 	}
		// }elseif($vobject->aalarm){ //funambol sends old vcalendar 1.0 format
		// 	$aalarm = explode(';', (string) $vobject->aalarm);
		// 	if(!empty($aalarm[0])) {				
		// 		$p = Sabre\VObject\DateTimeParser::parse($aalarm[0]);
		// 		$this->reminder = $this->start_time-$p->format('U');
		// 	}		
		// }
		
		$this->setAttributes($attributes, false);
		
		$recurrenceIds = $vobject->select('recurrence-id');
		if(count($recurrenceIds)){
			
			//this is a single instance of a recurring series.
			//attempt to find the exception of the recurring series event by uuid
			//and recurrence time so we can set the relation cal_exceptions.exception_event_id=cal_events.id
			
			$firstMatch = array_shift($recurrenceIds);
			$recurrenceTime=$firstMatch->getDateTime()->format('U');
			
			$whereCriteria = \GO\Base\Db\FindCriteria::newInstance()
							->addCondition('calendar_id', $this->calendar_id,'=','ev')
							->addCondition('uuid', $this->uuid,'=','ev')
							->addCondition('time', $recurrenceTime,'=','t');
			
			$joinCriteria = \GO\Base\Db\FindCriteria::newInstance()
							->addCondition('event_id', 'ev.id','=','t',true, true);
			
			
			$findParams = \GO\Base\Db\FindParams::newInstance()
							->single()
							->criteria($whereCriteria)
							->join(Event::model()->tableName(),$joinCriteria,'ev');
			
			$exception = Exception::model()->find($findParams);
			if($exception){
				
				
				$this->exception_for_event_id=$exception->event_id;
				if (empty($this->name) || $this->name==\GO::t("Unnamed"))
					$this->name = $exception->mainevent->name;
			}else
			{				
				
				
				//exception was not found for this recurrence. Find the recurring series and add the exception.
				$recurringEvent = Event::model()->findByUuid($this->uuid, 0, $this->calendar_id);
				if($recurringEvent){
					
					\GO::debug("Creating MISSING exception for ".date('c', $recurrenceTime));
					//aftersave will create Exception
					$this->exception_for_event_id=$recurringEvent->id;
					
					//will be saved later
					$exception = new Exception();
					$exception->time=$recurrenceTime;
					$exception->event_id=$recurringEvent->id;
					if (empty($this->name) || $this->name==\GO::t("Unnamed"))
						$this->name = $exception->mainevent->name;
				}else
				{
					//ignore this because the invited participant might not be invited to the series
					//throw new \Exception("Could not find master event!");
					
					//hack to make it be seen as an exception
					$this->exception_for_event_id = -1;
				}
			}
		}
		
		if($vobject->valarm && $vobject->valarm->trigger){
			
			$reminderTime = false;
			try {
				$reminderTime = $vobject->valarm->getEffectiveTriggerTime();
			}
			catch(\Exception $e) {
				//invalid trigger.
			}

			if($reminderTime) {
				//echo $reminderTime->format('c');
				if($this->all_day_event)
					$this->_utcToLocal($reminderTime);
				$seconds = $reminderTime->format('U');
				$this->reminder = $this->start_time-$seconds;
				if($this->reminder<0)
					$this->reminder=0;

			}
		}elseif($vobject->aalarm){ //funambol sends old vcalendar 1.0 format
			$aalarm = explode(';', (string) $vobject->aalarm);
			if(!empty($aalarm[0])) {				
				$p = Sabre\VObject\DateTimeParser::parse($aalarm[0]);
				$this->reminder = $this->start_time-$p->format('U');
			}		
		}
		
		if($withCategories) {
			$cats = (string) $vobject->categories;
			if(!empty($cats)){
				//Group-Office only supports a single category.
				$cats = explode(',',$cats);
				$categoryName = array_shift($cats);

				$category = Category::model()->findByName($this->calendar_id, $categoryName);
				if(!$category && !$dontSave && $this->calendar_id){
					$category = new Category();
					$category->name=$categoryName;
					$category->calendar_id=$this->calendar_id;
					$category->save(true);
				}			

				if($category){
					$this->category_id=$category->id;			
					$this->background=$category->color;
				}
			}
		}
		
		//set is_organizer flag
		if($vobject->organizer && $this->calendar){
			$organizerEmail = str_replace('mailto:','', strtolower((string) $vobject->organizer));
			$this->is_organizer=$organizerEmail == $this->calendar->user->email;
		}		
		
		
		if(!$dontSave){
			$this->cutAttributeLengths();
//			try {
				$this->_isImport=true;
				
				if (!$importExternal)
					$this->setValidationRule('uuid', 'unique', array('calendar_id','start_time', 'exception_for_event_id'));
				
					
				if(!$this->save()){	

					if ($importExternal) {
						$installationName = !empty(\GO::config()->title) ? \GO::config()->title : 'Group-Office';
						$validationErrStr = implode("\n", $this->getValidationErrors())."\n";

						$mailSubject = str_replace(array('%cal','%event'),array($this->calendar->name,$this->name),\GO::t("Event not saved in %event calendar \"%cal\"", "calendar"));
						$body = \GO::t("This message is from your %goname calendar. %goname attempted to import an event called \"%event\" with start time %starttime from an external calendar into calendar \"%cal\", but that could not be done because the event contained errors. The event may still be in the external calendar.

The following is the error message:
%errormessage", "calendar");
						$body = str_replace(
											array('%goname','%event','%starttime','%cal','%errormessage'),
											array(
												$installationName,
												$this->name,
												\GO\Base\Util\Date::get_timestamp($this->start_time),
												$this->calendar->name,
												$validationErrStr
											),
											$body
										);
						$message = \GO\Base\Mail\Message::newInstance(
														$mailSubject
														)->setFrom(\GO::config()->webmaster_email, \GO::config()->title)
														->addTo($this->calendar->user->email);

						$message->setHtmlAlternateBody(nl2br($body));

						if (\GO\Base\Mail\Mailer::newGoInstance()->send($message))
							throw new \GO\Base\Exception\Validation('DUE TO ERROR, CRON SENT MAIL TO: '.$this->calendar->user->email.'. THIS IS THE EMAIL MESSAGE:'."\r\n".$body);
						else
							throw new \GO\Base\Exception\Validation('CRON COULD NOT SEND EMAIL WITH ERROR MESSAGE TO: '.$this->calendar->user->email.'. THIS IS THE EMAIL MESSAGE:'."\r\n".$body);
					} else {
						throw new \GO\Base\Exception\Validation(implode("\n", $this->getValidationErrors())."\n");
					}
					
				}
				$this->_isImport=false;
//			} catch (\Exception $e) {
//				throw new \Exception($this->name.' ['.\GO\Base\Util\Date::get_timestamp($this->start_time).' - '.\GO\Base\Util\Date::get_timestamp($this->end_time).'] '.$e->getMessage());
//			}
			
			if(!empty($exception)){			
				//save the exception we found by recurrence-id
				$exception->exception_event_id=$this->id;
				$exception->save();
				
				\GO::debug("saved exception");
			}		
			


			if($vobject->organizer)
				$p = $this->importVObjectAttendee($this, $vobject->organizer, true);
			else
				$p=false;

			$calendarParticipantFound=!empty($p) && $p->user_id==$this->calendar->user_id;
			
			$attendees = $vobject->select('attendee');
			foreach($attendees as $attendee){
				$p = $this->importVObjectAttendee($this, $attendee, false);
				
				if($p->user_id==$this->calendar->user_id){
					$calendarParticipantFound=true;
				}
			}
			
			//if the calendar owner is not in the participants then we should chnage the is_organizer flag because otherwise the event can't be opened or accepted.
			if(!$calendarParticipantFound){
				
				if($makeSureUserParticipantExists){
					$participant = \GO\Calendar\Model\Participant::model()->findSingleByAttributes(array('event_id'=>$this->id,'email'=>$this->calendar->user->email));
					
					if(!$participant){
						//this is a bad situation. The import thould have detected a user for one of the participants.
						//It uses the E-mail account aliases to determine a user. See GO_Calendar_Model_Event::importVObject
						$participant = new \GO\Calendar\Model\Participant();
						$participant->event_id=$this->id;
						$participant->user_id=$this->calendar->user_id;
						$participant->email=$this->calendar->user->email;	
					} else {
						$participant->user_id=$this->calendar->user_id;
					}
					
					$participant->save();
				}else
				{
					$this->is_organizer=true;
					$this->save();
				}
			}
			
			//Add exception dates to Event
			foreach($vobject->select('EXDATE') as $i => $exdate) {
				try {
					$dts = $exdate->getDateTimes();
					if($dts === null) {
						continue;
					}
					
					//TODO this change must be done for every participant event
					
					foreach($dts as $dt) {					
						$events = $this->getRelatedParticipantEvents(true);
						foreach($events as $event) {
							$event->addException($dt->format('U'));
						}
					}
				} catch (Exception $e) {
					trigger_error($e->getMessage(),E_USER_NOTICE);
				}
			}

			if($importExternal && $this->isRecurring()){
				$exceptionEventsStmt = Event::model()->find(
					\GO\Base\Db\FindParams::newInstance()->criteria(
						\GO\Base\Db\FindCriteria::newInstance()
							->addCondition('calendar_id',$this->calendar_id)
							->addCondition('uuid',$this->uuid)
							->addCondition('rrule','','=')
					)
				);
				foreach ($exceptionEventsStmt as $exceptionEventModel) {
					$exceptionEventModel->exception_for_event_id=$this->id;
					
					$exceptionEventModel->save();
					//TODO: This method only works when an exception takes place on the same day as the original occurence.
					//We should store the RECURRENCE-ID value so we can find it later.
					$this->addException($exceptionEventModel->start_time, $exceptionEventModel->id);
					
					
//					\GO::debug('=== EXCEPTION EVENT === ['.\GO\Base\Util\Date::get_timestamp($exceptionEventModel->start_time).'] '.$exceptionEventModel->name.' (\Exception for event: '.$exceptionEventModel->exception_for_event_id.')');
				}
			}
		}
				
		

		return $this;
	}	
	
	
	public static function eventIsFromCurrentImport(Event $eventModel, $importedEventsArray) {

		if (!empty($importedEventsArray))
			foreach ($importedEventsArray as $importedEventRecord) {
				if ($importedEventRecord['uuid']==$eventModel->uuid && $importedEventRecord['start_time']==$eventModel->start_time)
					return true;
			}
		
		return false;
		
	}
	
	
	/**
	 * Will import an attendee from a VObject to a given event. If the attendee
	 * already exists it will update it.
	 * 
	 * @param Event $event
	 * @param Sabre\VObject\Property $vattendee
	 * @param boolean $isOrganizer
	 * @return Participant 
	 */
	public function importVObjectAttendee(Event $event, Sabre\VObject\Property $vattendee, $isOrganizer=false){
			
		$attributes = $this->_vobjectAttendeeToParticipantAttributes($vattendee);
		$attributes['is_organizer']=$isOrganizer;
		
		if($isOrganizer)
			$attributes['status']= Participant::STATUS_ACCEPTED;
	
		$p= Participant::model()
						->findSingleByAttributes(array('event_id'=>$event->id, 'email'=>$attributes['email']));
		if(!$p){
			$p = new Participant();
			$p->is_organizer=$isOrganizer;		
			$p->event_id=$event->id;			
			if(\GO::modules()->email){
				$account = \GO\Email\Model\Account::model()->findByEmail($attributes['email']);
				if($account)
					$p->user_id=$account->user_id;
			}
			
			if(!$p->user_id){
				$user = \GO\Base\Model\User::model()->findSingleByAttribute('email', $attributes['email']);
				if($user)
					$p->user_id=$user->id;
			}		
			
			$p->setAttributes($attributes);
		}else
		{
			//the organizer might be added as a participant too. We don't want to 
			//import that a second time but we shouldn't update the is_organizer flag if
			//we found an existing participant.
			//unset($attributes['is_organizer']);			
			$p->status = $attributes['status'];			
		}

		
		$p->save();
		
		return $p;
	}
	
	private function _vobjectAttendeeToParticipantAttributes(Sabre\VObject\Property $vattendee){
		return array(
				'name'=>(string) $vattendee['CN'],
				'email'=>str_replace('mailto:','', strtolower((string) $vattendee)),
				'status'=>$this->_importVObjectStatus((string) $vattendee['PARTSTAT']),
				'role'=>(string) $vattendee['ROLE']
		);
	}
	
	private function _importVObjectStatus($status)
	{
		$statuses = array(
			'NEEDS-ACTION' => Participant::STATUS_PENDING,
			'ACCEPTED' => Participant::STATUS_ACCEPTED,
			'DECLINED' => Participant::STATUS_DECLINED,
			'TENTATIVE' => Participant::STATUS_TENTATIVE
		);

		return isset($statuses[$status]) ? $statuses[$status] : Participant::STATUS_PENDING;
	}
	private function _exportVObjectStatus($status)
	{
		$statuses = array(
			Participant::STATUS_PENDING=>'NEEDS-ACTION',	
			Participant::STATUS_ACCEPTED=>'ACCEPTED',
			Participant::STATUS_DECLINED=>'DECLINED',
			Participant::STATUS_TENTATIVE=>'TENTATIVE'
		);

		return isset($statuses[$status]) ? $statuses[$status] : 'NEEDS-ACTION';
	}
	
	protected function afterDuplicate(&$duplicate) {
		
		if (!$duplicate->isNew) {
			
			$stmt = $duplicate->participants;
			
			if (!$stmt->rowCount())
				$this->duplicateRelation('participants', $duplicate);

			if($duplicate->isRecurring() && $this->isRecurring())
				$this->duplicateRelation('exceptions', $duplicate);
			
			if($duplicate->is_organizer) {
				$this->duplicateRelation('resources', $duplicate, array('status'=>self::STATUS_NEEDS_ACTION));
			}
		}
		
		return parent::afterDuplicate($duplicate);
	}
	
	/**
	 * Add a participant to this calendar
	 * 
	 * This function sets the event_id for the participant and saves it.
	 * 
	 * @param Participant $participant
	 * @return bool Save of participant is successfull
	 */
	public function addParticipant($participant){
		$participant->event_id = $this->id;
		return $participant->save();
	}
	
	/**
	 * 
	 * @param Participant $participant
	 * @return Event 
	 */
	public function createCopyForParticipant(Participant $participant){
//		$calendar = Calendar::model()->getDefault($user);
//		
//		return $this->duplicate(array(
//			'user_id'=>$user->id,
//			'calendar_id'=>$calendar->id,
//			'is_organizer'=>false
//		));
		
		\GO::debug("Creating event copy for ".$participant->name);
		
		//create event in participant's default calendar if the current user has the permission to do that
		$calendar = $participant->getDefaultCalendar();
		if ($calendar && $calendar->userHasCreatePermission()){
			
			//find if an event for this exception already exists.
			$exceptionDate = $this->exception_for_event_id!=0 ? $this->start_time : false;			
			$existing = Event::model()->findByUuid($this->uuid, 0, $calendar->id, $exceptionDate);
			
			if(!$existing){				
			
				//ignore acl permissions because we allow users to schedule events directly when they have access through
				//the special freebusypermissions module.			
				$participantEvent = $this->duplicate(array(
						'calendar_id' => $calendar->id,
						'user_id'=>$participant->user_id,
						'is_organizer'=>false, 
	//					'status'=>  Event::STATUS_NEEDS_ACTION
						),
								true,true);			
				return $participantEvent;
			}else
			{
				\GO::debug("Found existing event: ".$existing->id.' - '.$existing->getAttribute('start_time', 'formatted'));
				
					
				//correct errors that somehow occurred.
				$attributes = $this->getAttributeSelection(array('name','start_time','end_time','rrule','repeat_end_time','location','description','private'), 'raw');
				$existing->setAttributes($attributes, false);
				if($existing->isModified()){
					$existing->updatingRelatedEvent=true;
					$existing->save(true);
				}
				
				return $existing;
			}
			
		}
		return false;
				
	}
	
	/**
	 * Get the default participant model for a new event.
	 * The default is the calendar owner except if the owner is admin. In that
	 * case it will default to the logged in user.
	 * 
	 * @return \Participant
	 */
	public function getDefaultOrganizerParticipant(){
		$calendar = $this->calendar;
		
		$user = $calendar->user_id==1 || !$calendar->user ? \GO::user() : $calendar->user;
		
		$participant = new Participant();
		$participant->event_id=$this->id;
		$participant->user_id=$user->id;		
		
		$participant->name=$user->name;
		$participant->email=$user->email;
		$participant->status=Participant::STATUS_ACCEPTED;
		$participant->is_organizer=1;
		
		return $participant;
	}
	
	/**
	 * Get's the organizer's event if this event belongs to a meeting.
	 * 
	 * @return Event
	 */
	public function getOrganizerEvent(){
		if($this->is_organizer)
			return false;
		
		return Event::model()->findSingleByAttributes(array('uuid'=>$this->uuid, 'is_organizer'=>1));
	}
	
	/**
	 * Check if this event has other participant then the given user id.
	 * 
	 * @param int|array $user_id
	 * @return boolean 
	 */
	public function hasOtherParticipants($user_id=0){
		
		if(empty($user_id))
			$user_id=array($this->calendar->user_id,\GO::user()->id);
		elseif(!is_array($user_id))
			$user_id = array($user_id);
		
		if(empty($this->id))
			return false;
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->single();
		
		$findParams->getCriteria()
						->addInCondition('user_id', $user_id,'t', true, true)
						->addCondition('event_id', $this->id);
						
		
		$p = Participant::model()->find($findParams);
		
		return $p ? true : false;
	}
	
	/**
	 * When checking all Event models make sure there is a UUID if not create one
	 */
	public function checkDatabase() {

	  if(empty($this->uuid))
		$this->uuid = \GO\Base\Util\UUID::create('event', $this->id);
	  
		//in some cases on old databases the repeat_end_time is set but the UNTIL property in the rrule is not. We correct that here.
		if($this->repeat_end_time>0 && strpos($this->rrule,'UNTIL=')===false){
			$rrule = new \GO\Base\Util\Icalendar\Rrule();
			$rrule->readIcalendarRruleString($this->start_time, $this->rrule);						
			$rrule->until=$this->repeat_end_time;
			$this->rrule= $rrule->createRrule();	
		}
		
		parent::checkDatabase();
	}
	
	
	/**
	 * Get the organizer model of this event
	 * 
	 * @return Participant
	 */
	public function getOrganizer(){
		return Participant::model()->findSingleByAttributes(array(
				'is_organizer'=>true,
				'event_id'=>$this->id
		));
	}
	
	
	/**
	 * Get the participant model where the user matches the calendar user
	 * 
	 * @return Participant
	 */
	public function getParticipantOfCalendar(){
		return Participant::model()->findSingleByAttributes(array(
				'user_id'=>$this->calendar->user_id,
				'event_id'=>$this->id
		));
	}
	
	/**
	 * Returns all participant models for this event and all the related events for a meeting.
	 * 
	 * @return Participant
	 */
	public function getParticipantsForUser(){
		//update all participants with this user and event uuid in the system		
		$findParams = \GO\Base\Db\FindParams::newInstance();
		
		$findParams->joinModel(array(
				'model'=>'GO\Calendar\Model\Event',
	 			'localTableAlias'=>'t', //defaults to "t"
	 			'localField'=>'event_id', //defaults to "id"	  
	 			'foreignField'=>'id', //defaults to primary key of the remote model
	 			'tableAlias'=>'e', //Optional table alias	  
	 			));
		
		$findParams->getCriteria()
						->addCondition('user_id', $this->user_id)
						->addCondition('uuid', $this->uuid,'=','e')  //recurring series and participants all share the same uuid
						->addCondition('start_time', $this->start_time,'=','e') //make sure start time matches for recurring series
						->addCondition("exception_for_event_id", 0, $this->exception_for_event_id==0 ? '=' : '!=','e');//the master event or a single occurrence can start at the same time. Therefore we must check if exception event has a value or is 0.
		
		return Participant::model()->find($findParams);			
		
	}
	
	
//	public function sendReply(){
//		if($this->is_organizer)
//			throw new \Exception("Meeting reply can only be send from the organizer's event");
//	}	
	
	/**
	 * Update's the participant status on all related meeting events and optionally sends a notification by e-mail to the organizer.
	 * This function has to be called on an event that belongs to the participant and not the organizer.
	 * 
	 * @param int $status Participant status, See Participant::STATUS_*
	 * @param boolean $sendMessage
	 * @param int $recurrenceTime Export for a specific recurrence time for the recurrence-id
	 * @throws Exception
	 */
	public function replyToOrganizer($recurrenceTime=false, $sendingParticipant=false, $includeIcs=true){
		
//		if($this->is_organizer)
//			throw new \Exception("Meeting reply can't be send from the organizer's event");
		

		//we need to pass the sending participant to the toIcs function. 
		//Only the organizer and current participant should be included
		if(!$sendingParticipant)
			$sendingParticipant = $this->getParticipantOfCalendar();
			

		if(!$sendingParticipant)
			throw new \Exception("Could not find your participant model");

		$organizer = $this->getOrganizer();
		if(!$organizer)
			throw new \Exception("Could not find organizer to send message to!");

		$updateReponses = \GO::t("updateReponses", "calendar");
		$subject= sprintf($updateReponses[$sendingParticipant->status], $sendingParticipant->name, $this->name);


		//create e-mail message
		$message = \GO\Base\Mail\Message::newInstance($subject)
							->setFrom($sendingParticipant->email, $sendingParticipant->name)
							->addTo($organizer->email, $organizer->name);

		$body = '<p>'.$subject.': </p>'.$this->toHtml();
		
		$url = \GO::createExternalUrl('calendar', 'openCalendar', array(
					'unixtime'=>$this->start_time
				));
		
		$body .= '<br /><a href="'.$url.'">'.\GO::t("Open calendar", "calendar").'</a>';

//		if(!$this->getOrganizerEvent()){
			//organizer is not a Group-Office user with event. We must send a message to him an ICS attachment
		if($includeIcs){
			$ics=$this->toICS("REPLY", $sendingParticipant, $recurrenceTime);				
			$a = new Swift_Attachment($ics, \GO\Base\Fs\File::stripInvalidChars($this->name) . '.ics', 'text/calendar; METHOD="REPLY"');
			$a->setEncoder(new Swift_Mime_ContentEncoder_PlainContentEncoder("8bit"));
			$a->setDisposition("inline");
			$message->attach($a);
			
			//for outlook 2003 compatibility
			$a2 = new Swift_Attachment($ics, 'invite.ics', 'application/ics');
			$a2->setEncoder(new Swift_Mime_ContentEncoder_PlainContentEncoder("8bit"));
			$message->attach($a2);
		}
//		}

		$message->setHtmlAlternateBody($body);

		\GO\Base\Mail\Mailer::newGoInstance()->send($message);

	}
	
	
	public function sendCancelNotice(){
//		if(!$this->is_organizer)
//			throw new \Exception("Meeting request can only be send from the organizer's event");
		
		$stmt = $this->participants;

		while ($participant = $stmt->fetch()) {		
			//don't invite organizer
			if($participant->is_organizer)
				continue;

			
			// Set the language of the email to the language of the participant.
			$language = false;
			if(!empty($participant->user_id)){
				$user = \GO\Base\Model\User::model()->findByPk($participant->user_id, false, true);
				
				if($user)
					\GO::language()->setLanguage($user->language);
			}

			$subject =  \GO::t("Cancellation", "calendar").': '.$this->name;

			//create e-mail message
			$message = \GO\Base\Mail\Message::newInstance($subject)
								->setFrom($this->user->email, $this->user->name)
								->addTo($participant->email, $participant->name);


			//check if we have a Group-Office event. If so, we can handle accepting and declining in Group-Office. Otherwise we'll use ICS calendar objects by mail
			$participantEvent = $participant->getParticipantEvent();

			$body = '<p>'.\GO::t("The following event has been cancelled by the organizer", "calendar").': </p>'.$this->toHtml();					
			
//			if(!$participantEvent){
				

				$ics=$this->toICS("CANCEL");				
				$a = new \Swift_Attachment($ics, \GO\Base\Fs\File::stripInvalidChars($this->name) . '.ics', 'text/calendar; METHOD="CANCEL"');
				$a->setEncoder(new Swift_Mime_ContentEncoder_PlainContentEncoder("8bit"));
				$a->setDisposition("inline");
				$message->attach($a);
				
				//for outlook 2003 compatibility
				$a2 = new \Swift_Attachment($ics, 'invite.ics', 'application/ics');
				$a2->setEncoder(new Swift_Mime_ContentEncoder_PlainContentEncoder("8bit"));
				$message->attach($a2);
				
//			}else{
			if($participantEvent){
				$url = \GO::createExternalUrl('calendar', 'openCalendar', array(
				'unixtime'=>$this->start_time
				));

				$body .= '<br /><a href="'.$url.'">'.\GO::t("Open calendar", "calendar").'</a>';
			}

			$message->setHtmlAlternateBody($body);

			// Set back the original language
			if($language !== false)
				\GO::language()->setLanguage($language);
			
			\GO\Base\Mail\Mailer::newGoInstance()->send($message);
		}

		return true;
		
	}


	/**
	 * Sends a meeting request to all participants. If the participant is not a Group-Office user
	 * or the organizer has no permissions to schedule an event it will include an
	 * icalendar attachment so the calendar software can schedule it.
	 * 
	 * @return boolean
	 * @throws Exception
	 */
	public function sendMeetingRequest($newParticipantsOnly=false, $update=false){		
		
		if(!$this->is_organizer)
			throw new \Exception("Meeting request can only be send from the organizer's event");
		
		$stmt = $this->participants;
		
		//handle missing user
		if(!$this->user){
			$this->user_id=1;
			$this->save(true);
		}

			while ($participant = $stmt->fetch()) {
				if (!$newParticipantsOnly || (isset(\GO::session()->values['new_participant_ids']) && in_array($participant->id,\GO::session()->values['new_participant_ids']))) {
					
					//don't invite organizer
					if($participant->is_organizer)
						continue;

					// Set the language of the email to the language of the participant.
					$language = false;
					if(!empty($participant->user_id)){
						$user = \GO\Base\Model\User::model()->findByPk($participant->user_id, false, true);

						if($user)
							\GO::language()->setLanguage($user->language);
					}

					//if participant status is pending then send a new inviation subject. Otherwise send it as update			
					if(!$update){
						$subject = \GO::t("Invitation", "calendar").': '.$this->name;
						$bodyLine = \GO::t("You are invited for the following event", "calendar");

					}else
					{
						$subject = \GO::t("Updated invitation", "calendar").': '.$this->name;
						$bodyLine = \GO::t("The following event has been updated by the organizer", "calendar");
					}				

					//create e-mail message
					$message = \GO\Base\Mail\Message::newInstance($subject)
										->setFrom($this->user->email, $this->user->name)
										->addTo($participant->email, $participant->name);


					//check if we have a Group-Office event. If so, we can handle accepting 
					//and declining in Group-Office. Otherwise we'll use ICS calendar objects by mail
					$participantEvent = $participant->getParticipantEvent();

					$body = '<p>'.$bodyLine.': </p>'.$this->toHtml();			


	//				if(!$participantEvent){					

					//build message for external program
					$acceptUrl = \GO::url("calendar/event/invitation",array("id"=>$this->id,'accept'=>1,'email'=>$participant->email,'participantToken'=>$participant->getSecurityToken()),false, false, false);
					$declineUrl = \GO::url("calendar/event/invitation",array("id"=>$this->id,'accept'=>0,'email'=>$participant->email,'participantToken'=>$participant->getSecurityToken()),false, false, false);

	//				if($participantEvent){	
						//hide confusing buttons if user has a GO event.
						$body .= '<div class="go-hidden">';
	//				}
					$body .= 

							'<p><br /><b>' . \GO::t("Only use the links below if your mail client does not support calendaring functions.", "calendar") . '</b></p>' .
							'<p>' . \GO::t("Do you accept this event?", "calendar") . '</p>' .
							'<a href="'.$acceptUrl.'">'.\GO::t("Accept", "calendar") . '</a>' .
							'&nbsp;|&nbsp;' .
							'<a href="'.$declineUrl.'">'.\GO::t("Decline", "calendar") . '</a>';

	//				if($participantEvent){	
						$body .= '</div>';
	//				}

					$ics=$this->toICS("REQUEST");				
					$a = new \Swift_Attachment($ics, \GO\Base\Fs\File::stripInvalidChars($this->name) . '.ics', 'text/calendar; METHOD="REQUEST"');
					$a->setEncoder(new Swift_Mime_ContentEncoder_PlainContentEncoder("8bit"));
					$a->setDisposition("inline");
					$message->attach($a);

					//for outlook 2003 compatibility
					$a2 = new \Swift_Attachment($ics, 'invite.ics', 'application/ics');
					$a2->setEncoder(new Swift_Mime_ContentEncoder_PlainContentEncoder("8bit"));
					$message->attach($a2);

					if($participantEvent){
						$url = \GO::createExternalUrl('calendar', 'openCalendar', array(
						'unixtime'=>$this->start_time
						));

						$body .= '<br /><a href="'.$url.'">'.\GO::t("Open calendar", "calendar").'</a>';
					}

					$message->setHtmlAlternateBody($body);

					// Set back the original language
					if($language !== false)
						\GO::language()->setLanguage($language);

					if(!\GO\Base\Mail\Mailer::newGoInstance()->send($message)) {
						throw new \Exception("Failed to send invite");
					}

					
				}
				
			}
			
			unset(\GO::session()->values['new_participant_ids']);
			
			return true;
	}
	
	public function resourceGetEventCalendarName() {
		
		if ($this->isResource()) {
			$resourcedEventModel = Event::model()->findByPk($this->resource_event_id, false , true);
			$calendarModel = $resourcedEventModel ? $resourcedEventModel->calendar : false;
			return !empty($calendarModel) ? $calendarModel->name : '';
		} else {
			return '';
		}
		
	}
	
}
