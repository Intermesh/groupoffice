<?php
namespace go\modules\business;

use go\core\model\Module;
use go\core\model\User;

/**
 * Grant module rights to a user in the tests.
 *
 * Licensed modules aren't available in the tests, so {@see Module::getUserRights()} returns no rights for a user that
 * isn't an administrator. This stores the rights in core_permission like the system settings do, and puts the rights
 * computed from that record in the rights cache of the module, skipping the license check.
 */
trait ModuleRightsTrait
{
	/**
	 * Grant the given rights to the personal group of the user
	 *
	 * @param string $package
	 * @param string $name
	 * @param User $user
	 * @param string[] $rights Names of module rights, like "mayManage". Empty gives access to the module only.
	 */
	private static function grantModuleRights(string $package, string $name, User $user, array $rights): void
	{
		$module = Module::findByName($package, $name, null);
		$bits = 0;
		foreach ($module->module()->getRights() as $right => $bit) {
			if (in_array($right, $rights)) {
				$bits |= $bit;
			}
		}

		$groupId = $user->getPersonalGroup()->id;
		go()->getDbConnection()->delete('core_permission', ['moduleId' => $module->id, 'groupId' => $groupId])->execute();
		go()->getDbConnection()->insert('core_permission', ['moduleId' => $module->id, 'groupId' => $groupId, 'rights' => $bits])->execute();

		$userRights = (new \ReflectionMethod(Module::class, 'userRights'))->invoke($module, $user->id);
		$cache = new \ReflectionProperty(Module::class, 'userRightsCache');
		$value = $cache->getValue();
		$value[$module->id][$user->id] = $userRights;
		$cache->setValue(null, $value);
	}

	/**
	 * Remove the rights of the user granted with {@see grantModuleRights()}
	 */
	private static function revokeModuleRights(string $package, string $name, User $user): void
	{
		$module = Module::findByName($package, $name, null);
		go()->getDbConnection()->delete('core_permission', ['moduleId' => $module->id, 'groupId' => $user->getPersonalGroup()->id])->execute();
		(new \ReflectionProperty(Module::class, 'userRightsCache'))->setValue(null, []);
	}
}
