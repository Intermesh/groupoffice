<?php

namespace go\core\auth\model;

use Exception;
use go\core\acl\model\Acl;
use go\core\App;
use go\core\auth\Method;
use go\core\auth\Password;
use go\core\db\Query;
use go\core\orm\Entity;
use go\core\validate\ErrorCode;

/**
 * todo
 * 
 * $qs[] = "ALTER TABLE `core_user` CHANGE `lastlogin` `_lastlogin` INT(11) NOT NULL DEFAULT '0';";
$qs[] = "ALTER TABLE `core_user` ADD `lastLogin` DATETIME NULL DEFAULT NULL AFTER `force_password_change`, ADD `modifiedAt` DATETIME NULL DEFAULT NULL AFTER `lastLogin`, ADD `createdAt` DATETIME NULL DEFAULT NULL AFTER `modifiedAt`;";
$qs[] = "update `core_user` set modifiedAt=from_unixtime(mtime), createdAt =from_unixtime(ctime), lastLogin = from_unixtime(_lastlogin);";
$qs[] = "ALTER TABLE `core_user`
  DROP `_lastlogin`,
  DROP `ctime`,
  DROP `mtime`;";
 */
class User extends Entity {
	
	const DIGEST_REALM = 'Group-Office';

	const ID_SUPER_ADMIN = 1;
	
	const PASSWORD_MIN_LENGTH = 8;

	/**
	 * The ID
	 * 
	 * @var int
	 */
	public $id;

	/**
	 * Username eg. "john"
	 * @var string
	 */
	public $username;

	/**
	 * Display name eg. "John Smith"
	 * @var string
	 */
	public $displayName;

	public $enabled;
	/**
	 * E-mail address
	 * 
	 * @var string
	 */
	public $email;

	/**
	 * Alternative e-mail address for password reset
	 * 
	 * @var string
	 */
	public $recoveryEmail;
	protected $recoveryHash;
	protected $recoverySendAt;
	
	/**
	 * Login count
	 * 
	 * @var int
	 */
	public $logins;
	
	public $lastlogin;
	
//	/**
//	 *
//	 * @var \go\core\util\DateTime
//	 */
//	public $lastLogin;
//	
//	/**
//	 *
//	 * @var \go\core\util\DateTime
//	 */
//	public $modifiedAt;
//	
//	/**
//	 *
//	 * @var \go\core\util\DateTime
//	 */
//	public $createdAt;
	
	
	public $date_format;
	public $time_format;
	public $thousands_seporator;
	public $decimal_separator;
	public $currency;
	
	
	public $max_rows_list;
	public $timezone;
	public $start_module;
	public $language;
	public $theme;
	public $first_weekday;
	public $sort_name;
	
	public $mute_sound;
	public $mute_reminder_sound;
	public $mute_new_mail_sound;
	public $show_smilies;
	public $auto_punctuation;
	
	public $list_separator;
	public $text_separator;
	private $files_folder_id;
	public $disk_quota;
	public $disk_usage;
	
	public $mail_reminders;
	public $popup_reminders;
	public $popup_emails;
	public $holidayset;
	public $sort_email_Addresses_by_time;
	public $no_reminders;
	
	private $last_password_change;
	public $force_password_change;
	
	
	public function getDateFormat() {
		return $this->date_format;
	}
	
	public function getTimeFormat() {
		return $this->time_format;
	}
	
	public function getDateTimeFormat() {
		return $this->getDateFormat() .' '. $this->time_format;
	}

	/**
	 *
	 * @var Password
	 */
	protected $password;
	protected $digest;

	/**
	 * The groups of the user
	 * 
	 * @var UserGroup[]
	 */
	public $groups;
	
	/**
	 * Changed to false in setValues() so when the the jmap api is used it needs to be verified
	 * @var bool 
	 */
	private $passwordVerified = true;

	protected static function defineMapping() {
		return parent::defineMapping()
			->addTable('core_user', 'u')
			->addTable('core_auth_password', 'p', ['id' => 'userId'])
//			->addRelation('password', Password::class, ['id' => 'userId'], false)
			->addRelation("groups", UserGroup::class, ['id' => 'userId']);
	}
	
	public function setValues(array $values) {
		$this->passwordVerified = false;
		return parent::setValues($values);
	}
	
	

	public function setPasswordConfirm($passwordConfirm){
		return true;
	}
	
	public function setCurrentPassword($currentPassword){
		$this->currentPassword = $currentPassword;
		
		if(!$this->checkPassword($currentPassword)) {
			$this->setValidationError("currentPassword", ErrorCode::INVALID_INPUT);
		}
	}
		
	/**
	 * Check if the password is correct for this user.
	 * 
	 * @param string $password
	 * @return boolean 
	 */
	public function checkPassword($password) {		
		$this->passwordVerified = password_verify($password, $this->password);
		
		if($this->passwordVerified){
			$this->updateDigest($password);
		}
		return $this->passwordVerified;
	}
	
	private $plainPassword;

	public function setPassword($password) {
		$this->plainPassword = $password;		
		
	}

	private function updateDigest() {
		$digest = md5($this->username . ":" . self::DIGEST_REALM . ":" . $this->password);
		if ($digest != $this->digest) {
			$this->digest = $digest;
		}
	}

	/**
	 * Make sure to call this when changing the password with a recovery hash
	 * @param string $hash
	 */
	public function checkRecoveryHash($hash) {
		if($hash === $this->recoveryHash) {
			$this->passwordVerified = true;
			$this->recoveryHash = null;
		}
	}
	
	private function validatePasswordChange() {		
		
		if($this->passwordVerified) {
			return true;
		}
		
		if(!$this->isModified(['password']) || $this->getOldValue('password') == null) {
			return true;
		}
		
		if(App::get()->getInstaller()->isInProgress()) {
			return true;
		} 
		
		$authState = App::get()->getAuthState();
		if(!$authState) {
			return false;
		}
		if(!$authState->isAuthenticated()) {
			return false;
		}						
		
		return App::get()->getAuthState()->getUser()->isAdmin();
		
	}
	
	protected function internalValidate() {
		
		if(!$this->validatePasswordChange()) {
			if(!$this->hasValidationErrors('currentPassword')) {
				$this->setValidationError('currentPassword', ErrorCode::REQUIRED);
			}
		}
		
		if(isset($this->plainPassword)) {
			if(strlen($this->plainPassword) < self::PASSWORD_MIN_LENGTH) {
				$this->setValidationError('password', ErrorCode::INVALID_INPUT, "Minimum password length is 8 chars");
			}
		}
		
		return parent::internalValidate();
	}

	

	public function hasPermissionLevel($level = Acl::LEVEL_READ) {
		return $this->id == App::get()->getAuthState()->getUser()->id || App::get()->getAuthState()->getUser()->isAdmin();
	}

	/**
	 * Check if use is an admin
	 * 
	 * @return boolean
	 */
	public function isAdmin() {
		return (new Query)
			->select('*')
			->from('core_user_group')
			->where(['groupId' => Group::ID_ADMINS, 'userId' => $this->id])->single() !== false;
	}
	
	/**
	 * Alias for making isAdmin() a public property
	 * @return bool
	 */
	public function getIsAdmin() {
		return $this->isAdmin();
	}

	/**
	 * Get available authentication methods
	 * 
	 * @return Method[]
	 */
	public function getAuthenticationMethods() {

		$methods = [];

		$authMethods = Method::find()->orderBy(['sortOrder' => 'ASC']);

		foreach ($authMethods as $authMethod) {
			$authenticator = $authMethod->getAuthenticator();

			if ($authenticator && $authenticator::isAvailableFor($this->username)) {
				$methods[] = $authMethod;
			}
		}

		return $methods;
	}
	
	public function sendRecoveryMail($to){
		
		$this->recoveryHash = bin2hex(random_bytes(20));
		$this->recoverySendAt = new \DateTime();
		
		$message = GO()->getMailer()->compose()	  
			->setFrom(GO()->getSettings()->systemEmail, GO()->getSettings()->title)
			->setTo(!empty($to) ? $to : $this->recoveryEmail, $this->displayName)
			->setSubject(GO()->t('Lost password','core','lostpassword'))
			->setBody(strtr(GO()->t('recoveryMailBody','core','lostpassword'),[
				':displayName' => $this->displayName,
				':username' => $this->username,
				':resetLink' => \go\core\Environment::get()->getWebClientUrl().'#recover/'.$this->recoveryHash
			]), 'text/html');
		
		return $this->save() && $message->send();
	}
	
	protected function internalSave() {
		
		if(isset($this->plainPassword)) {
			$this->password = password_hash($this->plainPassword, PASSWORD_DEFAULT);
			$this->updateDigest($this->plainPassword);
		}
		
		if(!parent::internalSave()) {
			return false;
		}
		
		//create users' group
		if(!$this->isNew()) {
			return true;
		}
		
		$group = new Group();
		$group->name = $this->username;
		$group->isUserGroupFor = $this->id;
		return $group->save();
	}
	
	
	/**
	 * Check if this user has a module
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public function hasModule($name) {
		$module = \go\core\module\model\Module::find()->where(['name' => $name])->single();
		if(!$module) {
			return false;
		}
		
		if(!Acl::getPermissionLevel($module->aclId, $this->id)) {
			return false;
		}
		
		return true;		
	}

}
