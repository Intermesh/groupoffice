<?php

namespace go\core\controller;

use Exception;
use GO;
use go\core\controller;
use go\core\jmap\Response;
use go\core\model;

class Settings extends Controller {

//	public function get() {
//		Response::get()->addResponse(GO()->getSettings()->toArray());
//	}
//
//	public function set($params) {
//		GO()->getSettings()->setValues($params);
//		$success = GO()->getSettings()->save();
//
//		Response::get()->addResponse(['success' => $success]);
//	}

	public function sendTestMessage($params) {
		
		$settings = GO()->getSettings()->setValues($params);
	
		$message = GO()->getMailer()->compose()
						->setFrom($settings->systemEmail, $settings->title)
						->setTo($settings->systemEmail)
						->setSubject(GO()->t('Test message'))
						->setBody(GO()->t("You're settings are correct.\n\nBest regards,\n\nGroup-Office"));

		$success = $message->send();
		
		Response::get()->addResponse(['success' => $success]);
	}
	
		public function applyDefaultGroups($params) {
		
		$settings = model\Settings::get()->setValues($params);		
		$success = $settings->save();
		if(!$success) {
			throw new Exception("Could not save settings");
		}
		
		$defaultGroups = $settings->getDefaultGroups();
				
		foreach(model\Group::find() as $group) {
			
			$groups = $defaultGroups;
			
			if(!in_array($group->id, $groups)) {
				$groups[] = $group->id;
			}
			
			$groupsRecords = array_map(function($groupId) {return ['groupId' => $groupId];}, $groups);
			
			$acl = $group->findAcl();
			
			$acl->setValues([
					'groups' => $groupsRecords
			]);
			
			if(!$acl->save()) {
				throw new Exception("Couldn't save ACL for group ". $group->id);
			}
		}
		
		Response::get()->addResponse(['success' => $success]);
		
	}

}
