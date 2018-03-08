<?php
namespace go\modules\core\core\model;

use go\core;
use go\core\db\Query;

class Settings extends core\Settings {
	
	protected function __construct() {
		
		//temp workaround
		
		$this->language = \GO::config()->language;
		$this->title = \GO::config()->title;		
		$this->systemEmail = \GO::config()->webmaster_email;
		
		$this->smtpEncryption = \GO::config()->smtp_encryption;
		$this->smtpHost = \GO::config()->smtp_server;
		$this->smtpPort = \GO::config()->smtp_port;
		$this->smtpUsername = \GO::config()->smtp_username;
		$this->smtpPassword = \GO::config()->smtp_password;
		
		
		parent::__construct();
	}
	
	protected function getModuleId() {
		return (new Query)
			->selectSingleValue('id')
			->from('core_module')
			->where(['package' => 'core', 'name' => 'core'])
			->execute()
			->fetch();
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
	 * SMTP hostname
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
	
}
