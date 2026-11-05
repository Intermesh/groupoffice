<?php
namespace GO\Base\Exception;

use go\core\exception\UserSafeException;

class NoCron extends \Exception  implements UserSafeException{
	
	public function __construct($message=null,$code=0,$errorInfo=null) {
		
		$message = "The main cron job doesn't appear to be running. Please add a cron job: \n\n* * * * * www-data php ".\GO::config()->root_path."cron.php ".\GO::config()->get_config_file();
		
		parent::__construct($message);
	}
	
}
