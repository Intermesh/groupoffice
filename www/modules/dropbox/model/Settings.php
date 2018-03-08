<?php


namespace GO\Dropbox\Model;


class Settings extends \GO\Base\Model\AbstractSettingsCollection{

	public $app_key;
	public $app_secret;

	public function myPrefix() {
		return 'dropbox_';
	}
}