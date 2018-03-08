<?php


namespace GO\Modules;


class ModulesModule extends \GO\Base\Module{
	
	public function autoInstall() {
		return true;
	}
	public function adminModule() {
		return true;
	}
}