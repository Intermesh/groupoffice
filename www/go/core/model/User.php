<?php

namespace go\core\model;

use DateTimeZone;
use Exception;
use GO\Base\Model\AbstractUserDefaultModel;
use GO\Base\Model\User as LegacyUser;
use GO\Base\Util\Http;
use GO\Calendar\Model\Calendar;
use go\core\App;
use go\core\auth\Authenticate;
use go\core\auth\BaseAuthenticator;
use go\core\auth\DomainProvider;
use go\core\auth\Password;
use go\core\convert\UserSpreadsheet;
use go\core\customfield\Date;
use go\core\data\Model;
use go\core\db\Column;
use go\core\db\Criteria;
use go\core\exception\ConfigurationException;
use go\core\Installer;
use go\core\mail\Message;
use go\core\mail\Util;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\exception\Forbidden;
use go\core\jmap\Entity;
use go\core\orm\CustomFieldsTrait;
use go\core\util\ClassFinder;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;
use GO\Files\Model\Folder;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\EmailAddress;
use go\modules\community\notes\model\NoteBook;
use go\modules\community\tasks\model\Tasklist;


class User extends Entity {
	
	use CustomFieldsTrait;

	const ID_SUPER_ADMIN = 1;

	/**
	 * Fires on login
	 *
	 * @param User $user
	 */
	const EVENT_LOGIN = 'login';

	/**
	 * Fires on logout
	 *
	 * @param User $user
	 */
	const EVENT_LOGOUT = 'logout';

	/**
	 * @param string $username
	 * @param User $user Can be null
	 */
	const EVENT_BADLOGIN = 'badlogin';

	const USERNAME_REGEX = '/[A-Za-z0-9_\-\.@]+/';
	
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
	 * @var bool
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

	/**
	 * Home directory of the user
	 *
	 * eg. users/admin
	 *
	 * @var string
	 */
	public $homeDir;

	/**
	 * When true the user interface will show a confirm dialog before moving item with drag and drop
	 * @var bool
	 */
	public $confirmOnMove;
	
	
	public $max_rows_list;

	/**
	 *
	 * @var bool
	 */
	protected $archive = false;
	
	/**
	 * The user timezone
	 * 
	 * @var string eg. europe/amsterdam
	 */
	public $timezone;
	public $start_module;
	public $language;
	protected $theme;
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

	protected $permissionLevel;
	
	public function getDateTimeFormat(): string
	{
		return $this->dateFormat . ' ' . $this->timeFormat;
	}

	/**
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * The group ID's of the user
	 * 
	 * @var int[]
	 */
	public $groups = [];
	
	/**
	 * Changed to false in setValues() so when the the jmap api is used it needs to be verified
	 * @var bool 
	 */
	private $passwordVerified = true;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('core_user', 'u')
			->addTable('core_auth_password', 'p', ['id' => 'userId'])
			->addScalar('groups', 'core_user_group', ['id' => 'userId']);
	}


	/**
	 * @var Group
	 */
	private $personalGroup;

  /**
   * Get the user's personal group used for granting permissions
   *
   * @return Group
   * @throws Exception
   */
	public function getPersonalGroup(): Group
	{
		if(empty($this->personalGroup)){
			$this->personalGroup = Group::find()->where(['isUserGroupFor' => $this->id])->single();
		}

		return $this->personalGroup;
	}

	/**
	 * @throws Exception
	 */
	public function setPersonalGroup($values) {
		$this->getPersonalGroup()->setValues($values);
	}
	
	public function setValues(array $values): User
	{
		$this->passwordVerified = false;
		return parent::setValues($values);
	}


	/**
	 * @throws Forbidden
	 */
	public function setArchive($v) {
		if(!go()->getAuthState()->isAdmin()) {
			throw new Forbidden("Only admins can archive");
		}
		$this->archive = $v;
	}

	protected function canCreate(): bool
	{
		return go()->getModel()->getUserRights()->mayChangeUsers;
	}
	
	protected function init() {
		parent::init();
		
		if($this->isNew()) {
			$s = Settings::get();
			$this->language = $s->language;
			$this->timeFormat = $s->defaultTimeFormat;	
			$this->dateFormat = $s->defaultDateFormat;
			$this->timezone = $s->defaultTimezone;
			$this->firstWeekday = (int) $s->defaultFirstWeekday;
			$this->currency = $s->defaultCurrency;
			$this->shortDateInList = (bool) $s->defaultShortDateInList;
			$this->confirmOnMove = (bool) $s->defaultConfirmOnMove;
			$this->listSeparator = $s->defaultListSeparator;
			$this->textSeparator = $s->defaultTextSeparator;
			$this->thousandsSeparator = $s->defaultThousandSeparator;
			$this->decimalSeparator = $s->defaultDecimalSeparator;
			
			$this->groups = array_merge($this->groups, $s->getDefaultGroups());
			if(!in_array(Group::ID_EVERYONE, $this->groups)) { 			
				$this->groups[] = Group::ID_EVERYONE;
			}
		}
	}

	private $currentPassword;

  /**
   * @param $currentPassword
   * @throws Exception
   */
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
   * @throws Exception
   */
	public function checkPassword(string $password): bool
	{

		$auth = new Authenticate();
		$success = $auth->passwordLogin($this->username, $password);

		if($success) {
			$this->passwordVerified = true;
		}
		return $success !== false;
	}
	
	/**
	 * Needed because password is protected
	 *
	 * @param string $password
	 * @return boolean
	 */
	public function passwordVerify(string $password): bool
	{
		return password_verify($password, $this->password);
	}

	private $plainPassword;
	
	public function plainPassword() {
		return $this->plainPassword;
	}

	public function setPassword($password) {
		$this->recoveryHash = null;
		$this->recoverySendAt = null;
		$this->plainPassword = $password;
	}

	/**
	 * Check if this user has a password stored in the database.
	 * 
	 * Used by authenticators (IMAP or LDAP) so they can clear it if it's not needed.
	 * 
	 * @return bool
	 */
	public function hasPassword(): bool
	{
		return !empty($this->password);
	}

  /**
   * Clear the password stored in the database.
   *
   * Used by authenticators (IMAP or LDAP) so they can clear it if it's not needed.
   *
   * @return bool
   * @throws Exception
   */
	public function clearPassword(): bool
	{
		return go()->getDbConnection()->delete('core_auth_password', ['userId' => $this->id])->execute();
	}

	public function getPassword() {
		return null;
	}

  /**
   * Make sure to call this when changing the password with a recovery hash
   * @param string $hash
   * @return bool
   */
	public function checkRecoveryHash(string $hash): bool
	{
		if($hash === $this->recoveryHash) {
			$this->passwordVerified = true;
			$this->recoveryHash = null;
			return true;
		}
		return false;
	}

	/**
	 * @throws Exception
	 */
	private function validatePasswordChange(): bool
	{
		
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
		
		return App::get()->getAuthState()->isAdmin();		
	}
	
	protected function internalValidate() {

		if(!isset($this->homeDir) && in_array("homeDir", $this->selectedProperties)) {
			$this->homeDir = "users/" . $this->username;
		}

		if(empty($this->recoveryEmail)) {
			$this->recoveryEmail = $this->email;
		}

		if($this->isModified(['username'])) {

			if(!preg_match(self::USERNAME_REGEX, $this->username)) {
				$this->setValidationError('username', ErrorCode::MALFORMED, go()->t("You have invalid characters in the username") . " (a-z, 0-9, -, _, ., @).");
			}
		}

		if($this->isModified('groups')) {	
			
			
			if(!in_array(Group::ID_EVERYONE, $this->groups)) {
				$this->groups[] = Group::ID_EVERYONE;
				// $this->setValidationError('groups', ErrorCode::INVALID_INPUT, go()->t("You can't remove group everyone"));
			}
			
			if(!$this->isNew()) {
				if(!in_array($this->getPersonalGroup()->id, $this->groups)) {
					$this->setValidationError('groups', ErrorCode::INVALID_INPUT, go()->t("You can't remove the user's personal group"));
				}
			}

			if($this->id == 1 && !in_array(Group::ID_ADMINS, $this->groups)) {
				$this->setValidationError('groups', ErrorCode::INVALID_INPUT, go()->t("You can't remove group Admins from the primary admin user"));
			}
		}
		
		if(!$this->validatePasswordChange()) {
			if(!$this->hasValidationErrors('currentPassword')) {
				$this->setValidationError('currentPassword', ErrorCode::REQUIRED);
			}
		}
		
		if(isset($this->plainPassword) && $this->validatePassword) {
			if(strlen($this->plainPassword) < go()->getSettings()->passwordMinLength) {
				$this->setValidationError('password', ErrorCode::INVALID_INPUT, "Minimum password length is ".go()->getSettings()->passwordMinLength." chars");
			}
		}
		
		if($this->isNew()) {
			$config = go()->getConfig();
			
			if(!empty($config['limits']['userCount']) && $config['limits']['userCount'] <= self::count()) {
				throw new Forbidden("The maximum number of users have been reached");
			}
		}

		if($this->isModified(['email'])) {

			if(!Util::validateEmail($this->email)) {
				$this->setValidationError('email', ErrorCode::MALFORMED);
			} else {

				$id = User::find()->selectSingleValue('id')->where(['email' => $this->email])->single();

				if ($id && $id != $this->id) {
					$this->setValidationError('email', ErrorCode::UNIQUE, 'The e-mail address must be unique in the system');
				}
			}
		}

		$this->validateMaxUsers();

		if($this->isModified(['timezone'])) {
			try {
				new DateTimeZone($this->timezone);
			} catch(Exception $e) {
				$this->setValidationError('timezone', ErrorCode::INVALID_INPUT, go()->t("Invalid timezone"));
			}
		}
		
		parent::internalValidate();
	}

	private function validateMaxUsers () {
		if(!$this->isNew()) {
			return;
		}

		if($this->maxUsersReached()) {
			$this->setValidationError('password', ErrorCode::FORBIDDEN, go()->t("You're not allowed to create more than x users"));
		}
	}

	/**
	 * @throws ConfigurationException
	 */
	private function maxUsersReached(): bool
	{
	  if(empty(go()->getConfig()['max_users'])) {
	    return false;
    }

		$stmt = go()->getDbConnection()->query("SELECT count(*) AS count FROM `core_user` WHERE enabled = 1");
		$record = $stmt->fetch();
		$countActive = $record['count'];
		return $countActive >= go()->getConfig()['max_users'];
	}

	/**
	 * @throws Exception
	 */
	private static function count(): int
	{
		return (int) (new Query())
						->selectSingleValue('count(*)')
						->from('core_user')
						//->where('deletedAt is null')
						->single();
	}


	protected function internalGetPermissionLevel(): int
	{
		if($this->id == App::get()->getAuthState()->getUserId()) {
			return Acl::LEVEL_WRITE;
		}

		return parent::internalGetPermissionLevel();
	}
	
	protected static function textFilterColumns(): array
	{
		return ['username', 'displayName', 'email'];
	}
	
	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
      ->add('permissionLevel', function(Criteria $criteria, $value, Query $query) {
        if(!$query->isJoined('core_group', 'g')) {
          $query->join('core_group', 'g', 'u.id = g.isUserGroupFor');
        }
        Acl::applyToQuery($query, 'g.aclId', $value);
      }, Acl::LEVEL_READ)
      ->add('showDisabled', function (Criteria $criteria, $value){
        if($value === false) {
          $criteria->andWhere('enabled', '=', 1);
        }
      }, false)
      ->add('groupId', function (Criteria $criteria, $value, Query $query){
        $query->join('core_user_group', 'ug', 'ug.userId = u.id')->andWhere(['ug.groupId' => $value]);
      });
	}


  /**
   * Check if use is an admin
   *
   * @return boolean
   * @throws Exception
   */
	public function isAdmin(): bool
	{
		return !!(new Query)
			->select()
			->from('core_user_group')
			->where(['groupId' => Group::ID_ADMINS, 'userId' => $this->id])->single();
	}

	/**
	 * @throws Exception
	 */
	public static function isAdminById($userId): bool
	{
		if($userId == User::ID_SUPER_ADMIN) {
			return true;
		}

		return !!(new Query)
				->select()
				->from('core_user_group')
				->where(['groupId' => Group::ID_ADMINS, 'userId' => $userId])->single();
	}

  /**
   * Alias for making isAdmin() a public property
   * @return bool
   * @throws Exception
   */
	public function getIsAdmin(): bool
	{
		return $this->isAdmin();
	}


	/**
	 * Get available authentication methods
	 * 
	 * @return BaseAuthenticator[]
	 */
	public function getAuthenticators(): array
	{

		$authenticators = [];

		$auth = new Authenticate();
		$primary = $auth->getPrimaryAuthenticatorForUser($this->username);

		$authenticators[] = $primary;

		foreach ($auth->getSecondaryAuthenticatorsForUser($this->username) as $authenticator) {
			if ($authenticator::isAvailableFor($this->username)) {
				$authenticators[] = $authenticator;
			}
		}

		return $authenticators;
	}

  /**
   * Send a password recovery link
   *
   * @param string $to
   * @param string $redirectUrl If given GroupOffice will redirect to this URL after creating a new password.
   * @throws Exception
   */
	public function sendRecoveryMail(string $to, string $redirectUrl = ""){
		
		$this->recoveryHash = bin2hex(random_bytes(20));
		$this->recoverySendAt = new DateTime();

		if(!$this->save()) {
			throw new Exception("Could not save user");
		}
		
		$siteTitle=go()->getSettings()->title;
		$url = go()->getSettings()->URL.'#recover/'.$this->recoveryHash . '-' . urlencode($redirectUrl);
		$emailBody = go()->t('recoveryMailBody');
		$emailBody = sprintf($emailBody,$this->displayName, $siteTitle, $this->username, $url);
		$emailBody = str_replace('{ip_address}', Http::getClientIp() , $emailBody);
		
		$message = go()->getMailer()->compose()	  
			->setFrom(go()->getSettings()->systemEmail, $siteTitle)
			->setTo(!empty($to) ? $to : $this->recoveryEmail, $this->displayName)
			->setSubject(go()->t('Lost password'))
			->setBody($emailBody);
		
		if(!$message->send()) {
			throw new Exception("Could not send mail. The notication system setttings may be incorrect.");
		}
	}
	
	protected function internalSave(): bool
	{
		
		if(isset($this->plainPassword)) {
			$this->password = $this->passwordHash($this->plainPassword);

			if(!$this->isNew()) {

				//remove persistent remember me cookies on password change
				if(!RememberMe::delete(['userId' => $this->id])) {
					return false;
				}
			}
		}
		
		if(!parent::internalSave()) {
			return false;
		}	
		
		$this->saveContact();

		if(isset($this->personalGroup) && $this->personalGroup->isModified()) {
			if(!$this->personalGroup->save()) {
				$this->setValidationError('personalGroup', ErrorCode::RELATIONAL, "Couldn't save personal group");
				return false;
			}
		}
		$this->createPersonalGroup();

		if($this->isNew()) {
			$this->legacyOnSave();	
		}

		if($this->archive) {
			$this->archiveUser();
		}

		$this->changeHomeDir();

		if($this->isModified(['username', 'displayName', 'avatarId', 'email']) && !Installer::isInstalling()) {
			UserDisplay::entityType()->changes([[$this->id, $this->findAclId(), 0]]);
		}

		if(!$this->saveAuthorizedClients()) {
			return false;
		}

		return true;
	}

	/**
	 * @throws Exception
	 */
	private function changeHomeDir() {
		if(!$this->isModified("homeDir") || !Module::isInstalled('legacy', 'files')) {
			return;
		}

		$oldDir = $this->getOldValue('homeDir');
		if(!$oldDir) {
			return;
		}

		$folder = Folder::model()->findByPath($oldDir);
		if(!$folder) {
			return;
		}

		$parent = dirname($this->homeDir);
		if(empty($parent)) {
			throw new Exception("Invalid home directory. It must be a parent directory like users/username");
		}

		$dest = Folder::model()->findByPath($parent, true);

		$folder->name = basename($this->homeDir);
		$folder->parent_id=$dest->id;
		$folder->systemSave = true;

		if(!$folder->save(true)) {
			throw new Exception("Failed to move home dir from " . $oldDir . "  to " .$this->homeDir);
		}
	}


	
	/**
	 * Hash a password for users
	 * 
	 * @param string $password
	 * @return string
	 */
	public static function passwordHash(string $password): string
	{
		return password_hash($password, PASSWORD_DEFAULT);
	}

	/**
	 * @throws Exception
	 */
	private function saveContact(): bool
	{
		
//		if(!isset($this->contact) ){// || $this->isModified(['displayName', 'email', 'avatarId'])) {
//			$this->contact = $this->getProfile();
//		}

		if (!isset($this->contact)) {
			return true;
		}

		$this->contact->photoBlobId = $this->avatarId;
		if (!isset($this->contact->emailAddresses[0])) {
			$this->contact->emailAddresses = [(new EmailAddress($this->contact))->setValues(['email' => $this->email])];
		}
		if (empty($this->contact->name) || $this->isModified(['displayName'])) {
			$this->contact->name = $this->displayName;
			$parts = explode(' ', $this->displayName);
			$this->contact->firstName = array_shift($parts);
			$this->contact->lastName = implode(' ', $parts);
		}

		$this->contact->goUserId = $this->id;
		return $this->contact->save();
	}


	/**
	 * @throws Exception
	 */
	private function createPersonalGroup()
	{
		if ($this->isNew() || $this->isModified(['groups', 'username'])) {
			if ($this->isNew()) {// !in_array($this->getPersonalGroup()->id, $groupIds)) {
				$personalGroup = new Group();
				$personalGroup->name = $this->username;
				$personalGroup->isUserGroupFor = $this->id;
				$personalGroup->users[] = $this->id;

				if (!$personalGroup->save()) {
					throw new Exception("Could not create home group");
				}

				$this->personalGroup = $personalGroup;
			} else {
				$personalGroup = $this->getPersonalGroup();
				if ($this->isModified('username')) {
					$personalGroup->name = $this->username;
					$personalGroup->save();
				}
			}

			if (!in_array($personalGroup->id, $this->groups)) {
				$this->groups[] = $personalGroup->id;
			}
		}
	}


	/**
	 * @throws \GO\Base\Exception\AccessDenied
	 */
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
	public function addGroup(int $groupId): User
	{
		
		if(!in_array($groupId, $this->groups)) {
			$this->groups[] = $groupId;
		}
		
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
	public function hasModule(string $package, string $name): bool
	{
		return Module::isAvailableFor($package, $name, $this->id);		
	}


	/**
	 * Get the user disk quota in bytes
	 * @return int amount of bytes the user may use
	 * @throws ConfigurationException
	 * @throws ConfigurationException
	 * @throws ConfigurationException
	 */
	public function getStorageQuota(){
		if(!empty($this->disk_quota)) {
			return $this->disk_quota*1024*1024;
		} else 
		{
			return go()->getStorageQuota();
		}
	}

	/**
	 * @throws ConfigurationException
	 */
	public function getStorageFreeSpace() {
		if(!empty($this->disk_quota)) {
			return $this->disk_quota*1024*1024 - $this->disk_usage;
		} else
		{
			return go()->getStorageFreeSpace();
		}
	}
	
	protected static function internalDelete(Query $query): bool
	{

		$query->andWhere('id != 1');
				
		go()->getDbConnection()->beginTransaction();

		go()->getDbConnection()->delete('go_settings', (new Query)->where('user_id', 'in', $query))->execute();
		//go()->getDbConnection()->delete('go_reminders', (new Query)->where('user_id', 'in', $query))->execute();
		go()->getDbConnection()->delete('go_reminders_users', (new Query)->where('user_id', 'in', $query))->execute();

		Group::delete( (new Query)->where('isUserGroupFor', 'in', $query));

		if(!static::legacyOnDelete($query) || !parent::internalDelete($query)) {
			go()->getDbConnection()->rollBack();
			return false;
		}

		return go()->getDbConnection()->commit();
	}


	/**
	 * @throws \GO\Base\Exception\AccessDenied
	 */
	public static function legacyOnDelete(Query $query): bool
	{

			foreach($query as $id) {
				$user = LegacyUser::model()->findByPk($id, false, true);
				LegacyUser::model()->fireEvent("beforedelete", [$user, true]);
				//delete all acl records		
				$defaultModels = AbstractUserDefaultModel::getAllUserDefaultModels();

				foreach($defaultModels as $model){
					$model->deleteByAttribute('user_id',$id);
				}

				LegacyUser::model()->fireEvent("delete", [$user, true]);
			}
	

		return true;
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
	public static function getAuthenticationDomains(): array
	{
		$classes = go()->getCache()->get("authentication-domains-providers");
		if(!is_array($classes)) {
			$classFinder = new ClassFinder();
			$classes = $classFinder->findByParent(DomainProvider::class);
			go()->getCache()->set("authentication-domains-providers", $classes);
		}
		$domains = [];
		foreach($classes as $cls) {
			$domains = array_merge($domains, $cls::getDomainNames());
		}
		return $domains;		
	}
	
	/**
	 *
	 * @var Contact
	 */
	private $contact;

	/**
	 * @throws Exception
	 */
	public function getProfile() {
		if(!Module::isInstalled('community', 'addressbook')) {
			return null;
		}
		
		$contact = Contact::findForUser($this->id);
		if(!$contact) {
			$contact = new Contact();
			$contact->goUserId = $this->id;
			$contact->addressBookId = go()->getSettings()->userAddressBook()->id;
			if($this->email) {
				$contact->emailAddresses[0] = (new EmailAddress($contact))->setValues(['email' => $this->email]);
			}
			$nameParts = explode(" ", $this->displayName);
			$contact->firstName = array_shift($nameParts);
			$contact->lastName = implode(" ", $nameParts);
		}
		
		return $contact;
	}

	/**
	 * @throws Exception
	 */
	public function setProfile($values) {
		if(!Module::isInstalled('community', 'addressbook')) {
			throw new Exception("Can't set profile without address book module.");
		}
		
		$this->contact = $this->getProfile();		
		$this->contact->setValues($values);		
	
		if(!empty($this->contact->name)) {
			$this->displayName = $this->contact->name;
		}
	}


	/**
	 * @inheritDoc
	 */
	public static function converters(): array
	{
		return array_merge(parent::converters(), [UserSpreadsheet::class]);
	}

	/**
	 * Decorate the message for newsletter sending.
	 * This function should at least add the to address.
	 *
	 * @param Message $message
	 */
	public function decorateMessage(Message $message) {
		$message->setTo($this->email, $this->displayName);
	}

	private $country;

	public function getCountry() {
		if(!isset($this->country)) {
			$tz = new DateTimeZone($this->timezone);
			$i = $tz->getLocation();
			$this->country = $i['country_code'];
		}

		return $this->country;
	}


	/**
	 * Archive a user - remove all shares instead of with admins only.
	 *
	 * If a user is archived, any shares with themselves and non-admin users are deleted.Please note that we only do
	 * this for community items. It is not entirely certain for other objects if they should be archived.
	 *
	 * @throws Exception
	 * @throws Exception
	 * @throws Exception
	 * @throws \GO\Base\Exception\AccessDenied
	 * @throws Exception
	 * @throws Exception
	 * @throws Exception
	 * @throws Exception
	 * @throws Exception
	 * @throws Exception
	 * @throws Exception
	 * @throws Exception
	 * @throws Exception
	 */
	private function archiveUser()
	{
		$aclIds = [];

		if ($defAddressBookId = $this->addressBookSettings->getDefaultAddressBookId()) {
			$addressBook = AddressBook::findById($defAddressBookId);
			$aclIds[] = $addressBook->findAclId();
			AddressBook::entityType()->change($addressBook);
		}
		if ($defNoteBookId = $this->notesSettings->getDefaultNoteBookId()) {
			$noteBook = NoteBook::findById($defNoteBookId);
			$aclIds[] = $noteBook->findAclId();
			NoteBook::entityType()->change($noteBook);
		}
		if ($defTaskListId = $this->tasksSettings->getDefaultTasklistId()) {
			$aclIds[] = Tasklist::findById($defTaskListId)->aclId;
		}
		if ($calendarId = $this->calendarSettings->calendar_id) {
			$aclIds[] = Calendar::model()->findByPk($calendarId)->findAclId();
		}
		$grpId = $this->getPersonalGroup()->id();
		foreach (Acl::findByIds($aclIds) as $rec) {
			foreach ($rec->groups as $aclGrp) {
				if (!in_array($aclGrp->groupId, [Group::ID_ADMINS, $grpId])) {
					$rec->removeGroup($aclGrp->groupId);
				}
			}
			$rec->save();
		}
	}


	public function setTheme($v) {
		$this->theme = $v;
	}

	/**
	 * @throws ConfigurationException
	 */
	public function getTheme() {
		if(!go()->getConfig()['allow_themes']) {
			return go()->getConfig()['theme'];
		} else {
			return $this->theme;
		}
	}

	/**
	 * Get authorized clients with ['remoteIpAddress', 'platform', 'browser']
	 * @return array[]
	 * @throws Exception
	 */
	public function getAuthorizedClients(): array
	{
		$clients =
			go()->getDbConnection()->select("remoteIpAddress, platform, browser, max(expiresAt) as expiresAt")
			->from(
				go()->getDbConnection()
				->select("remoteIpAddress, platform, browser, max(expiresAt) as expiresAt")
				->from('core_auth_token')
				->where('userId', '=', $this->id)
				->andWhere('expiresAt', '>', new DateTime())
				->groupBy(['remoteIpAddress', 'platform', 'browser'])
				->union(
					go()->getDbConnection()
						->select("remoteIpAddress, platform, browser, max(expiresAt) as expiresAt")
						->distinct()
						->from('core_auth_remember_me')
						->where('userId', '=', $this->id)
						->andWhere('expiresAt', '>', new DateTime())
						->groupBy(['remoteIpAddress', 'platform', 'browser'])
				)
			)->groupBy(['remoteIpAddress', 'platform', 'browser']);


		go()->debug($clients);

		$clients = $clients->all();

		foreach($clients as &$client) {
//			try {
//				$geo = Geolocation::locate($client['remoteIpAddress']);
//				$client['countryCode'] = $geo['countryCode'];
//			} catch(\Exception $e) {
//				ErrorHandler::logException($e);
//				$client['countryCode'] = null;
//			}

			$client['expiresAt'] = (DateTime::createFromFormat(Column::DATETIME_FORMAT, $client['expiresAt']));
		}

		return $clients;
	}

	private $authorizedClients;

	public function setAuthorizedClients($clients) {
		$this->authorizedClients = $clients;
	}

	/**
	 * @throws Exception
	 */
	private function saveAuthorizedClients(): bool
	{
		if(!isset($this->authorizedClients)) {
			return true;
		}

		$query = (new Query)
			->where('userId', '=', $this->id)
			->andWhere('expiresAt', '>', new DateTime());

		if(!empty($this->authorizedClients)) {
			$c = new Criteria();
			foreach ($this->authorizedClients as $client) {
				unset($client['countryCode'], $client['expiresAt']);
				$c->andWhereNot($client);
			}

			$query->andWhere($c);
		}

		return Token::delete($query) && RememberMe::delete($query);
	}



}
