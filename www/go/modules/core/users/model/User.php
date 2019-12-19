<?php

namespace go\modules\core\users\model;

use Exception;
use GO;
use GO\Base\Model\AbstractUserDefaultModel;
use GO\Base\Model\User as LegacyUser;
use GO\Base\Util\Http;
use go\core\acl\model\Acl;
use go\core\App;
use go\core\auth\Method;
use go\core\auth\Password;
use go\core\auth\PrimaryAuthenticator;
use go\core\db\Criteria;
use go\core\db\Query;
use go\core\exception\Forbidden;
use go\core\jmap\Entity;
use go\modules\core\modules\model\Module;
use go\core\orm\CustomFieldsTrait;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;
use go\modules\core\groups\model\Group;
use go\modules\core\users\model\Settings;

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
	
	use CustomFieldsTrait;
	
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
	public $loginCount;
	
	/**
	 * Last login time
	 * 
	 * @var DateTime
	 */
	public $lastLogin;
	
	/**
	 *
	 * @var DateTime
	 */
	public $modifiedAt;
	
	/**
	 *
	 * @var DateTime
	 */
	public $createdAt;
	
	/**
	 * Date format
	 * @var string
	 */
	public $dateFormat;
	
	/**
	 * Display dates short in lists.
	 * 
	 * @var boolean
	 */
	public $shortDateInList = true;
	
	/**
	 * Time format
	 * 
	 * @var string
	 */
	public $timeFormat;
	
	/**
	 * char to separate thousands in numbers
	 * 
	 * @var string
	 */
	public $thousandsSeparator;
	
	/**
	 * Char to separate decimals in numbers
	 * @var string
	 */
	public $decimalSeparator;
	
	/**
	 * Currency char
	 * 
	 * @var string
	 */
	public $currency;
	
	/**
	 * Separator for CSV lists. eg. ; or ,
	 * @var string
	 */
	public $listSeparator;
	
	/**
	 * Separator for text in CSV. eg. '"'
	 * 
	 * @var string
	 */
	public $textSeparator;
	
	
	public $max_rows_list;
	public $timezone;
	public $start_module;
	public $language;
	public $theme;
	public $firstWeekday;
	public $sort_name;
	
	public $mute_sound;
	public $mute_reminder_sound;
	public $mute_new_mail_sound;
	public $show_smilies;
	public $auto_punctuation;
	
	
	protected $files_folder_id;
	/**
	 * Disk quota in MB
	 * @var int
	 */
	public $disk_quota;
	
	/**
	 * Disk usage in bytes
	 * 
	 * @var int
	 */
	public $disk_usage;
	
	public $mail_reminders;
	public $popup_reminders;
	public $popup_emails;
	public $holidayset;
	public $sort_email_Addresses_by_time;
	public $no_reminders;
	
	protected $last_password_change;
	public $force_password_change;
	
	
	public function getDateTimeFormat() {
		return $this->dateFormat . ' ' . $this->timeFormat;
	}

	/**
	 *
	 * @var Password
	 */
	protected $password;
	
	/**
	 * Used for DIGEST authentication which is required for webdav to work with the Microsoft Windows client.
	 * 
	 * @var string
	 */
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
	
	/**
	 * The working week
	 * 
	 * @var WorkingWeek
	 */
	public $workingWeek;

	protected static function defineMapping() {
		return parent::defineMapping()
			->addTable('core_user', 'u')
			->addTable('core_auth_password', 'p', ['id' => 'userId'])
//			->addRelation('password', Password::class, ['id' => 'userId'], false)
			->addRelation("groups", UserGroup::class, ['id' => 'userId'])
			->addRelation('workingWeek', WorkingWeek::class, ['id' => 'user_id'], false);
	}
	
	/**
	 * Get the user's personal group used for granting permissions
	 * 
	 * @return Group	 
	 */
	public function getPersonalGroup() {
		return Group::find()->where(['isUserGroupFor' => $this->id])->single();
	}
	
	public function setValues(array $values) {
		$this->passwordVerified = false;
		return parent::setValues($values);
	}
	
	protected function init() {
		parent::init();
		
		if($this->isNew()) {
			$s = Settings::get();
			$this->timeFormat = $s->defaultTimeFormat;	
			$this->dateFormat = $s->defaultDateFormat;
			$this->timezone = $s->defaultTimezone;
			$this->firstWeekday = $s->defaultFirstWeekday;
			$this->currency = $s->defaultCurrency;
			$this->shortDateInList = $s->defaultShortDateInList;
			$this->listSeparator = $s->defaultListSeparator;
			$this->textSeparator = $s->defaultTextSeparator;
			$this->thousandsSeparator = $s->defaultThousandSeparator;
			$this->decimalSeparator = $s->defaultDecimalSeparator;			
			foreach($s->getDefaultGroups() as $groupId) {
				$this->groups[] = (new UserGroup)->setValues(['groupId' => $groupId]);
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
	 * Check if the password is correct for this user.
	 * 
	 * @param string $password
	 * @return boolean 
	 */
	public function checkPassword($password) {		
		
		$authenticator = $this->getPrimaryAuthenticator();
		if(!isset($authenticator)) {
			throw new \Exception("No primary authenticator found!");
		}
		$success = $authenticator->authenticate($this->username, $password);		
		if($success) {
			$this->passwordVerified = true;
		}
		return $success;
	}
	
	/**
	 * needed because password is protected
	 * @param string $password
	 * @return boolean
	 */
	public function passwordVerify($password) {
		return password_verify($password, $this->password);
	}
	
	private $plainPassword;
	
	public function plainPassword() {
		return $this->plainPassword;
	}

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
		
		if(!$this->isNew() && $this->isModified('groups')) {
			$groupIds = array_column($this->groups, 'groupId');
			
			if(!in_array(Group::ID_EVERYONE, $groupIds)) {
				$this->setValidationError('groups', ErrorCode::INVALID_INPUT, "You can't remove group everyone");
			}
			
			if(!in_array($this->getPersonalGroup()->id, $groupIds)) {
				$this->setValidationError('groups', ErrorCode::INVALID_INPUT, "You can't remove the user's personal group");
			}
		}
		
		if(!$this->validatePasswordChange()) {
			if(!$this->hasValidationErrors('currentPassword')) {
				$this->setValidationError('currentPassword', ErrorCode::REQUIRED);
			}
		}
		
		if(isset($this->plainPassword) && $this->validatePassword) {
			if(strlen($this->plainPassword) < GO()->getSettings()->passwordMinLength) {
				$this->setValidationError('password', ErrorCode::INVALID_INPUT, "Minimum password length is ".GO()->getSettings()->passwordMinLength." chars");
			} else {
				$this->updateDigest();
			}
		}
		
		if($this->isNew()) {
			$config = GO()->getConfig();
			
			if(!empty($config['limits']['userCount']) && $config['limits']['userCount'] <= self::count()) {
				throw new Forbidden("The maximum number of users have been reached");
			}
		}
		$this->validateMaxUsers();
		return parent::internalValidate();
	}

	private function validateMaxUsers () {
		if(!$this->isNew()) {
			return;
		}

		if($this->maxUsersReached()) {
			$this->setValidationError('password', ErrorCode::FORBIDDEN, go()->t("You're not allowed to create more than x users"));
		}
	}
	
	private function maxUsersReached() {
		$stmt = go()->getDbConnection()->query("SELECT count(*) AS count FROM `core_user` WHERE enabled = 1");
		$record = $stmt->fetch();
		$countActive = $record['count'];
		return \GO::config()->max_users > 0 && $countActive >= \GO::config()->max_users;
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
	
	protected static function searchColumns() {
		return ['username', 'displayName', 'email'];
	}
	
	public static function filter(Query $query, array $filter) {
				
		if(!isset($filter['showDisabled']) || $filter['showDisabled'] !== true) {
			$query->andWhere('enabled', '=', 1);
		}
		
		if(!empty($filter['groupId'])) {
			$query->join('core_user_group', 'ug', 'ug.userId = u.id')->andWhere(['ug.groupId' => $filter['groupId']]);
		}
		
		if(!empty($filter['exclude'])) {
			$query->andWhere('id', 'NOT IN', $filter['exclude']);
		}
		
		return parent::filter($query, $filter);
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

		$authMethods = Method::find()->orderBy(['sortOrder' => 'DESC']);

		foreach ($authMethods as $authMethod) {
			$authenticator = $authMethod->getAuthenticator();

			if ($authenticator && $authenticator::isAvailableFor($this->username)) {
				$methods[] = $authMethod;
			}
		}

		return $methods;
	}
	
	/**
	 * Send a password recovery link
	 * 
	 * @param string $to
	 * @param string $redirectUrl If given GroupOffice will redirect to this URL after creating a new password.
	 * @return boolean
	 */
	public function sendRecoveryMail($to, $redirectUrl = ""){
		
		$this->recoveryHash = bin2hex(random_bytes(20));
		$this->recoverySendAt = new DateTime();
		
		$siteTitle=GO()->getSettings()->title;
		$url = GO()->getSettings()->URL.'#recover/'.$this->recoveryHash . '-' . urlencode($redirectUrl);
		$emailBody = GO()->t('recoveryMailBody');
		$emailBody = sprintf($emailBody,$this->displayName, $siteTitle, $this->username, $url);
		$emailBody = str_replace('{ip_address}', Http::getClientIp() , $emailBody);
		
		$message = GO()->getMailer()->compose()	  
			->setFrom(GO()->getSettings()->systemEmail, $siteTitle)
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
		
		$this->addSystemGroups();
		
		
		return true;		
	}
	
	/**
	 * Gets the user's primary authenticator class. Usually this is 
	 * \go\core\auth\Password but can also be implemented by the LDAP or 
	 * IMAP authenticator modules.
	 * 
	 * @return PrimaryAuthenticator
	 */
	public function getPrimaryAuthenticator() {
		foreach($this->getAuthenticationMethods() as $method) {
			$authenticator = $method->getAuthenticator();
			if ($authenticator instanceof PrimaryAuthenticator) {
				return $authenticator;
			}			
		}	
		
		return null;
	}
	
	private function addSystemGroups() {
		if($this->isNew() || $this->isModified('groups')) {						
			$groupIds = array_column($this->groups, 'groupId');
			
			if($this->isNew()){// !in_array($this->getPersonalGroup()->id, $groupIds)) {
				$personalGroup = new Group();
				$personalGroup->name = $this->username;
				$personalGroup->isUserGroupFor = $this->id;
				if(!$personalGroup->save()) {
					throw new Exception("Could not create home group");
				}
			} else
			{
				$personalGroup = $this->getPersonalGroup();
			}
			
			if(!in_array($personalGroup->id, $groupIds)) { 
				$personalUserGroup = (new UserGroup)->setValues(['groupId' => $personalGroup->id, 'userId' => $this->id]);
				if(!$personalUserGroup->internalSave()) {
					throw new Exception("Couldn't add user to group");
				}
				$this->groups[] = $personalUserGroup;
			}
			
			if(!in_array(Group::ID_EVERYONE, $groupIds)) { 
				$everyoneUserGroup = (new UserGroup)->setValues(['groupId' => Group::ID_EVERYONE, 'userId' => $this->id]);
				if(!$everyoneUserGroup->internalSave()) {
					throw new Exception("Couldn't add user to group");
				}
				$this->groups[] = $everyoneUserGroup;
			}
			
			$this->legacyOnSave();			
			
		}
	}
	
	
	public function legacyOnSave() {
		//for old framework. Remove when all is refactored!
		$defaultModels = AbstractUserDefaultModel::getAllUserDefaultModels($this->id);			
		$user = LegacyUser::model()->findByPk($this->id, false, true);
		foreach($defaultModels as $model){
			$model->getDefault($user);
		}
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
	 * @param string $package
	 * @param string $name
	 * 
	 * @return boolean
	 */
	public function hasModule($package, $name) {

		if($package == "legacy") {
			$package = null;
		}

		$module = Module::find()->where(['package' => $package, 'name' => $name])->single();
		if(!$module) {
			return false;
		}
		
		if(!Acl::getPermissionLevel($module->aclId, $this->id)) {
			return false;
		}
		
		return true;		
	}
	
	
	/**
	 * Get the user disk quota in bytes
	 * @return int amount of bytes the user may use
	 */
	public function getStorageQuota(){
		if(!empty($this->disk_quota)) {
			return $this->disk_quota*1024*1024;
		} else 
		{
			return GO()->getStorageQuota();
		}
	}
	
	public function getStorageFreeSpace() {
		if(!empty($this->disk_quota)) {
			return $this->disk_quota*1024*1024 - $this->disk_usage;
		} else
		{
			return GO()->getStorageFreeSpace();
		}
	}
	
	protected function internalDelete() {
		
		if($this->id == 1) {
			$this->setValidationError("id", ErrorCode::FORBIDDEN, "You can't delete the primary administrator");
			return false;
		}
		$this->legacyOnDelete();
		return parent::internalDelete();
	}
	
	
	public function legacyOnDelete() {
		$user = LegacyUser::model()->findByPk($this->id, false, true);
		LegacyUser::model()->fireEvent("beforedelete", [$user, true]);
		//delete all acl records		
		$defaultModels = AbstractUserDefaultModel::getAllUserDefaultModels();

		foreach($defaultModels as $model){
			$model->deleteByAttribute('user_id',$this->id);
		}


		LegacyUser::model()->fireEvent("delete", [$user, true]);
	}
	
	/**
	 * Get authentication domains that authenticators can use to identify the user
	 * belongs to that authenticator.
	 * 
	 * For example the IMAP and LDAP authenticator modules use this by implementing
	 * the \go\core\auth\DomainProvider interface.
	 * 
	 * @return string[]
	 */
	public static function getAuthenticationDomains() {
		
		$domains = GO()->getCache()->get("authentication-domains");
		if(is_array($domains)) {
			return $domains;
		}
		
		
		$classFinder = new \go\core\util\ClassFinder();
		$classes = $classFinder->findByParent(\go\core\auth\DomainProvider::class);
		
		$domains = [];
		foreach($classes as $cls) {
			$domains = array_merge($domains, $cls::getDomainNames());
		}
		
		GO()->getCache()->set("authentication-domains", $domains);
		
		return $domains;		
	}

}
