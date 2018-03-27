<?php
namespace go\modules\core\core\model;

use go\core;
use go\core\db\Query;

class Settings extends core\Settings {

	public function getModuleName() {
		return 'core';
	}

	const SMTP_ENCRYPTION_TLS = 'tls';
	const SMTP_ENCRYPTION_SSL = 'ssl';
	
	/**
	 * System default language ISO code
	 * 
	 * @var string  eg. "en"
	 */
	public $language = 'en';
	
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
	
	
	
	
}
