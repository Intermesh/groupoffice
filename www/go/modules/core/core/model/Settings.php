<?php
namespace go\modules\core\core\model;

use go\core;
use go\core\http\Request;

class Settings extends core\Settings {

	public function getModuleName() {
		return 'core';
	}
	
	protected function __construct() {
		parent::__construct();
		
		if(!isset($this->URL)) {
			$this->URL = $this->detectURL();
		}
		
		if(!isset($this->language)) {
			$this->language = $this->getDefaultLanguage();
		}
	}
	
	private function getDefaultLanguage() {		
		//can't use Language here because an infite loop will occur as it depends on this model.
		if(isset($_GET['SET_LANGUAGE']) && $this->hasLanguage($_GET['SET_LANGUAGE'])) {
			return $_GET['SET_LANGUAGE'];
		}
		
		$browserLanguages= Request::get()->getAcceptLanguages();
		foreach($browserLanguages as $lang){
			$lang = str_replace('-','_',explode(';', $lang)[0]);
			if(core\Environment::get()->getInstallFolder()->getFile('go/modules/core/language/'.$lang.'.php')->exists()){
				return $lang;
			}
		}
		
		return "en";
	}
	
	
	/**
	 * Auto detects URL to Group-Office if we're running in a webserver
	 * 
	 * @return string
	 */
	private function detectURL() {
		
		if(!isset($_SERVER['SERVER_NAME'])) {
			return null;
		}		
		
		$path = '/' . trim(dirname($_SERVER['PHP_SELF']), '/');		
		$https = (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on" || $_SERVER["HTTPS"] == "1")) || !empty($_SERVER["HTTP_X_SSL_REQUEST"]);
		$protocol = $https ? 'https://' : 'http://';
		
		//trim install folder
		if(substr($path, -8) == '/install') {
			$path = substr($path, 0, -8);
		}
		
		return $protocol . $_SERVER['HTTP_HOST'] . $path;
	}

	const SMTP_ENCRYPTION_TLS = 'tls';
	const SMTP_ENCRYPTION_SSL = 'ssl';
	
	/**
	 * System default language ISO code
	 * 
	 * @var string  eg. "en"
	 */
	public $language;
	
	/**
	 * The title of the Group-Office environment
	 * 
	 * @var string
	 */
	public $title = 'Group-Office';
	
	
	/**
	 * The e-mail address for sending out system messages.
	 * 
	 * @var string
	 */
	public $systemEmail = 'admin@intermesh.dev';
	
	
	/**
	 * SMTP host name
	 * 
	 * @var string
	 */
	public $smtpHost = 'localhost';
	
	/**
	 * SMTP port
	 * 
	 * @var string
	 */
	public $smtpPort = 587;
	
	/**
	 * SMTP username
	 * @var string
	 */
	public $smtpUsername = null;
	
	/**
	 * SMTP Password
	 * @var string
	 */
	public $smtpPassword = null;

	/**
	 * Encryption to use for SMTP
	 * @var string|bool
	 */
	public $smtpEncryption = self::SMTP_ENCRYPTION_TLS;
	
	/**
	 * If set then all system notifications go to this email address
	 * 
	 * @var string 
	 */
	public $debugEmail = null;
	
	
	/**
	 * When maintenance mode is enabled, only admin users can login.
	 * @var boolean 
	 */
	public $maintenanceMode = false;
	
	/**
	 * HTML message that will show on the login screen.
	 * 
	 * @var string 
	 */
	public $loginMessage = null;
	
	
	/**
	 * Minimum password length
	 * 
	 * @var int
	 */
	public $passwordMinLength = 6;
	
	
	/**
	 * The full url to Group-Office.
	 * 
	 * @var string 
	 */
	public $URL;
	
	
	
	
}
