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


class SyncModule extends Module{

	public function autoInstall() {
		return true;
	}
	
	public static function defineListeners() {

		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
		User::on(User::EVENT_SAVE, static::class, 'onUserSave');
		User::on(User::EVENT_BEFORESAVE, static::class, 'onUserBeforeSave');
	}
	

	public static function onMap(Mapping $mapping) {
		$mapping->addHasOne('syncSettings', UserSettings::class, ['id' => 'user_id'])
						->addArray('syncNoteBooks', UserNoteBook::class, ['id' => 'userId'])
						->addArray('syncAddressBooks', UserAddressBook::class, ['id' => 'userId']);
		
	}

	public static function onUserBeforeSave(User $user) {
		if($user->isNew()) {
			
			
			if(empty($user->syncAddressBooks)) {
				$addressBook = AddressBook::find(['id'])->filter(['permissionLevel' => Acl::LEVEL_WRITE, 'permissionLevelGroups' => $user->groups])->single();
				if($addressBook) {
					$user->syncAddressBooks[] = (new UserAddressBook())->setValues(['addressBookId' => $addressBook->id, 'isDefault' => true]);
				}
			}

			if(empty($user->syncNoteBooks)) {
				$noteBook = NoteBook::find(['id'])->filter(['permissionLevel' => Acl::LEVEL_WRITE, 'permissionLevelGroups' => $user->groups])->single();
				if($noteBook) {
					$user->syncNoteBooks[] = (new UserNoteBook())->setValues(['noteBookId' => $noteBook->id, 'isDefault' => true]);
				}
			}
		}
	}

	public static function onUserSave(User $user) {
		if($user->isNew()) {

			$legacyUser = GOUser::model()->findByPk($user->id);
			Settings::model()->findForUser($legacyUser);
		}
	}
}
