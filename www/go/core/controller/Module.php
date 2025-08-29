<?php

namespace go\core\controller;

use go\core\exception\Forbidden;
use go\core\exception\NotFound;
use go\core\jmap\Entity;
use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\Response;
use go\core\model;
use go\core\orm\Query;
use go\core\util\ArrayObject;


class Module extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\Module::class;
	}

	private function filterPermissions(Query $query) {

		if(go()->getAuthState()->isAdmin()) {
			return $query;
		}

		$query->join("core_permission", "p" , "p.moduleId = m.id")
			->join("core_user_group", "ug", "ug.groupId = p.groupId AND ug.userId = :userId")
			->bind(':userId', go()->getAuthState()->getUserId())
			->groupBy(['m.id']);

		return $query;
	}

	/**
	 * @param model\Module $entity
	 * @return bool
	 * @throws \Exception
	 */
	protected function canUpdate(Entity $entity): bool
	{
		return $entity->getUserRights()->mayManage ?? go()->getAuthState()->isAdmin();
	}

	protected function canCreate(Entity $entity): bool
	{
		if($entity->name == "core" && $entity->package == "core") {
			return false;
		}
		return go()->getAuthState()->isAdmin();
	}

	protected function canDestroy(Entity $entity): bool
	{
		if($entity->name == "core" && $entity->package == "core") {
			return false;
		}
		return go()->getAuthState()->isAdmin();
	}

	protected function getQueryQuery(ArrayObject $params): Query
	{
		return $this->filterPermissions(parent::getQueryQuery($params));
	}

	protected function getGetQuery(ArrayObject $params): \go\core\orm\Query
	{
		return $this->filterPermissions(parent::getGetQuery($params));
	}

	public function installLicensed(): array
	{
		$modules = \GO::modules()->getAvailableModules();

		foreach ($modules as $moduleClass) {

			$moduleController = $moduleClass::get();

			if ($moduleController->autoInstall() && $moduleController->isInstallable()) {
				if($moduleController->isInstalled()) {
					$model = $moduleController->getModel();
					$model->enabled = true;
					try {
						$model->save();
					} catch(\Throwable $e) {
						go()->log($e);
					}

				} else {
					if ($moduleController instanceof \go\core\Module) {
						if ($moduleController->requiredLicense()) {
							$moduleController->install();
						}
					} else {
						if ($moduleController->appCenter() && !\GO\Base\Model\Module::install($moduleController->name())) {
							throw new \Exception("Could not save module " . $moduleController->name());
						}
					}
				}
			}
		}

		return ['success' => true];
	}
	
	public function install($params) {

		if(!go()->getAuthState()->isAdmin()) {
			throw new Forbidden();
		}
		
		if(empty($params['package']))
		{
			throw new InvalidArguments("'package' param is required");
		}
		
		if(empty($params['name']))
		{
			throw new InvalidArguments("'name' param is required");
		}
		
		$cls = "go\\modules\\" . $params['package'] . "\\" . $params['name'] . "\Module";
		if(!class_exists($cls)) {
			throw new NotFound();
		}
		
		$mod = $cls::get();
		$model = $mod->install();

		if(!$model) {
			throw new \Exception("Failed to install module. Please check the server error log for details.");
		}

    return $this->get(['ids' => [$model->id]]);
	}
	
	public function uninstall($params) {
		if(!go()->getAuthState()->isAdmin()) {
			throw new Forbidden();
		}

		if(empty($params['package']))
		{
			throw new InvalidArguments("'package' param is required");
		}
		
		if(empty($params['name']))
		{
			throw new InvalidArguments("'name' param is required");
		}
		
		$cls = "go\\modules\\" . $params['package'] . "\\" . $params['name'] . "\Module";
		if(class_exists($cls)) {

			$mod = $cls::get();
			$success = $mod->uninstall();
		} else {
			//remove from modules without uninstall
			$success = model\Module::delete(['package' => $params['package'] == 'legacy' ? null : $params['package'], 'name' => $params['name']]);
			go()->debug(model\Module::$lastDeleteStmt);
		}
		
		return ['success' => $success];
	}
	
	/**
	 * Handles the Foo entity's Foo/query command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/query
	 */
	public function query($params) {
		return $this->defaultQuery($params);
	}
	
	/**
	 * Handles the Foo entity's Foo/get command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/get
	 */
	public function get($params) {
		return $this->defaultGet($params);
	}
	
	/**
	 * Handles the Foo entity's Foo/set command
	 * 
	 * @see https://jmap.io/spec-core.html#/set
	 * @param array $params
	 */
	public function set($params) {
		return $this->defaultSet($params);
	}
	
	
	/**
	 * Handles the Foo entity's Foo/changes command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/changes
	 */
	public function changes($params) {
		return $this->defaultChanges($params);
	}
}
