<?php

namespace GO\Cms;


class CmsModule extends \GO\Base\Module{
	
	public function autoInstall() {
		return true;
	}
	
	public function author() {
		return 'Merijn Schering';
	}
	
	public function authorEmail() {
		return 'mschering@intermesh.nl';
	}
}
?>
