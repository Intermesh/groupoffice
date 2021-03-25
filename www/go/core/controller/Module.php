<?php

namespace go\core\controller;

use go\core\exception\NotFound;
use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\Response;
use go\core\model;


class Module extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\Module::class;
	}
	
		
	protected function getQueryQuery($params)
	{
		return parent::getQueryQuery($params)->orderBy(['sort_order' => 'ASC']);
	}

	protected function getGetQuery($params)
	{
		return parent::getGetQuery($params)->orderBy(['sort_order' => 'ASC']);
	}

	public function installLicensed(){
		$modules = \GO::modules()->getAvailableModules();

		foreach ($modules as $moduleClass) {

			$moduleController = $moduleClass::get();

			if ($moduleController->autoInstall() && !$moduleController->isInstalled() && $moduleController->isInstallable()) {
				if ($moduleController instanceof \go\core\Module) {
					if($moduleController->requiredLicense()) {
						$moduleController->install();
					}
				} else {
					if ($moduleController->appCenter() && !\GO\Base\Model\Module::install($moduleController->name())) {
						throw new \Exception("Could not save module " . $moduleController->name());
					}
				}
			}
		}

		return ['success' => true];
	}
	
	public function install($params) {
		
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
			throw new \Exception("Failed to install");
		}

    return $this->get(['ids' => [$model->id]]);
	}
	
	public function uninstall($params) {
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
		$success = $mod->uninstall();
		
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
