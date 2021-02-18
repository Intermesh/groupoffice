<?php

namespace GO\Calendar\Controller;

use Exception;
use GO\Base\Controller\AbstractController;
use GO\Base\Exception\NotFound;
use GO\Base\Util\Number;
use GO\Calendar\Model\Event;


class AttendanceController extends AbstractController{
	protected function actionLoad($params){
		
		$event = Event::model()->findByPk($params['id']);
		if(!$event)
			throw new NotFound();
		
		$participant=$event->getParticipantOfCalendar();
		if(!$participant) {
			$name = $event->calendar->user->displayName . ' (' . $event->calendar->user->email . ')';
			throw new Exception(str_replace('{name}', $name, "{name} was not invited for this event"));
		}
		
		$organizer = $event->getOrganizer();
		if(!$organizer)
			throw new Exception("The organizer of this event is missing");
		
		$response = array("success"=>true, 'data'=>array(
				'notify_organizer'=>true,
				'status'=>$participant->status, 
				'organizer'=>$organizer->name,
				'info'=>$event->toHtml(),
				'reminder'=>$event->reminder
			));		
		
		// Translate the reminder back to the 2 params needed for the form
		$response = EventController::reminderSecondsToForm($response);
		
		return $response;
	}
	
	protected function actionSubmit($params){
		$response = array("success"=>true);
		
		$event = Event::model()->findByPk($params['id']);
		if(!$event)
			throw new NotFound();
		
		
		// Reset the reminder value to NULL, processing will be done below
		if(isset($params['enable_reminder'])){
			$event->reminder = null;
		}
		
		
		// If enable_reminder is checked, then process it further
		if(!empty($params['enable_reminder'])){
			
			$event->reminder = 0; // The reminder will be on the event start by default
			
			if(isset($params['reminder_value']) && !empty($params['reminder_value']) && isset($params['reminder_multiplier'])){
				$event->reminder = \GO\Base\Util\Number::unlocalize ($params['reminder_value']) * $params['reminder_multiplier'];
			}
		}
		
		$event->save();
		
		if(!empty($params['exception_date'])){
			$event = $event->createExceptionEvent($params['exception_date']);
		}
		
		$participant=$event->getParticipantOfCalendar();
		if($params['status']!=$participant->status){
			$participant->status=$params['status'];
			$participant->save();
		
			if(!empty($params['notify_organizer']))
				$event->replyToOrganizer();
		}

		return $response;
	}
}
