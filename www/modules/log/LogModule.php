<?php

namespace GO\Log;


class LogModule extends \GO\Base\Module{
	/**
	 * Initialize the listeners for the ActiveRecords
	 */
	public static function initListeners(){	
		$c = new \GO\Core\Controller\MaintenanceController();
		$c->addListener('servermanagerReport', 'GO\Log\LogModule', 'rotateLog');

	}	

	public function adminModule() {
		return true;
	}

	public static function rotateLog(){

		echo "Running log rotate for ".\GO::config()->id."\n";		
		$controller = new Controller\LogController();
		$controller->run("rotate");			
	}
}
