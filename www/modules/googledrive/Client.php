<?php
namespace GO\Googledrive;

require_once \GO::config()->root_path.'modules/googledrive/google-api-php-client/src/Google_Client.php';
require_once \GO::config()->root_path.'modules/googledrive/google-api-php-client/src/contrib/Google_DriveService.php';

class Client extends \Google_Client{
	public function __construct($config = array()) {
		parent::__construct($config);
		
		if(empty(\GO::config()->googledrive_oauth2_client_id) || empty(\GO::config()->googledrive_oauth2_client_secret))
			throw new \Exception("Google drive API client not setup. \$config['googledrive_oauth2_client_id'], \$config['googledrive_oauth2_client_secret'] must be set.");
		
		$this->setApplicationName(\GO::config()->title);
		$this->setUseObjects(true);
		$this->setClientId(\GO::config()->googledrive_oauth2_client_id);
		$this->setClientSecret(\GO::config()->googledrive_oauth2_client_secret);
		
//		$this->setDeveloperKey(\GO::config()->googledrive_simple_api_key);
	}
}
