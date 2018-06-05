<?php


namespace GO\Cron;


class CronModule extends \GO\Base\Module{
	
	public function author() {
		return 'Wesley Smits';
	}
	
	public function authorEmail() {
		return 'wsmits@intermesh.nl';
	}

	public function adminModule() {
		return true;
	}
	
	public function autoInstall() {
		return true;
	}
	
}
