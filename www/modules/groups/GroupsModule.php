<?php


namespace GO\Groups;


class GroupsModule extends \GO\Base\Module{
	
	public function autoInstall() {
		return false; //is installed as core module!
	}
	public function adminModule() {
		return true;
	}
}
