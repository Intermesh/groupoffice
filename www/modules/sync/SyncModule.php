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
	}

	public static function onMap(Mapping $mapping) {
		$mapping->addHasOne('syncSettings', UserSettings::class, ['id' => 'user_id'], true);
	}

	/**
	 * Include Z-Push to use the Z-Push classes in update scripts for example
	 *
	 * @throws FatalMisconfigurationException
	 */
	public static function requireZPush() {
		define("GO_CONFIG_FILE", \go\core\App::findConfigFile());
		define('ZPUSH_CONFIG', go()->getEnvironment()->getInstallPath() . '/modules/z-push/config.php');
		require_once go()->getEnvironment()->getInstallPath() . '/modules/z-push/vendor/z-push/vendor/autoload.php';
		require_once (go()->getEnvironment()->getInstallPath() . '/modules/z-push/backend/go/autoload.php');
		require_once (ZPUSH_CONFIG);
		\ZPush::CheckConfig();
	}

}
