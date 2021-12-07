<?php
/**
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
namespace go\modules\community\tasks\controller;

use go\core\jmap\Entity;
use go\core\jmap\EntityController;
use go\modules\community\tasks\model;

class Task extends EntityController {

	protected function entityClass(): string
	{
		return model\Task::class;
	}

	public function query($params)
	{
		return $this->defaultQuery($params);
	}

	public function get($params) {
		return $this->defaultGet($params);
	}

	public function set($params) {
		return $this->defaultSet($params);
	}

	public function export($params) {
		return $this->defaultExport($params);
	}

	public function exportColumns($params) {
		return $this->defaultExportColumns($params);
	}
	
	public function import($params) {
		return $this->defaultImport($params);
	}

	public function importCSVMapping($params) {
		return $this->defaultImportCSVMapping($params);
	}

	public function changes($params) {
		return $this->defaultChanges($params);
	}

	protected function create(array $properties): \go\core\jmap\Entity
	{

		$cls = $this->entityClass();

		/** @var Entity $entity */
		$entity = new $cls;

		if (isset($properties['projectId']) && empty($properties['tasklistId'])) {
			$properties['tasklistId'] = model\Tasklist::createForProject($properties['projectId'])->id;
		}
		$entity->setValues($properties);

		return $entity;
	}
}

