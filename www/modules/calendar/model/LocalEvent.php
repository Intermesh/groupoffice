<?php

// An event model that is used by the calendar view to show the correct event data.
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * 
 * @property Event $_event
 * @property Calendar $_calendar
 * @property string $_startTime
 * @property string $_endTime
 * @property string $_alternateEndTime
 * @property string $_alternateStartTime
 * 
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

namespace GO\Calendar\Model;


class LocalEvent extends \GO\Base\Model {
	
	
	private $_initials = array();
	private $_calendarNames = array();
	
	private $_isMerged = false;
	
	private $_backgroundColor = '';
	
	public $displayId = 0;
	
	private $_displayName = '';
	
	/**
	 *
	 * @var Event 
	 */
	private $_event;
	
	/**
	 *
	 * @var Calendar 
	 */
	private $_calendar;
	
	/**
	 *
	 * @var StringHelper 
	 */
	private $_startTime;
	
	/**
	 *
	 * @var StringHelper 
	 */
	private $_endTime;

	/**
	 * The end time of an recurring event in the current period
	 * 
	 * @var StringHelper 
	 */
	private $_alternateEndTime;
	
	/**
	 * The start time of an recurring event in the current period
	 * 
	 * @var StringHelper 
	 */
	private $_alternateStartTime;
	
	/**
	 * Constructor
	 * 
	 * @param Event $event
	 * @param StringHelper $periodStartTime
	 * @param StringHelper $periodEndTime 
	 */
	public function __construct(Event $event, $periodStartTime, $periodEndTime){
		$this->_event = $event;
		$this->_startTime = $periodStartTime;
		$this->_endTime = $periodEndTime;
		$this->_backgroundColor = $event->background;
		$this->_displayName = $event->name;
		
		$this->_calendar = $this->_event->calendar;	
		
		// If there is no user attached to this event (user_id = 0) then create a temporary user object
//		if(!$event->user){
//			$event->user = new \GO\Base\Model_User();
//			$event->user->first_name = \GO::t('unknown').' '.\GO::t('user');
//		}
		
		$this->_initials[] = $event->user ? $event->user->getShortName() : '??';
		$this->_calendarNames[] = $this->_calendar->name;
	}

	public function setBackgroundColor($color){
		$this->_backgroundColor = $color;
	}
	
	public function getUuid(){
		return $this->_event->uuid;
	}
	
	public function getResponseData(){
		
		$dayString = \GO::t('full_days');
		
		$response = $this->_event->getAttributes('formatted');

		if($this->isAllDay()){
			$response['time'] =  $this->getFormattedTime();
		} else {
			if (date(\GO::user()->date_format, $this->getAlternateStartTime()) != date(\GO::user()->date_format, $this->getAlternateEndTime()))
				$response['time'] =  $this->getFormattedTime();
			else
				$response['time'] =  $this->getFormattedTime();
		}
//		$response['time_of_day'] = $this->getTimeOfDay();
        
		$response['status'] = $this->_event->status;
		$response['username'] = $this->_event->user ? $this->_event->user->getName() : \GO::t('unknown').' '.\GO::t('user');
		$response['musername'] = !empty($this->_event->mUser) ? $this->_event->mUser->getName() : '';
		
//		if($this->_event->status==Event::STATUS_CANCELLED){			
//			$response['name'] .= ' ('.$this->_event->localizedStatus.')';
//		}
//		
		if($this->_isMerged){
			$response['name'] = $response['name'] .' ('.implode(',',$this->_initials).')';
			$response['calendar_name'] = implode('; ',$this->_calendarNames);
			unset($response['status']); // unset this, it is not relevant to show this in merge view
			unset($response['username']); // unset this, it is not relevant to show this in merge view.
		}else
		{
			$response['calendar_name']=$this->_calendarNames[0];
		}
		
		$response['id'] = $this->_event->id.':'.$this->getAlternateStartTime(); // a unique id for the data store. Is not really used.
		$response['background'] = $this->_backgroundColor;
		$response['recurring_start_time'] = date('Y-m-d H:i', $this->_event->start_time);
		$response['start_time'] = date('Y-m-d H:i', $this->getAlternateStartTime());
		$response['end_time'] = date('Y-m-d H:i',  $this->getAlternateEndTime());	
		$response['ctime'] = date('Y-m-d H:i',  $this->_event->ctime);
		$response['mtime'] = date('Y-m-d H:i',  $this->_event->mtime);
		$response['event_id'] = $this->_event->id;
		$response['description'] = \GO\Base\Util\StringHelper::cut_string($this->_event->description, 800);
		$response['private'] = $this->isPrivate();
		
		$response['private_enabled']=$this->_event->private;
		
		$response['resources'] = array();
		
		if($response['private']){
			$response['name']=\GO::t('private','calendar');
			$response['description']="";
			$response['location']="";
			$response['repeats'] = false;
			$response['has_reminder'] = false;
			$response['link_count'] = false;
			$response['status_color'] = false;
			$response['status'] = false;
		} else {
			$response['repeats'] = $this->isRepeating();
			$response['has_reminder'] = $this->_event->hasReminders()?1:0;
			$response['link_count'] = $this->getLinkCount();
			$response['status_color'] = $this->_event->getStatusColor();
			
			if ($this->_event->resources)	{
				foreach ($this->_event->resources as $resourceModel) {
					$response['resources'][$resourceModel->id] = $resourceModel->calendar->name;
				}
			}
		
		}
		
		
		$response['permission_level']=$this->_event->permissionLevel;
		$response['all_day_event'] = $this->isAllDay();
		$response['day'] = $dayString[date('w', $this->getAlternateStartTime())].' '.\GO\Base\Util\Date::get_timestamp($this->getAlternateStartTime(),false);  // date(implode(\GO::user()->date_separator,str_split(\GO::user()->date_format,1)), ($eventModel->start_time));
		$response['read_only'] = $this->isReadOnly();
		$response['model_name'] = $this->_event->className();
		
		$response['partstatus']="none";
		if(isset($response['status']) && $response['status']==Event::STATUS_CANCELLED){
			//hack to make it transparent on cancelled status too in the view.
			$response['partstatus']=  Participant::STATUS_DECLINED;
		}else{
			if($participant = $this->_event->getParticipantOfCalendar()){
				$response['partstatus']=$participant->status;
			}
		}
		
		$duration = $this->getDurationInMinutes();

		if($duration >= 60){
			$durationHours = floor($duration / 60);
			$durationRestMinutes = $duration % 60;
			$response['duration'] = $durationHours.' '.\GO::t('hours').', '.$durationRestMinutes.' '.\GO::t('mins');
		} else {
			$response['duration'] = $duration.'m';
		}
		
		return array_map(array("\GO\Base\Util\StringHelper", "encodeHtml"),$response);
	}
	
	public function getName(){
		return $this->_displayName;
	}
//    
//    /**
//     * Get the time of day the event occurs.
//     * If event is not set or there is no start_time we return "FullDay"
//     * @return string (morning|afternoon|evening|fullday)
//     */
//    public function getTimeOfDay()
//    {
//      if(!isset($this->_event) && empty($this->_event->start_time)) 
//        return "fullday";
//      $hour = date('G', $this->_event->start_time); //0 - 23
//      
//      if($hour >= 0 && $hour < 12)
//        return "morning";
//      elseif($hour >= 12 && $hour < 18)
//        return "afternoon";
//      elseif($hour >= 18)
//        return "evening";
//    }
	
	
	public function mergeWithEvent($event){
		
		//echo $this->_event->user->getShortName().' : '. $event->getEvent()->user->getShortName().', ';
		
			
		$this->_isMerged = true;
		$this->_initials[] = $event->getCalendar()->user->getShortName();
		$this->_calendarNames[] = $event->getCalendar()->name;
		$this->_backgroundColor = 'FFFFFF';
		
		if($event->getEvent()->is_organizer){
			$this->_event = $event->getEvent();
		}
		
	}

	/**
	 * Get the start time of the recurring event in the selected period
	 * 
	 * @return int Unix timestamp 
	 */
	public function getAlternateStartTime(){
		if(empty($this->_alternateStartTime)) {
			$this->setAlternateStartTime($this->_event->start_time);
		}
		return $this->_alternateStartTime;
	}
	
	/**
	 * Get the end time of the recurring event in the selected period
	 * 
	 * @return int Unix timestamp 
	 */
	public function getAlternateEndTime(){
		if(empty($this->_alternateEndTime)) {
			$this->setAlternateEndTime($this->_event->end_time);
		}
		return $this->_alternateEndTime;
	}
	
	/**
	 * Set the start time of the recurring event in the selected period
	 * 
	 * @param int $time  Unix timestamp
	 */
	public function setAlternateStartTime($time){
		// Convert timestamp to the zone of the user who has entered the event
		// Timezone may set the date incorrect so setTimezone() is needed
		if($this->_event->isFullDay()) {
			try {
				$eventTimezone =  new \DateTimeZone($this->_event->timezone);
			} catch(\Exception $e) {
				$eventTimezone = new \DateTimeZone(GO::config()->default_timezone);
				trigger_error("The timezone ".$this->_event->timezone." is invalid in event: ".$this->_event->id, E_USER_WARNING);
			}
			$startTime = new \DateTime();
			$startTime->setTimestamp($time);
			$startTime->setTimezone($eventTimezone);
			$this->_alternateStartTime = strtotime($startTime->format('Y-m-d 00:00:00'));
		} else {
			$this->_alternateStartTime = $time;
		}
	}
	
	/**
	 * Set the end time of the recurring event in the selected period
	 * 
	 * @param int $time  Unix timestamp
	 */
	public function setAlternateEndTime($time){

		if($this->_event->isFullDay()) {
			try {
				$eventTimezone =  new \DateTimeZone($this->_event->timezone);
			} catch(\Exception $e) {
				$eventTimezone = new \DateTimeZone(GO::config()->default_timezone);
				trigger_error("The timezone ".$this->_event->timezone." is invalid in event: ".$this->_event->id, E_USER_WARNING);
			}
			$endTime = new \DateTime();
			$endTime->setTimestamp($time);
			$endTime->setTimezone($eventTimezone);
			$this->_alternateEndTime = strtotime($endTime->format('Y-m-d 23:59:59'));
		} else {
			$this->_alternateEndTime = $time;
		}
	}
	
	/**
	 * Get the period start time
	 * 
	 * @return StringHelper 
	 */
	public function getPeriodStartTime(){
		return $this->_startTime;
	}
	
	/**
	 * Get the period end time
	 * 
	 * @return StringHelper 
	 */
	public function getPeriodEndTime(){
		return $this->_endTime;
	}
	
	
	/**
	 *
	 * @return Event 
	 */
	public function getEvent(){
		return $this->_event;
	}
	
	/**
	 *
	 * @return Calendar 
	 */
	public function getCalendar(){
		return $this->_calendar;
	}
	
	/**
	 * Get the number of links that this events has
	 * 
	 * @return int 
	 */
	public function getLinkCount(){
		return $this->_event->countLinks();
	}
	
	/**
	 * Get the formatted starting date of this event
	 * 
	 * @return StringHelper 
	 */
	public function getFormattedDate(){
		return date(\GO::user()->date_format,$this->_event->start_time);
	}
	
	/**
	 * Get the formatted starting date and time of this event
	 * 
	 * @return StringHelper 
	 */
	public function getFormattedDateAndTime(){
		return date(\GO::user()->date_format.' '.\GO::user()->time_format,$this->_event->start_time);
	}
	
	/**
	 * Get the formatted starting time of this event
	 * 
	 * @return StringHelper 
	 */
	public function getFormattedTime(){
		return date(\GO::user()->time_format,$this->_event->start_time);
	}
	
	/**
	 * Get the day this event starts on.
	 * 
	 * @return StringHelper 
	 */
	public function getDay(){
		$dayString = \GO::t('full_days','common');
		return $dayString[date('w',$this->_event->start_time)];
	}
	
	/**
	 * Get the time of the event duration in minutes
	 * 
	 * @return int 
	 */
	public function getDurationInMinutes(){
		
		$durationMinutes = ($this->_event->end_time-$this->_event->start_time)/60;

		return $durationMinutes;
	}

	/**
	 * Is this an all day event
	 * 
	 * @return boolean 
	 */
	public function isAllDay(){
		return $this->_event->all_day_event;
	}
	
	/**
	 * Is this a repeating event
	 * 
	 * @return boolean 
	 */
	public function isRepeating(){
		return !empty($this->_event->rrule);
	}
	
	/**
	 * Is this a private event for the current user. If the event or the calendar
	 * is owned by the current user it will not be displayed as private.
	 * 
	 * @return boolean 
	 */
	public function isPrivate(){
		return $this->_event->private && 
				(\GO::user()->id != $this->_event->user_id) && 
				\GO::user()->id!=$this->_event->calendar->user_id;	
	}
	
	/**
	 * Is this a read only event
	 * 
	 * @return boolean 
	 */
	public function isReadOnly(){
		return 
						//$this->_isMerged ||
						$this->_event->read_only || 
						!$this->_event->is_organizer || 
						$this->isPrivate() && \GO::user()->id != $this->_event->user_id || 
						$this->_event->permissionLevel < \GO\Base\Model\Acl::WRITE_PERMISSION;
	}
	
	/**
	 * Does this event have more participants
	 * 
	 * @return boolean 
	 */
	public function hasOtherParticipants(){
		return $this->_event->hasOtherParticipants();
	}

}