<?php


namespace GO\Users\Model;


class Settings extends \GO\Base\Model\AbstractSettingsCollection{

	public $register_email_subject;
	public $register_email_body;
	
	public $globalsettings_show_tab_addresslist;	
	
	public function myPrefix() {
		return '';
	}
	
}