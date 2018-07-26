<?php
namespace go\modules\core\core\model;

use go\core;
use go\core\http\Request;

class Settings extends core\Settings {
	
	protected function __construct() {
		parent::__construct();
		
		if(!isset($this->URL)) {
			$this->URL = $this->detectURL();
			$this->save();
		}
		
		if(!isset($this->language)) {
			$this->language = $this->getDefaultLanguage();
			$this->save();
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
	 * 
	 * @var string
	 */
	protected $smtpPassword = null;
	
	
	public function getSmtpPassword() {
		return \go\core\util\Crypt::decrypt($this->smtpPassword);
	}
	
	public function setSmtpPassword($value) {
		$this->smtpPassword = \go\core\util\Crypt::encrypt($value);
	}

	/**
	 * Encryption to use for SMTP
	 * @var string|bool
	 */
	public $smtpEncryption = self::SMTP_ENCRYPTION_TLS;
	
	/**
	 * Set to false to ignore certificate errors. 
	 * 
	 * @var boolean
	 */
	public $smtpEncryptionVerifyCertificate = true;
	
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
	 * The full URL to Group-Office.
	 * 
	 * @var string 
	 */
	public $URL;
	
	/**
	 * This variable is checked against the code version.
	 * If it doesn't match /install/upgrade.php will be executed.
	 * 
	 * @var string
	 */
	public $databaseVersion;
	
	
	/**
	 * Primary color in html notation 000000;
	 * 
	 * @var string
	 */
	public $primaryColor;
	
	/**
	 * Blob ID for the logo
	 * 
	 * @var string
	 */
	public $logoId;
	
	
	/**
	 * Get's the transparent color based on the primary color.
	 * 
	 * @return string
	 */
	public function getPrimaryColorTransparent() {
		list($r, $g, $b) = sscanf($this->primaryColor, "%02x%02x%02x");
		
		return "rgba($r, $g, $b, .16)";
	}
	
	public function save() {
		
		//for old framework config caching in GO\Base\Config
		if(isset($_SESSION)) {
			unset($_SESSION['GO_SESSION']['newconfig']);
		}
		
		//Make sure URL has trailing slash
		if(isset($this->URL)) {
			$this->URL = rtrim($this->URL, '/ ').'/';
		}
		
		return parent::save();
	}
}
