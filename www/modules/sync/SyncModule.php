<?php

namespace GO\Sync;

use GO\Base\Model\User as GOUser;
use GO\Base\Module;
use go\core\model\Acl;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\model\User;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\notes\model\NoteBook;
use GO\Sync\Model\Settings;
use GO\Sync\Model\UserAddressBook;
use GO\Sync\Model\UserNoteBook;
use GO\Sync\Model\UserSettings;
use go\core\model;

class SyncModule extends Module{

	public function autoInstall() {
		return true;
	}
	
	public function defineListeners() {

		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
		// User::on(User::EVENT_SAVE, static::class, 'onUserSave');
		// User::on(User::EVENT_BEFORE_SAVE, static::class, 'onUserBeforeSave');
	}
	

	public static function onMap(Mapping $mapping) {
		$mapping->addHasOne('syncSettings', UserSettings::class, ['id' => 'user_id'], true);
		
	}

	
	// public static function onUserSave(User $user) {
	// 	if($user->isNew()) {

	// 		if(!model\Module::isAvailableFor('community', 'sync', $user->id)) {
	// 			return true;
	// 		}

	// 		$legacyUser = GOUser::model()->findByPk($user->id);
	// 		Settings::model()->findForUser($legacyUser);
	// 	}
	// }
}
