<?php
/**
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
namespace go\modules\community\tasks\controller;

use Exception;
use go\core\jmap\Entity;
use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\model\Acl;
use go\core\util\ArrayObject;
use go\modules\business\registration\Module as RegistrationModule;
use go\modules\community\tasks\model;
use go\modules\business\license\model\License;

class Task extends EntityController {

	protected function entityClass(): string
	{
		return model\Task::class;
	}

	/**
	 * @throws InvalidArguments
	 */
	public function query(array $params): ArrayObject
	{
		return $this->defaultQuery($params);
	}

	/**
	 * @throws Exception
	 */
	public function get(array $params): ArrayObject
	{
		return $this->defaultGet($params);
	}

	public function set(array $params): ArrayObject
	{
		return $this->defaultSet($params);
	}

	public function export(array $params): ArrayObject
	{
		return $this->defaultExport($params);
	}

	public function exportColumns(array $params): ArrayObject
	{
		return $this->defaultExportColumns($params);
	}
	
	public function import(array $params): ArrayObject
	{
		return $this->defaultImport($params);
	}

	public function importCSVMapping(array $params): ArrayObject
	{
		return $this->defaultImportCSVMapping($params);
	}

	public function changes(array $params): ArrayObject
	{
		return $this->defaultChanges($params);
	}

	protected function create(array $properties): Entity
	{

		$cls = $this->entityClass();

		/** @var Entity $entity */
		$entity = new $cls;

		if (isset($properties['projectId']) && empty($properties['tasklistId'])) {
			$properties['tasklistId'] = model\TaskList::createForProject($properties['projectId'])->id;
		}
		$entity->setValues($properties);

		return $entity;
	}

	public function merge(array $params): ArrayObject
	{
		return $this->defaultMerge($params);
	}

	/**
	 * Used to show counter badge for support.
	 *
	 */
	public function countMine(): int
	{

		$defaultListId = go()->getAuthState()->getUser(['tasksSettings'])->tasksSettings->getDefaultTasklistId();

		$query = model\Task::find(['id'])
			->selectSingleValue("IFNULL(count(*), 0)")
			->filter([
				"tasklistId" => $defaultListId,
				"complete" => false,
			])->filter([
				"operator" => "OR",
				"conditions" => [
					["due" => '< tomorrow'],
					["due" => null]
				]
			]);

		$query->removeJoin("tasks_task_user");
		$query->removeJoin("pr2_hours");
		$query->groupBy([]);

		return $query->single();
	}

	/**
	 * In case that time registration is available to the end user, but the tasks module is not...
	 *
	 * @throws \go\core\http\Exception
	 * @throws Exception
	 */
	protected function authenticate()
	{
		if (!go()->getAuthState()->isAuthenticated()) {
			throw new Exception(401, "Unauthorized");
		}

		$this->rights = $this->getClassRights();
		if (!$this->checkModulePermissions()) {
			$mod = \go\core\model\Module::findByClass(static::class, ['name', 'package']);
			if (License::has("groupoffice-pro")) {
				$regClsRights = go()->getAuthState()->getClassRights(RegistrationModule::class);
				if (!$regClsRights->mayRead) {
					throw new Exception(403, str_replace('{module}', ($mod->package ?? "legacy") . "/" . $mod->name, go()->t("Forbidden, you don't have access to module '{module}'.")));
				}
			} else {
				throw new Exception(403, str_replace('{module}', ($mod->package ?? "legacy") . "/" . $mod->name, go()->t("Forbidden, you don't have access to module '{module}'.")));
			}
		}
	}
}

