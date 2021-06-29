<?php
/**
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
namespace go\modules\community\tasks;
							
use go\core;
use go\core\model;
use go\core\model\Group;
use go\core\model\User;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\modules\community\tasks\model\UserSettings;

class Module extends core\Module {
							
	public function getAuthor() {
		return "Intermesh BV <info@intermesh.nl>";
	}

	public function autoInstall()
	{
		return true;
	}

	public function defineListeners()
	{
		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
	}

	public static function onMap(Mapping $mapping) {
		$mapping->addHasOne('tasksSettings', UserSettings::class, ['id' => 'userId'], true);
	}

	protected function afterInstall(model\Module $model)
	{
		// Share address book module with Internal group
		if(!$model->findAcl()
			->addGroup(Group::ID_INTERNAL)
			->save()) {
			return false;
		}

		return parent::afterInstall($model);
	}

}