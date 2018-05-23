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
	
	
	public $validatePassword = true;

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
	
	public $avatarId;

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
	protected $files_folder_id;
	public $disk_quota;
	public $disk_usage;
	
	public $mail_reminders;
	public $popup_reminders;
	public $popup_emails;
	public $holidayset;
	public $sort_email_Addresses_by_time;
	public $no_reminders;
	
	protected $last_password_change;
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
	public $groups = [];
	
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
	
	/**
	 * Get the personal group object of this user
	 * 
	 * @return Group
	 */
	public function getGroup(){
		return Group::find()->where(['isUserGroupFor'=>$this->id])->single();
	}
	
	protected function init() {
		parent::init();
		
		if($this->isNew()) {
			$s = \go\modules\core\users\model\Settings::get();
			$this->time_format = $s->defaultTimeFormat;	
			$this->date_format = $s->defaultDateFormat;
			$this->timezone = $s->defaultTimezone;
			$this->first_weekday = $s->defaultFirstWeekday;

			$this->currency = $s->defaultCurrency;
			
			foreach($s->getDefaultGroups() as $v) {
				$this->groups[] = (new UserGroup)->setValues($v);
			}
		}
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
	 * Checks if the given password matches the password in the core_auth_password table.
	 * 
	 * This function should probably be in a "password" property.
	 * 
	 * @param string $password
	 * @return boolean 
	 */
	public function checkPasswordTable($password) {		
		$this->passwordVerified = password_verify($password, $this->password);
		
		if($this->passwordVerified){
			$this->updateDigest($password);
		}
		return $this->passwordVerified;
	}
	
	/**
	 * Check if the password is correct for this user.
	 * 
	 * @param string $password
	 * @return boolean 
	 */
	public function checkPassword($password) {		
		
		foreach($this->getAuthenticationMethods() as $method) {
			$authenticator = $method->getAuthenticator();
			if (!($authenticator instanceof \go\core\auth\PrimaryAuthenticator)) {
				continue;
			}
			
			$this->passwordVerified = $authenticator->authenticate($this->username, $password);
			break;
		}	
		
		return $this->passwordVerified;
	}
	
	private $plainPassword;

	public function setPassword($password) {
		$this->plainPassword = $password;		
	}
	
	public function getDigest() {
		return $this->digest;
	}

	private function updateDigest() {
		$digest = md5($this->username . ":" . self::DIGEST_REALM . ":" . $this->plainPassword);
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
			return true;
		}
		return false;
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
		
		if(isset($this->plainPassword) && $this->validatePassword) {
			if(strlen($this->plainPassword) < GO()->getSettings()->passwordMinLength) {
				$this->setValidationError('password', ErrorCode::INVALID_INPUT, "Minimum password length is ".GO()->getSettings()->passwordMinLength." chars");
			}
		}
		
		if($this->isNew()) {
			$config = GO()->getConfig();
			
			if(!empty($config['limits']['userCount']) && $config['limits']['userCount'] <= self::count()) {
				throw new \go\core\exception\Forbidden("The maximum number of users have been reached");
			}
		}
		
		return parent::internalValidate();
	}
	
	private static function count() {
		return (int) (new Query())
						->selectSingleValue('count(*)')
						->from('core_user')
						//->where('deletedAt is null')
						->single();
	}

	

	public function hasPermissionLevel($level = Acl::LEVEL_READ) {
		return $this->id == App::get()->getAuthState()->getUserId() || App::get()->getAuthState()->getUser()->isAdmin();
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
	
	public function sendRecoveryMail($to, $redirectUrl = ""){
		
		$this->recoveryHash = bin2hex(random_bytes(20));
		$this->recoverySendAt = new \DateTime();
		
		$siteTitle=\GO()->getSettings()->title;
		$url = \GO()->getSettings()->URL.'#recover/'.$this->recoveryHash . '/' . urlencode($redirectUrl);
		$emailBody = \GO()->t('recoveryMailBody');
		$emailBody = sprintf($emailBody,$this->displayName, $siteTitle, $this->username, $url);
		$emailBody = str_replace('{ip_address}', \GO\Base\Util\Http::getClientIp() , $emailBody);
		
		$message = \GO()->getMailer()->compose()	  
			->setFrom(\GO()->getSettings()->systemEmail, $siteTitle)
			->setTo(!empty($to) ? $to : $this->recoveryEmail, $this->displayName)
			->setSubject(GO()->t('Lost password'))
			->setBody($emailBody);
		
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
		if($this->isNew()) {
			$group = new Group();
			$group->name = $this->username;
			$group->isUserGroupFor = $this->id;
			if(!$group->save()) {
				throw new \Exception("Could not create home group");
			}

			if(!(new UserGroup)->setValues(['groupId' => $group->id, 'userId' => $this->id])->internalSave()) {
				throw new \Exception("Couldn't add user to group");
			}
			if(!(new UserGroup)->setValues(['groupId' => Group::ID_EVERYONE, 'userId' => $this->id])->internalSave()) {
				throw new \Exception("Couldn't add user to group");
			}
		}
		
		
		return true;		
	}
	
	/**
	 * Add user to group if not already in it.
	 * 
	 * You need to call save() after this function.
	 * 
	 * @param int $groupId
	 * @return $this
	 */
	public function addGroup($groupId) {
		foreach($this->groups as $group) {
			if($group->groupId == $groupId) {
				return $this;
			}
		}
		
		$this->groups[] = (new UserGroup)->setValues(['groupId' => $groupId]);
		
		return $this;
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
