<?php


namespace GO\Zpushadmin\Model;


class Settings extends \GO\Base\Model\AbstractSettingsCollection{

	public $zpushadmin_can_connect;

	public function myPrefix() {
		return '';
	}
	
}
