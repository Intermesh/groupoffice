<?php


namespace GO\Base\Export;


class Settings extends \GO\Base\Model\AbstractSettingsCollection{

	public $export_include_headers;
	public $export_human_headers;
	public $export_include_hidden;
	
	public function myPrefix() {
		return '';
	}
			
}
