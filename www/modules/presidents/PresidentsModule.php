<?php
/**
 * This module is intended for developers as an example for basic CRUD functions 
 */

namespace GO\Presidents;


class PresidentsModule extends \GO\Base\Module{
	
	public function autoInstall() {
		return false;
	}
	
	public function author() {
		return 'Merijn Schering';
	}
	
	public function authorEmail() {
		return 'mschering@intermesh.nl';
	}
}

?>
