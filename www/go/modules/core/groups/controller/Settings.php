<?php
namespace go\modules\core\groups\controller;

use go\core\jmap\Controller;
use go\core\jmap\Response;
use go\modules\core\groups\model;


class Settings extends Controller {
	
	public function get() {
		$settings = model\Settings::get();
		Response::get()->addResponse($settings->toArray());
	}
	
	public function set($params) {
		
		$settings = model\Settings::get()->setValues($params);		
		$success = $settings->save();
		
		Response::get()->addResponse(['success' => $success]);
	}
	
	public function applyDefaultGroups($params) {
		
		$settings = model\Settings::get()->setValues($params);		
		$success = $settings->save();
		if(!$success) {
			throw new \Exception("Could not save settings");
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
				throw new \Exception("Couldn't save ACL for group ". $group->id);
			}
		}
		
		Response::get()->addResponse(['success' => $success]);
		
	}
}
