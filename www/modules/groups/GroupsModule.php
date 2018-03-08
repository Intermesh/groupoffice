<?php


namespace GO\Groups;


class GroupsModule extends \GO\Base\Module{
	
	public function autoInstall() {
		return true;
	}
	public function adminModule() {
		return true;
	}
}