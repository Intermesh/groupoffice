<?php

namespace go\core\controller;

use GO\Base\Model\Module;
use go\core\exception\Forbidden;
use go\core\exception\NotFound;
use go\core\Installer;
use go\core\jmap\SetError;
use go\core\orm\exception\SaveException;
use go\core\util\ArrayObject;

class ModuleInfo extends \go\core\Controller
{
	/**
	 * Get the installed and installable modules with their settings
	 *
	 * @apiPermission Administrators get all modules. Other users only get the installed modules they may manage, because the module model holds its settings.
	 * @apiReadOnly
	 */
	public function get(array $params)
	{
		$list = array_map(function ($m) {
			return $m->toArray();
		}, $this->getVisibleModules());

		if (go()->getAuthState()->isAdmin()) {
			$list = array_merge($list, $this->getUnavailable());
		}

		if (!empty($params['ids'])) {
			$list = array_values(array_filter($list, function ($m) use ($params) {
				return in_array($m['id'], $params['ids']);
			}));
		}

		return ["list" => $list];
	}

	private function getUnavailable(): array
	{
		$installer = new Installer();

		$mods = [];
		foreach ($installer->getUnavailableModules(null) as $unavailableModule) {
			if (empty($unavailableModule['package'])) {
				$unavailableModule['package'] = 'legacy';
			}
			$mods[] = [
				'id' => $unavailableModule['package'] . "/" . $unavailableModule['name'],
				'name' => $unavailableModule['name'],
				'package' => $unavailableModule['package'],
				'title' => $unavailableModule['name'],
				'author' => "Unknown",
				'description' => "",
				'rights' => [],
				"status" => "unavailable",
				'packageTitle' => go()->t("Unavailable"),

				'enabled' => $unavailableModule['enabled'],
				'installed' => true,
				'model' => null,
				'available' => false,
				'documentationUrl' => "",
				'category' => go()->t("Unavailable")

			];
		}

		return $mods;
	}

	/**
	 * Get the ID's of the installed and installable modules
	 *
	 * @apiPermission Checked in ModuleInfo::getVisibleModules(): administrators get all modules, other users the installed modules they may manage.
	 * @apiReadOnly
	 */
	public function query(array $params)
	{
		return ['ids' => array_map(function ($m) {
			return $m->getPackage() . "/" . $m->name();
		}, $this->getVisibleModules())];
	}

	/**
	 * The available modules the current user may see. Administrators see all of them, other users only the installed
	 * modules they may manage.
	 *
	 * @return \go\core\Module[]
	 */
	private function getVisibleModules(): array
	{
		$col = new \GO\Base\ModuleCollection();
		$mods = array_map(function ($m) {
			return $m::get();
		}, $col->getAvailableModules(true));

		if (go()->getAuthState()->isAdmin()) {
			return $mods;
		}

		return array_values(array_filter($mods, function ($m) {
			$model = $m->getModel();
			return $model && !empty($model->getUserRights()->mayManage);
		}));
	}

	/**
	 * Install, uninstall, enable or delete modules
	 *
	 * @apiPermission Only administrators.
	 * @throws Forbidden
	 */
	public function set(array $params)
	{
		if (!go()->getAuthState()->isAdmin()) {
			throw new Forbidden();
		}

		$result = new ArrayObject([
			'created' => null,
			'updated' => null,
			'destroyed' => null,
			'notCreated' => null,
			'notUpdated' => null,
			'notDestroyed' => null,
		]);

		if (!empty($params['update'])) {
			$result['updated'] = [];
			foreach ($params['update'] as $id => $props) {
				list($package, $name) = explode('/', $id);

				$module = \go\core\model\Module::findByName($package, $name, null);

				if (!$module && !empty($props['installed'])) {
					$module = $this->install($package, $name);
				}

				if ($module && isset($props['installed']) && !$props['installed']) {
					$module->uninstall();
					$result['updated'][$id] = null;
					continue;
				}

				if (isset($props['enabled'])) {
					$module->enabled = $props['enabled'];
				}

				if (!empty($props['settings'])) {
					$module->getSettings()->setValues($props['settings']);
				}

				if (!empty($props['acl'])) {
					$module->setAcl($props['acl']);
				}

				if (!$module->save()) {
					throw new SaveException($module);
				}

				$result['updated'][$id] = null;
			}
		}

		if (!empty($params['destroy'])) {
			$result['destroyed'] = [];
			foreach ($params['destroy'] as $id) {
				list($package, $name) = explode('/', $id);

				$module = \go\core\model\Module::findByName($package, $name, null);
				if (!$module) {
					$result['notDestroyed'][$id] = new SetError('notFound', go()->t("Item not found"));
					continue;
				}

				if ($package !== "legacy") {
					$cls = "go\\modules\\" . $package . "\\" . $name . "\Module";
					if (class_exists($cls)) {

						$mod = $cls::get();
						if (!$mod->uninstall()) {
							throw new \Exception("Failed to uninstall module '" . $name . "'");
						}
					} else {
						//remove from modules without uninstall
						\go\core\model\Module::delete(['package' => $package, 'name' => $name]);
						go()->debug(\go\core\model\Module::$lastDeleteStmt);
					}
				} else {
					$module = Module::model()->findByName($name);
					$module->delete();
				}

				$result['destroyed'][] = $id;

			}
		}

		return $result;
	}


	private function install(string $package, string $name)
	{

		if ($package != "legacy") {
			$cls = "go\\modules\\" . $package . "\\" . $name . "\Module";
			if (!class_exists($cls)) {
				throw new NotFound();
			}
			$mod = $cls::get();
			return $mod->install();

		} else {
			\GO\Base\Model\Module::install($name);
			return \go\core\model\Module::findByName($package, $name, null);
		}


	}


}