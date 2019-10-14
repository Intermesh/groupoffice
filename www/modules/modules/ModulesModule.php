<?php


namespace GO\Modules;


class ModulesModule extends \GO\Base\Module{
	
	public function autoInstall() {
		return false; //is installed as core module
	}
	public function adminModule() {
		return true;
	}
}
