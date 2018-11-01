<?php

namespace go\modules\core\modules\controller;

use go\core\exception\NotFound;
use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\Response;
use go\modules\core\modules\model;


class Module extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\Module::class;
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
		
		$mod = new $cls;
		$model = $mod->install();
		
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
		
		$mod = new $cls;
		$success = $mod->uninstall();
		
		Response::get()->addResponse(['success' => $success]);
	}
}
