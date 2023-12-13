<?php
namespace go\core\model;

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Exception;
use go\core;
use go\core\exception\Forbidden;
use go\core\jmap\Request as JmapRequest;
use go\core\util\Crypt;
use go\modules\business\license\model\License;
use go\modules\community\addressbook\model\AddressBook;

class Settings extends core\Settings {

	use core\validate\ValidationTrait;

	/**
	 * @throws Exception
	 */
	protected function __construct() {
		parent::__construct();

		$save = false;

		if(!isset($this->URL)) {
			$this->URL = $this->detectURL();
			$save = true;
		}

		if(!isset($this->language)) {
			$this->language = $this->getDefaultLanguage();
			$save = true;
		}

		if($save) {
			try {
				$this->save();
			}catch(Exception $e) {

				//ignore error on install because core module is not there yet
				if(!core\Installer::isInProgress()) {
					throw $e;
				}
			}
		}
	}

	private function hasLanguage(string $lang): bool
	{
		return core\Environment::get()->getInstallFolder()->getFile('go/modules/core/language/'.$lang.'.php')->exists();
	}

	private function getDefaultLanguage() {
		//can't use Language here because an infinite loop will occur as it depends on this model.
		if(isset($_GET['SET_LANGUAGE']) && $this->hasLanguage($_GET['SET_LANGUAGE'])) {
			return $_GET['SET_LANGUAGE'];
		}

		$browserLanguages= JmapRequest::get()->getAcceptLanguages();
		foreach($browserLanguages as $lang){
			$lang = str_replace('-','_',explode(';', $lang)[0]);
			if($this->hasLanguage($lang)){
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
	private function detectURL(): ?string
	{

		//check if this is ran on a webserver
		if(!isset($_SERVER['REQUEST_METHOD'])) {
			return null;
		}

		$path = dirname($_SERVER['SCRIPT_NAME']); // /index.php or /install/*.php

		if(basename($path) == 'install') {
			$path = dirname($path);
		}

		$url = JmapRequest::get()->isHttps() ? 'https://' : 'http://';
		$url .= JmapRequest::get()->getHost(false) . $path;

		return $url;
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

	/**
	 * Global SMTP timeout value in seconds
	 *
	 * Also used for the e-mail module.
	 *
	 * @var int
	 */
	public $smtpTimeout = 30;


	/**
	 * @throws Exception
	 */
	public function decryptSmtpPassword(): ?string
	{
		return $this->smtpPassword ? Crypt::decrypt($this->smtpPassword) : null;
	}

	/**
	 * @throws EnvironmentIsBrokenException
	 */
	public function setSmtpPassword(?string $value) {
		$this->smtpPassword = empty($value) ? null : Crypt::encrypt($value);
	}
	
	
	protected $locale;

	/**
	 * Get locale for the system. We need a UTF8 locale so command line functions
	 * work with UTF8.
	 *
	 * initialized in old framework GO.php. What should we do with it later?
	 *
	 * @return string
	 * @throws Forbidden
	 */
	public function getLocale(): string
	{

		if(go()->getInstaller()->isInProgress()) {
			return 'C.UTF-8';
		}
		
		if(isset($this->locale)) {
			return $this->locale;
		}
		
		try {
			if(function_exists("exec")) {
				exec('locale -a', $output);

				if (isset($output) && is_array($output)) {
					foreach ($output as $locale) {
						if (stripos($locale, 'utf') !== false) {
							$this->locale = $locale;
							$this->save();
							return $this->locale;
						}
					}
				}
			}
		} catch(Exception $e) {
			go()->debug("Could not determine locale");
		}

		//This locale is often installed so try to fallback on C.UTF8
		$this->locale = "C.UTF8";
		$this->save();		
		
		return $this->locale;
	}
	
	public function setLocale($locale) {
		$this->locale = $locale;
	}

	/**
	 * @throws Forbidden
	 */
	public function resetLocale(): string
	{
		$this->locale = null;
		return $this->getLocale();
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
	 * When maintenance mode is enabled, only admin users can login.
	 * @var boolean 
	 */
	public $maintenanceMode = false;

	/**
	 * When true the user interface will show a confirm dialog before moving item with drag and drop
	 * @var bool
	 */
	public $defaultConfirmOnMove = false;
	
	
	/**
	 * Enable HTML message that will show on the login screen.
	 * 
	 * @var string 
	 */
	public $loginMessageEnabled = false;
	
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
	 * Number of seconds to auto logout the user if inactive.
	 * Also disables the "remember login" feature as it would make no sense.
	 * @var int
	 */
	public $logoutWhenInactive = 0;
	
	
	/**
	 * Default domain name to append to username for authentication
	 * 
	 * @var string
	 */
	public $defaultAuthenticationDomain;

	/**
	 * An alternative URL to handle lost passwords
	 *
	 * @var string
	 */
	public $lostPasswordURL;
	
	
	/**
	 * The full URL to Group-Office. With trailing /.
	 * 
	 * eg. https://my.groupoffice.net/
	 *
	 * Alternatively to generate a URL based on the request you can use:
	 *
	 * @example
	 * ```
	 * Extjs3::get()->getBaseUrl();
	 * ```
	 *
	 * @var string
	 */
	public $URL;

	/**
	 * @var string
	 */
	protected $corsAllowOrigin = "";

	public function setCorsAllowOrigin($origins) {
		if(empty($origins)) {
			$this->corsAllowOrigin = "";
		} else{
			$origins = array_map(function($host) {
				return rtrim($host, '/');
			}, $origins);

			$this->corsAllowOrigin = implode(" ", $origins);
		}
	}

	public function getCorsAllowOrigin() : array {
		return empty($this->corsAllowOrigin) ? [] : explode(" ", $this->corsAllowOrigin);
	}


	/** @var bool Allow registration via the auth.php endpoint */
	public $allowRegistration = false;


	/**
	 * Keep log in core_change for this number of days.
	 *
	 * When a client has not logged in for this period the sync data will be deleted and resynchronized.
	 *
	 * @var int
	 */
	public $syncChangesMaxAge = 30;

	/**
	 * This variable is checked against the code version.
	 * If it doesn't match /install/upgrade.php will be executed.
	 *
	 * @var string
	 */
	public $databaseVersion;


	/** @var int Time the db cache was cleared. The client will invalidate it's indexeddb cache when this changes.
	 */
	public $cacheClearedAt;
	
	/** @var string Primary color in html notation 000000; */
	public $primaryColor;

	/** @var string Secondary color in html notation 000000; */
	public $secondaryColor;

	/** @var string Secondary color in html notation 000000; */
	public $tertiaryColor;

	/** @var string Secondary color in html notation 000000; */
	public $accentColor;
	
	/** @var string Blob ID for the logo */
	public $logoId;


	/** @var string Primary color in html notation 000000; */
	public $primaryDark;

	/** @var string Secondary color in html notation 000000; */
	public $secondaryDark;

	/** @var string Secondary color in html notation 000000; */
	public $tertiaryDark;

	/** @var string Secondary color in html notation 000000; */
	public $accentDark;

	/** @var string Blob ID for the logo */
	public $logoIdDark;

	/**
	 * Get's the transparent color based on the primary color.
	 * 
	 * @return ?string
	 */
	public function getPrimaryColorTransparent($theme = 'Color'): ?string
	{
		if(!isset($this->{'primary'.$theme})) {
			return null;
		}
		list($r, $g, $b) = sscanf($this->{'primary'.$theme}, "%02x%02x%02x");
		
		return "rgba($r, $g, $b, .16)";
	}

	public function printCssVars($theme = 'Color') {
		$str = !empty($this->{'primary'.$theme}) ? '--c-primary-tp: '.$this->getPrimaryColorTransparent($theme).';' : '';
		foreach(['primary', 'secondary', 'tertiary', 'accent'] as $type) {
			if(!empty($this->{$type.$theme})) $str .= '--c-'.$type.': #'.$this->{$type.$theme}.';';
		}
		return $str;
	}

	/**
	 * Default time zone for users
	 * 
	 * @var string
	 */
	public $defaultTimezone = "Europe/Amsterdam";
	
	/**
	 * Default date format for users
	 * 
	 * @link https://secure.php.net/manual/en/function.date.php
	 * @var string
	 */
	public $defaultDateFormat = "d-m-Y";
	
	/**
	 * Default time format for users
	 * 
	 * @link https://secure.php.net/manual/en/function.date.php
	 * @var string 
	 */
	public $defaultTimeFormat = "G:i";
	
	/**
	 * Default currency
	 * @var string
	 */
	public $defaultCurrency = "€";
	
	/**
	 * Default first week day
	 * 
	 * 0 = sunday
	 * 1 = monday
	 * 
	 * @var int 
	 */
	public $defaultFirstWeekday = 1;
	
	
	/**
	 * The default address book for new users
	 * @var int 
	 */
	public $userAddressBookId = null;


	private $userAddressBook;

	/**
	 * @return AddressBook
	 * @throws Exception
	 */
	public function userAddressBook(): ?AddressBook
	{
		if(!Module::findByName('community', 'addressbook')) {
			return null;
		}

		if(isset($this->userAddressBook)) {
			return $this->userAddressBook;
		}
		
		if(isset($this->userAddressBookId)) {
			$this->userAddressBook = AddressBook::findById($this->userAddressBookId);
		} else{
			$this->userAddressBook = null;
		}

		if(!$this->userAddressBook) {
			go()->getDbConnection()->beginTransaction();
			$this->userAddressBook = new AddressBook();
			$this->userAddressBook->name = go()->t("Users");

			if(!$this->userAddressBook->save()) {
				throw new Exception("Could not save address book");
			}
			$this->userAddressBookId = $this->userAddressBook->id;

			//Share users address book with internal
			$this->userAddressBook->findAcl()->addGroup(Group::ID_INTERNAL)->save();
			if(!$this->save()) {
				throw new Exception("Could not save core settings");
			}
			go()->getDbConnection()->commit();
		}

		return $this->userAddressBook;
	}

	/**
	 * @var int
	 */
	public $archivedUsersAddressBook;
	/**
	 * When archiving a user, move profile user
	 *
	 * @return AddressBook | null
	 * @throws Exception
	 */

	public function archivedUsersAddressBook()
	{
		if(!Module::findByName('community', 'addressbook')) {
			return null;
		}

		$ab = isset($this->archivedUsersAddressBook) ? AddressBook::findById($this->archivedUsersAddressBook) : null;

		if (!$ab) {
			go()->getDbConnection()->beginTransaction();
			$ab = new AddressBook();
			$ab->name = go()->t("Archived users");
			if(!$ab->save()) {
				throw new Exception("Could not save address book");
			}
			$this->archivedUsersAddressBook = $ab->id;

			//Share users address book with admins only
			$ab->findAcl()->addGroup(Group::ID_ADMINS)->save();
			if(!$this->save()) {
				throw new Exception("Could not save core settings");
			}
			go()->getDbConnection()->commit();
		}

		return $ab;
	}

	
	
	/**
	 * Default list separator for import and export
	 * 
	 * @var string
	 */
	public $defaultListSeparator = ';';
	
	/**
	 * Default text separator for import and export
	 * 
	 * @var string
	 */
	public $defaultTextSeparator = '"';
	
	/**
	 * Default thousands separator for numbers
	 * @var string
	 */
	public $defaultThousandSeparator = '.';
	
	/**
	 * Default decimal separator for numbers
	 * 
	 * @var string
	 */
	public $defaultDecimalSeparator = ',';	
	
	/**
	 * Default setting for users to have short date and times in lists.
	 * @var boolean
	 */
	public $defaultShortDateInList = true;


	/**
	 * License for Group-Office
	 *
	 * @var string
	 */
	public $license = null;

	/**
	 * Set to true when the license dialog has been presented and the user denied.
	 *
	 * @var bool
	 */
	public $licenseDenied = false;


	/**
	 * Set to true when the welcome dialog has been presented and the user denied.
	 *
	 * @var bool
	 */
	public $welcomeShown = false;


	/**
	 *
	 * @var bool
	 */
	public $demoDataAsked = false;


	private $defaultGroups;
	
	/**
	 * New users will be member of these groups
	 * 
	 * @return int[]
	 */
	public function getDefaultGroups(): array
	{
		if(!isset($this->defaultGroups)) {
			$this->defaultGroups = array_map("intval", (new core\db\Query)
				->selectSingleValue('groupId')
				->from("core_group_default_group")
				->all());

			go()->getCache()->set(static::class, $this);
		}

		return $this->defaultGroups;

	}

	/**
	 * Set default groups for new groups
	 *
	 * @param array $groups eg [['groupId' => 1]]
	 * @throws Exception
	 */
	public function setDefaultGroups(array $groups) {
		
		go()->getDbConnection()->exec("TRUNCATE TABLE core_group_default_group");
		
		foreach($groups as $groupId) {
			if(!go()->getDbConnection()->insert("core_group_default_group", ['groupId' => $groupId])->execute()) {
				throw new Exception("Could not save group id ".$groupId);
			}
		}
	}


	// SYNCHRONISATION SETTINGS
	/**
	 * when true user will get popup to allow its own device.
	 */
	public $activeSyncEnable2FA = false;
	/**
	 * When false administrator has to enable each new device
	 */
	public $activeSyncCanConnect = true;
	
	
	public function save(): bool
	{
		if(!$this->validate()){
			return false;
		}

		if(isset($this->logoId)) {
			//todo settings should have real columns with real keys?
			$blob = core\fs\Blob::findById($this->logoId);
			if($blob && isset($blob->staleAt)) {
				$blob->staleAt = null;
				$blob->save();
			}
		}
		
		//for old framework config caching in GO\Base\Config
		if(isset($_SESSION)) {
			unset($_SESSION['GO_SESSION']['newconfig']);
		}
		
		//Make sure URL has trailing slash
		if(isset($this->URL)) {
			$this->URL = rtrim($this->URL, '/ ').'/';
		}

		if($this->isModified('maintenanceMode') && $this->maintenanceMode) {
			Token::logoutEveryoneButAdmins();
		}
		
		return parent::save();
	}

	/**
	 * @throws Exception
	 */
	protected function internalValidate()
	{
		if($this->isModified('license')) {
			if(isset($this->license)) {
				$data = License::getLicenseData();
				if (!$data) {
					throw new Exception("License data was corrupted");
				}

				// force validation because the license was just replaced
				if (!License::validate($data, true)) {
					throw new Exception(License::$validationError);
				}
			}

			if(go()->getInstaller()->disableUnavailableModules()){
				go()->rebuildCache();
			}
		}
	}
}
