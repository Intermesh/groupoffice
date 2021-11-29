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
 * The Participant model
 *
 * @package GO.modules.Calendar
 * @version $Id: Participant.php 7607 2011-09-28 10:31:03Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 *
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property string $email
 * @property int $user_id
 * @property int $contact_id
 * @property int $status
 * @property string $last_modified
 * @property int $is_organizer
 * @property string $role
 * 
 * @property \GO\Calendar\Model\Event $event
 * @property string $statusName;
 * 
 * 
 */

namespace GO\Calendar\Model;

use go\core\model\Link;

class Participant extends \GO\Base\Db\ActiveRecord {

	const STATUS_TENTATIVE = "TENTATIVE";
	const STATUS_DECLINED = "DECLINED";
	const STATUS_ACCEPTED = "ACCEPTED";
	const STATUS_PENDING = "NEEDS-ACTION";
	
	public $notifyOrganizer=false;
	
	
	public $updateRelatedParticipants=true;
	
	public $dontCreateEvent = false;
	
	
	public $notifyRecurrenceTime=false;
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Participant
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function validate() {
		if (empty($this->name))
			$this->name = $this->email;

		return parent::validate();
	}

	/**
	 * Enable this function if you want this model to check the acl's automatically.
	 */
	// public function aclField(){
	//	 return 'acl_id';	
	// }

	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'cal_participants';
	}

	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 */
	public function relations() {
		return array(
				'event' => array('type' => self::BELONGS_TO, 'model' => 'GO\Calendar\Model\Event', 'field' => 'event_id')
		);
	}

	/**
	 * Check if the participant is available.
	 * 
	 * Returns a questionmark if the particiant is not a user.
	 * 
	 * @param int $start_time If empty then the related event will be used.
	 * @param int $end_time If empty then the related event will be used.
	 * 
	 * @return boolean/?
	 */
	public function isAvailable($start_time = false, $end_time = false) {
		if (!$this->hasFreeBusyAccess()) {
			return '?';
		} else {

			if (!$start_time && $this->event)
				$start_time = $this->event->start_time;

			if (!$end_time && $this->event)
				$end_time = $this->event->end_time;

			if ($start_time && $end_time)
				return self::userIsAvailable($start_time, $end_time, $this->user_id, $this->event);
			else
				return '?';
		}
	}

	/**
	 * Check if the current user has free busy access.
	 * 
	 * @return boolean
	 */
	public function hasFreeBusyAccess() {
		
		$permission=!empty($this->user_id);
		if($permission && \GO::modules()->isInstalled("freebusypermissions")){
			$permission = \GO\Freebusypermissions\FreebusypermissionsModule::hasFreebusyAccess(\GO::user()->id, $this->user_id);
		}
		return $permission;
	}

	/**
	 * Check if a user has events between two given times.
	 * 
	 * @param type $periodStartTime
	 * @param type $periodEndTime
	 * @param type $userId
	 * @param type $ignoreEvent
	 * @return boolean 
	 */
	public static function userIsAvailable($periodStartTime, $periodEndTime, $userId, $ignoreEvent = false) {

		$findParams = \GO\Base\Db\FindParams::newInstance()
						->ignoreAcl();

		$joinCriteria = \GO\Base\Db\FindCriteria::newInstance()
						->addRawCondition('t.calendar_id', 'c.id');

		$findParams->join(Calendar::model()->tableName(), $joinCriteria, 'c');

		$findParams->getCriteria()->addCondition('user_id', $userId, '=', 'c');

		if ($ignoreEvent) {
			$findParams->getCriteria()
							->addModel(Event::model())
							->addCondition('id', $ignoreEvent->id, '!=')
							->addCondition('uuid', $ignoreEvent->uuid, '!=')
			;
		}

		$events = Event::model()->findCalculatedForPeriod($findParams, $periodStartTime, $periodEndTime, true);

		foreach ($events as $event) {
			\GO::debug($event->getName());
		}

		return count($events) == 0;
	}
	
	/**
	 * Get free busy information in specified interval blocks of minutes
	 * 
	 * @param int $starttime
	 * @param int $endtime
	 * @param int $intervalMinutes
	 * @return array
	 */
	public function getFreeBusyInfo($starttime, $endtime, $intervalMinutes=15){
		

		$freebusy=array();
		$startTimeMin = $starttime/60;
		$endTimeMin = $endtime/60;
		
		for($i=$startTimeMin;$i<$endTimeMin;$i+=$intervalMinutes) {
			$freebusy[$i-$startTimeMin]=0;
		}
		
		if(empty($this->user_id))
			return $freebusy;

		$findParams = \GO\Base\Db\FindParams::newInstance()
						->ignoreAcl();

		$joinCriteria = \GO\Base\Db\FindCriteria::newInstance()
						->addRawCondition('t.calendar_id', 'c.id');

		$findParams->join(Calendar::model()->tableName(), $joinCriteria, 'c');

		$findParams->getCriteria()->addCondition('user_id', $this->user_id, '=', 'c');
		
		$events = Event::model()->findCalculatedForPeriod($findParams, $starttime, $endtime, true);



		foreach($events as $localEvent) {
			
//			echo $localEvent->getName()."\n";
	
			if($localEvent->getEvent()->id!=$this->event_id) {
				
				$eventEndTime=$localEvent->getAlternateEndTime();
				if($eventEndTime > $endtime) {
					$eventEndTime=$endtime-1;					
				}
				
				$eventStartTime=$localEvent->getAlternateStartTime();
				if($eventStartTime < $starttime) {
					$eventStartTime=$starttime;
				}

				$event_start = getdate($eventStartTime);
				$event_end = getdate($eventEndTime);
				
				$mod = $event_start['minutes'] % $intervalMinutes;
				if($mod>0)
					$event_start['minutes']+=(15-$mod);
				
				$mod = $event_end['minutes'] % $intervalMinutes;
				if($mod>0)
					$event_end['minutes']+=(15-$mod);

				
				$start_minutes = $event_start['minutes']+($event_start['hours']*60);
				$end_minutes = $event_end['minutes']+($event_end['hours']*60);
				
//				echo $start_minutes.' - > '.$end_minutes."\n";

				for($i=$start_minutes;$i<$end_minutes;$i+=$intervalMinutes) {
					$freebusy[$i]=1;
				}
			}
		}
		return $freebusy;
	}

	public function defaultAttributes() {
		$attr = parent::defaultAttributes();
		$attr['user_id'] = null;
		return $attr;
	}

	public function getSecurityToken() {
		return md5($this->event_id . $this->email . $this->event->ctime);
	}

	public function getStatusName() {
		switch ($this->status) {
			case self::STATUS_TENTATIVE :
				return \GO::t("Tentative", "calendar");
				break;

			case self::STATUS_DECLINED :
				return \GO::t("Declined", "calendar");
				break;

			case self::STATUS_ACCEPTED :
				return \GO::t("Accepted", "calendar");
				break;

			default:
				return \GO::t("Not responded yet", "calendar");
				break;
		}
	}

	/**
	 * Get related participant event. UUID and user_id of calendar must match. 
	 * Returns false if it doesn't exists.
	 * 
	 * @return Event
	 */
	public function getParticipantEvent() {

		if(!$this->event || !$this->user_id)
			return false;
		
		
		$params = \GO\Base\Db\FindParams::newInstance()
						->ignoreAcl()
						->single();		
		
		$params->getCriteria()
						->addCondition('uuid', $this->event->uuid)
						->addCondition('start_time', $this->event->start_time) //make sure start time matches for recurring series
						->addCondition("exception_for_event_id", 0, $this->event->exception_for_event_id==0 ? '=' : '!='); //the master event or a single occurrence can start at the same time. Therefore we must check if exception event has a value or is 0.


		$joinCriteria = \GO\Base\Db\FindCriteria::newInstance()
						->addCondition('calendar_id', 'c.id','=','t',true, true)
						->addCondition('user_id', $this->user_id,'=','c');

		$params->join(Calendar::model()->tableName(), $joinCriteria, 'c');
		
						
		return Event::model()->find($params);
	
	}

	/**
	 * Get related participant event. UUID and user_id of calendar must match. 
	 * Returns false if it doesn't exists.
	 * 
	 * @return Event
	 */
	public function getOrganizerEvent() {
		if ($this->is_organizer)
			return $this->event;
		else
			return Event::model()->findSingleByAttributes(array('uuid' => $this->event->uuid, 'is_organizer' => 1));
	}

	/**
	 * Get's the participant's default calendar if it has one.
	 * @return Calendar
	 */
	public function getDefaultCalendar() {
		if (empty($this->user_id))
			return false;

		return Calendar::model()->findDefault($this->user_id);
	}

	public function toJsonArray($start_time = false, $end_time = false) {
		$record = $this->getAttributes('html');

		$record['available'] = $this->isAvailable($start_time, $end_time);
		$calendar = $this->getDefaultCalendar();
		$record['create_permission'] = $calendar ? $calendar->userHasCreatePermission() : false;
		
		if($this->isNew){
			unset($record['id']);//otherwise it replaces new participants
		}
		return $record;
	}
	
	
	protected function afterSave($wasNew) {
		
		
		if(!$wasNew && $this->updateRelatedParticipants && $this->isModified('status')){
			$stmt = $this->getRelatedParticipants();
			
			foreach($stmt as $participant){
				
				$participant->updateRelatedParticipants=false;//prevent endless loop. Because it will also process this aftersave
				
				$participant->event->touch(); // Touch the event to update its mtime.
				
				$participant->status=$this->status;
				if(!$participant->save()) {
					throw new \Exception("Could not save participant: ". implode(", ", $participant->getValidationErrors()));
				}
			}
			
			//$this->event->touch(); // Touch the event to update the modification date.
		}
		
		if($wasNew && $this->event->is_organizer){
			
			//add this participant to each existing event.
			if (!$this->dontCreateEvent && $this->user_id > 0 && !$this->is_organizer) {
//			if ($this->user_id > 0 && !$this->is_organizer) {
				$newEvent = $this->event->createCopyForParticipant($this);					
			}
			
	
			$stmt = $this->event->getRelatedParticipantEvents();

			foreach($stmt as $event){
				if(!isset($newEvent) || !$newEvent || $event->id!=$newEvent->id){

					$p = Participant::model()->findSingleByAttributes(array(
							'event_id'=>$event->id,
							'email'=>$this->email
					));
					if(!$p){
						$p = new Participant();
						$p->setAttributes($this->getAttributes('raw'), false);
						$p->event_id=$event->id;
						$p->id=null;
						$p->save();
					}
				}
			}
			
			
			
			if(!$this->is_organizer && $this->contact_id && \GO::config()->calendar_autolink_participants){
				$contact = \go\modules\community\addressbook\model\Contact::findById($this->contact_id);
				if(!empty($contact)) {
					Link::create($contact, $this->event);
				}
			}
		}
		
//		$this->_updateEvents();
		
		return parent::afterSave($wasNew);
	}
	
	protected function afterDelete(){
		
		
		if($this->event && $this->event->is_organizer){
			$stmt = $this->getRelatedParticipants();
			
			foreach($stmt as $participant){
				if($event = $participant->getParticipantEvent())
				{
					if(!$event->is_organizer && $event->calendar->userHasCreatePermission())
						$event->delete(true);
				}
				$participant->delete();
			}
		}
		
		if($this->event) {
			$this->event->touch();
		}
		
		return parent::afterDelete();
	}
	
//	private function _updateEvents(){
//
//		if(!$this->event) {
//			return;
//		}
//		// Update mtime of all events (For each participant)
//		$stmt = $this->event->getRelatedParticipantEvents(true);
//		
//		foreach($stmt as $event){
//			$event->mtime = time();
//			$event->save(true);
//		}
//	}
	
	
//	private function _notifyOrganizer(){
//
////		if(!$sendingParticipant)
////			throw new \Exception("Could not find your participant model");
//
//		$organizer = $this->event->getOrganizer();
//		if(!$organizer)
//			throw new \Exception("Could not find organizer to send message to!");
//
//		$updateReponses = \GO::t("updateReponses", "calendar");
//		$subject= sprintf($updateReponses[$this->status], $this->user->name, $this->event->name);
//
//
//		//create e-mail message
//		$message = \GO\Base\Mail\Message::newInstance($subject)
//							->setFrom($this->user->email, $this->user->name)
//							->addTo($organizer->email, $organizer->name);
//
//		$body = '<p>'.$subject.': </p>'.$this->event->toHtml();
//
//		if(!$this->event->getOrganizerEvent()){
//			//organizer is not a Group-Office user with event. We must send a message to him an ICS attachment
//			$ics=$this->event->toICS("REPLY", $this, $this->notifyRecurrenceTime);				
//			$a = \Swift_Attachment::newInstance($ics, \GO\Base\Fs\File::stripInvalidChars($this->event->name) . '.ics', 'text/calendar; METHOD="REPLY"');
//			$a->setEncoder(new \Swift_Mime_ContentEncoder_PlainContentEncoder("8bit"));
//			$a->setDisposition("inline");
//			$message->attach($a);
//		}
//
//		$message->setHtmlAlternateBody($body);
//
//		\GO\Base\Mail\Mailer::newGoInstance()->send($message);
//		
//	}
	
	
	/**
	 * Returns all participant models for this event and all the related events for a meeting.
	 * 
	 * @return Participant
	 */
	public function getRelatedParticipants(){
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
						->addCondition('id', $this->id, '!=')
						->addCondition('email', $this->email)
						->addCondition('uuid', $this->event->uuid,'=','e')  //recurring series and participants all share the same uuid
						->addCondition('start_time', $this->event->start_time,'=','e') //make sure start time matches for recurring series
						->addCondition("exception_for_event_id", 0, $this->event->exception_for_event_id==0 ? '=' : '!=','e');//the master event or a single occurrence can start at the same time. Therefore we must check if exception event has a value or is 0.
		
		return Participant::model()->find($findParams);			
		
	}
	
	protected function beforeSave() {
		
		// Check for a user with this email address
		if($this->isNew && $this->user_id === null){
			$user = \GO\Base\Model\User::model()->findSingleByAttribute('email', $this->email);
			if($user)
				$this->user_id = $user->id;
		}
		
		if($this->is_organizer)
			$this->status=self::STATUS_ACCEPTED;
		
		return parent::beforeSave();
	}
}
