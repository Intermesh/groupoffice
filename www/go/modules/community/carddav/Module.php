<?php
namespace go\modules\community\carddav;

use go\core;
use go\core\model;
use go\core\model\Group;
use go\core\model\Module as GoModule;
use go\core\model\Permission;

class Module extends core\Module {
	/**
	 * The development status of this module
	 * @return string
	 */
	public function getStatus() : string{
		return self::STATUS_STABLE;
	}

	public function getAuthor(): string
	{
		return "Intermesh BV <info@intermesh.nl>";
	}

	public function autoInstall(): bool
	{
		return true;
	}

	protected function beforeInstall(GoModule $model): bool
	{
		// Share module with Internal group
		$model->permissions[Group::ID_INTERNAL] = (new Permission($model))
			->setRights(['mayRead' => true]);

		return parent::beforeInstall($model);
	}

}