<?php

namespace GO\Freebusypermissions;

use go\core\model\User;
use go\core\orm;

class FreebusypermissionsModule extends \GO\Base\Module{
	
	/**
	 * Initialize the listeners for the ActiveRecords
	 */
	public static function initListeners(){	
		\GO\Calendar\Model\Event::model()->addListener('load', 'GO\Freebusypermissions\FreebusypermissionsModule', 'has_freebusy_access');
	}
	
	public function defineListeners() {
		User::on(orm\Property::EVENT_MAPPING, static::class, 'onMap');
	}
	
	public function autoInstall() {
		return false;
	}
	
	public static function onMap(orm\Mapping $mapping) {
		$mapping->addHasOne('freebusySettings', model\UserSettings::class, ['id' => 'user_id'], true);
		return true;
	}
	
	public static function hasFreebusyAccess($request_user_id, $target_user_id){
		
		$fbAcl = FreebusypermissionsModule::getFreeBusyAcl($target_user_id);
		

		return \GO\Base\Model\Acl::getUserPermissionLevel($fbAcl->acl_id, $request_user_id) > 0;
	}

	public static function loadSettings($settingsController, &$params, &$response, $user) {
		
		$acl = FreebusypermissionsModule::getFreeBusyAcl($user->id);
		
		if(!empty($acl))
			$response['data']['freebusypermissions_acl_id']=$acl->acl_id;
		
		return parent::loadSettings($settingsController, $params, $response, $user);
	}
	
	public static function getFreeBusyAcl($userId){
		
		$fbAcl = Model\FreeBusyAcl::model()->findSingleByAttribute('user_id', $userId);
		
		if(!$fbAcl){
			
			$acl = new \GO\Base\Model\Acl();
			$acl->ownedBy = $userId;
			$acl->usedIn = Model\FreeBusyAcl::model()->tableName();
			$acl->save();
			
			if($acl){
				$fbAcl = new Model\FreeBusyAcl();
				$fbAcl->user_id = $userId;
				$fbAcl->acl_id = $acl->id;
				$fbAcl->save();
			} else {
				$fbAcl = false;
			}		
		}
		return $fbAcl;
	}
}
