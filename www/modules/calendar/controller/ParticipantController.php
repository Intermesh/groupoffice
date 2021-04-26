<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: Calendar.php 7607 2011-09-14 10:07:02Z <<USERNAME>> $
 * @copyright Copyright Intermesh
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */  

/**
 * The Calendar controller
 *
 */


namespace GO\Calendar\Controller;


class ParticipantController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Calendar\Model\Participant';
	
	protected function getStoreParams($params) {
		$c = \GO\Base\Db\FindParams::newInstance()
						->criteria(\GO\Base\Db\FindCriteria::newInstance()
										->addModel(\GO\Calendar\Model\Participant::model())
										->addCondition('event_id', $params['event_id'])
										);
		return $c;
	}
	
	protected function prepareStore(\GO\Base\Data\Store $store) {
		
		$store->getColumnModel()->setFormatRecordFunction(array($this, 'formatParticipantRecord'));
		
		return $store;
	}
	
	public function formatParticipantRecord($record, $model, $store){
		
		$record['available']=$model->isAvailable();
		
		return $record;
	}
	
	
	public function actionLoadOrganizer($params){
		
		$calendar = \GO\Calendar\Model\Calendar::model()->findByPk($params['calendar_id']);		
		
		$user = $calendar->user_id==1 ? \GO::user() : $calendar->user;
		if(empty($user)) { // user was deleted make admin owner of this calendar
			$calendar->user_id=1;
			$calendar->save();
			$user = $calendar->user;
		}
		$participant = new \GO\Calendar\Model\Participant();
		$participant->user_id=$user->id;
		$participant->name=$user->name;
		$participant->email=$user->email;
		$participant->is_organizer=true;
		
		return array('success'=>true, 'organizer'=>$participant->toJsonArray($params['start_time'],$params['end_time']));
	}
	
	public function actionReload($params){
		$event = empty($params['event_id']) ? false : \GO\Calendar\Model\Event::model()->findByPk($params['event_id']);

		$participantAttrs=json_decode($params['participants']);

		$store = new \GO\Base\Data\ArrayStore();
		
		foreach($participantAttrs as $participantAttr) {
			$participant = new \GO\Calendar\Model\Participant();
			$participant->setAttributes($participantAttr);
			if($event)
				$participant->event_id=$event->id;
			
			$store->addRecord($participant->toJsonArray($params['start_time'], $params['end_time']));
		}
		
		return $store->getData();
	}

	public function actionGetContacts($params){
		$contacts = json_decode($params['contacts'], true);

		$store = new \GO\Base\Data\ArrayStore();

		foreach($contacts as $contact){

			$contactEntity = \go\modules\community\addressbook\model\Contact::findById($contact['entityId']);
			if(empty($contact['email'])) {
				$contact['email'] = $contactEntity->emailAddresses[0]->email;
			}
			if(empty($contact['name'])) {
				$contact['name'] = $contactEntity->name;
			}
			$userId = $contactEntity->goUserId;
			if(!$userId) {
				$user = \go\core\model\User::find()->where(['email' => $contact['email']])->single();
				if($user) {
					$userId = $user->id;
				}
			}
			
			
			$participant = new \GO\Calendar\Model\Participant();
			$participant->contact_id=$contact['entityId'];
			$participant->user_id=$userId;
			$participant->name=$contact['name'];
			$participant->email=$contact['email'];

			$store->addRecord($participant->toJsonArray($params['start_time'], $params['end_time']));
		}
		
		return $store->getData();
	}
	
	
	
	
	
	public function actionGetUsers($params){
		$ids = json_decode($params['users']);
		
		$oldParticipantIds = !empty(\GO::session()->values['new_participant_ids']) ? \GO::session()->values['new_participant_ids'] : array();
		\GO::session()->values['new_participant_ids'] = array_merge($oldParticipantIds,$ids);
		
		$store = new \GO\Base\Data\ArrayStore();

		foreach($ids as $user_id){

			$user=\GO\Base\Model\User::model()->findByPk($user_id, false,  true);

			$participant = new \GO\Calendar\Model\Participant();
			$participant->user_id=$user->id;
			$participant->name=$user->name;
			$participant->email=$user->email;
			$participant->is_organizer=!empty($params['is_organizer']);
		

			$store->addRecord($participant->toJsonArray($params['start_time'], $params['end_time']));
		}
		
		return $store->getData();
	}
	
	protected function actionClearNewParticipantsSession($params) {
		unset(\GO::session()->values['new_participant_ids']);
		return array('success'=>true);
	}
	
	public function actionGetUserGroups($params){
		$ids = json_decode($params['groups']);

		$store = new \GO\Base\Data\ArrayStore();
		
		$addedUsers=array();

		foreach($ids as $group_id){

			$group=\GO\Base\Model\Group::model()->findByPk($group_id, false, true);
			
			$stmt = $group->users();
			
			foreach($stmt as $user){
				
				if(!in_array($user->id, $addedUsers)){
					
					$addedUsers[]=$user->id;
					
					$participant = new \GO\Calendar\Model\Participant();
					$participant->user_id=$user->id;
					$participant->name=$user->name;
					$participant->email=$user->email;

					$store->addRecord($participant->toJsonArray($params['start_time'], $params['end_time']));
				}
			}
		}
		
		return $store->getData();
	}
	
	
	
	public function actionFreeBusyInfo($params) {

		$event_id = empty($params['event_id']) ? 0 : $params['event_id'];
		$date=getdate(\GO\Base\Util\Date::to_unixtime($params['date']));
		$daystart = mktime(0,0,0,$date['mon'], $date['mday'], $date['year']);
		$dayend = mktime(0,0,0,$date['mon'], $date['mday']+1, $date['year']);
		
		$response['results'] = array();
		$response['success'] = true;
		
		$merged_free_busy_participants = array();
		for ($i = 0; $i < 1440; $i+=15) {
			$merged_free_busy_participants[$i] = 0;
		}
		
		$merged_free_busy_all = array();
		for ($i = 0; $i < 1440; $i+=15) {
			$merged_free_busy_all[$i] = 0;
		}
		
		// Create Participants header row
		$row['strong'] = true;
		$row['name'] = \GO::t("Participants", "calendar");
		$row['email'] = '';
		$row['freebusy'] = array();
		
		for ($min=0; $min < 1440; $min+=15) {
			$row['freebusy'][] = array(
					'time' => date(\GO::user()->time_format, mktime(0, $min)),
					'busy' => false);
		}

		$response['results'][] = $row;

		$participants = !empty($params['participantData']) ? json_decode($params['participantData'], true) : [];
		
		// Create a participant row for every participant

		$participants = json_decode($params['participantData'], true);

		foreach($participants as $row){

			$row['freebusy'] = array();

			if(!empty($row['user_id'])){

				$user = \GO\Base\Model\User::model()->findByPk($row['user_id'], false,true);
				if ($user){
					$participant = new \GO\Calendar\Model\Participant();
					$participant->user_id=$user->id;
					$participant->name=$user->name;
					$participant->email=$user->email;
					$participant->event_id=$event_id;

					if ($participant->hasFreeBusyAccess()){
						$freebusy = $participant->getFreeBusyInfo($daystart, $dayend);
						foreach ($freebusy as $min => $busy) {
							if ($busy == 1) {
								$merged_free_busy_participants[$min] = 1;
								$merged_free_busy_all[$min] = 1;
							}
							$row['freebusy'][] = array('time' => date('G:i', mktime(0, $min)),'busy' => $busy);
						}
					}
				}
			}
			$row['strong'] = false;
			$response['results'][] = $row;
		}

		if(count($participants) > 1) {
			// Create the together row
			$row['strong'] = false;
			$row['name'] = \GO::t("All participants together", "calendar");
			$row['email'] = '';
			$row['freebusy'] = array();

			foreach ($merged_free_busy_participants as $min => $busy) {
				$row['freebusy'][] = array(
					'time' => date(\GO::user()->time_format, mktime(0, $min)),
					'busy' => $busy);
			}

			$response['results'][] = $row;
		}
		
		// And now for the resources...


		
		
		$merged_free_busy_resources = array();
		for ($i = 0; $i < 1440; $i+=15) {
			$merged_free_busy_resources[$i] = 0;
		}
		
		$resourceIds = json_decode($params['resourceIds']);
		if (empty($resourceIds)) $resourceIds = array('-1');
		
		$calendarsStmt = \GO\Calendar\Model\Calendar::model()->find(
			\GO\Base\Db\FindParams::newInstance()
				->criteria(\GO\Base\Db\FindCriteria::newInstance()
					->addInCondition('id',$resourceIds)
				)
				->order('name','ASC')
		);

		if($calendarsStmt->rowCount() > 0) {
			$resource['strong'] = true;
			$resource['name'] = \GO::t("Resources", "calendar");
			$resource['email'] = '';
			$resource['freebusy'] = array();

			for ($min = 0; $min < 1440; $min += 15) {
				$resource['freebusy'][] = array(
					'time' => date(\GO::user()->time_format, mktime(0, $min)),
					'busy' => false);
			}

			$response['results'][] = $resource;


			foreach ($calendarsStmt as $resourceCalModel) {
				$resource['strong'] = false;
				$resource['name'] = $resourceCalModel->name;
				$resource['email'] = $resourceCalModel->user->email;
				$resource['freebusy'] = array();

				$freebusy = $resourceCalModel->getFreeBusyInfo($daystart);
				foreach ($freebusy as $min => $busy) {
					if ($busy == 1) {
						$merged_free_busy_resources[$min] = 1;
						$merged_free_busy_all[$min] = 1;
					}
					$resource['freebusy'][] = array(
						'time' => date('G:i', mktime(0, $min)),
						'busy' => $busy);
				}

				$response['results'][] = $resource;
			}
		}
		
		if($calendarsStmt->rowCount() > 1) {
			$resource['name'] = \GO::t("All resources together", "calendar");
			$resource['email'] = '';
			$resource['freebusy'] = array();

			foreach ($merged_free_busy_resources as $min => $busy) {
				$resource['freebusy'][] = array(
					'time' => date(\GO::user()->time_format, mktime(0, $min)),
					'busy' => $busy);
			}

			$response['results'][] = $resource;
		}

		if($calendarsStmt->rowCount() > 0) {

			$business['name'] = \GO::t("All together", "calendar");
			$business['strong'] = true;
			$business['email'] = '';
			$business['freebusy'] = array();

			foreach ($merged_free_busy_all as $min => $busy) {
				$business['freebusy'][] = array(
					'time' => date(\GO::user()->time_format, mktime(0, $min)),
					'busy' => $busy);
			}


			$response['results'][] = $business;
		}
		
		return $response;
	}

}
