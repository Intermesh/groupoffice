<?php

namespace GO\Sync;

use go\core\auth\model\User;
use go\core\orm\Mapping;
use go\core\orm\Property;
use GO\Sync\Model\UserNoteBook;
use GO\Professional\Module;
use GO\Sync\Model\UserSettings;


class SyncModule extends Module{

	public function autoInstall() {
		return true;
	}
	
	public static function defineListeners() {

		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
	}
	

	public static function onMap(Mapping $mapping) {
		$mapping->addRelation('syncSettings', UserSettings::class, ['id' => 'user_id'], false)
						->addRelation('syncNoteBooks', UserNoteBook::class, ['id' => 'userId']);
	}
}
