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
use go\modules\community\tasks\model;

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
	 * @return mixed|null
	 * @throws Exception
	 */
	public function countMine(array $params) {

		if(empty($params) || $params['role'] == "support") {
			$query = model\Task::find(['id'])
				->selectSingleValue("count(*)")
				->filter([
					"operator" => "OR",
					"conditions" => [
						["responsibleUserId" => go()->getUserId()],
						["responsibleUserId" => null]
					]
				])
				->filter([
					"permissionLevel" => Acl::LEVEL_WRITE,
					"progress" => "needs-action",
					"role" => "support"
				]);
		} else{
			$defaultListId = go()->getAuthState()->getUser(['tasksSettings'])->tasksSettings->getDefaultTasklistId();

			$query = model\Task::find(['id'])
				->selectSingleValue("count(*)")
				->filter([
					"tasklistId" => $defaultListId,
					"complete" => false,
					'due' => '< tomorrow'
				]);

		}

		$query->removeJoin("tasks_task_user");
		$query->removeJoin("pr2_hours");
		$query->groupBy([]);


		return $query->single();
	}
}

