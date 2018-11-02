<?php


namespace GO\Demodata;


class DemodataModule extends \GO\Base\Module {

	public function author() {
		return 'Merijn Schering';
	}

	public function authorEmail() {
		return 'mschering@intermesh.nl';
	}

	public function autoInstall() {
		return true;
	}

}
