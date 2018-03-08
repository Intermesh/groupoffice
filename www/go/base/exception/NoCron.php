<?php
namespace GO\Base\Exception;

class NoCron extends \Exception{
	
	public function __construct($message=null,$code=0,$errorInfo=null) {
		
		$message = "The main cron job doesn't appear to be running. Please add a cron job: \n\n* * * * * www-data php ".\GO::config()->root_path."groupofficecli.php -c=".\GO::config()->get_config_file()." -r=core/cron/run -q > /dev/null";
		
		parent::__construct($message);
	}
	
}