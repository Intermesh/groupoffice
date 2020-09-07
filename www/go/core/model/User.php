<?php

namespace go\core\model;

use DateTimeZone;
use Exception;
use GO\Base\Html\Error;
use GO\Base\Model\AbstractUserDefaultModel;
use GO\Base\Model\User as LegacyUser;
use GO\Base\Util\Http;
use go\core\App;
use go\core\auth\Method;
use go\core\auth\Password;
use go\core\auth\PrimaryAuthenticator;
use go\core\convert\UserCsv;
use go\core\db\Criteria;
use go\core\mail\Message;
use go\core\orm\Query;
use go\core\exception\Forbidden;
use go\core\jmap\Entity;
use go\core\orm\CustomFieldsTrait;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\notes\model\NoteBook;


class User extends Entity {
	
	use CustomFieldsTrait;

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

	protected $permissionLevel;

	
	public function getDateTimeFormat() {
		return $this->dateFormat . ' ' . $this->timeFormat;
	}

	/**
	 *
	 * @var Password
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
			->addScalar('groups', 'core_user_group', ['id' => 'userId'])
			->addHasOne('workingWeek', WorkingWeek::class, ['id' => 'user_id']);
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
	public function getPersonalGroup() {
		if(empty($this->personalGroup)){
			$this->personalGroup = Group::find()->where(['isUserGroupFor' => $this->id])->single();
		}

		return $this->personalGroup;
	}

	public function setPersonalGroup($values) {
		$this->getPersonalGroup()->setValues($values);
	}
	
	public function setValues(array $values)
	{
		$this->passwordVerified = false;
		return parent::setValues($values);
	}


	public function setArchive($v) {
		if(!go()->getAuthState()->isAdmin()) {
			throw new Forbidden("Only admins can archive");
		}
		$this->archive = $v;
	}

	protected function canCreate()
	{
		return go()->getAuthState()->isAdmin();
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
	public function checkPassword($password) {		
		
		$authenticator = $this->getPrimaryAuthenticator();
		if(!isset($authenticator)) {
			throw new Exception("No primary authenticator found!");
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

	/**
	 * Check if this user has a password stored in the database.
	 * 
	 * Used by authenticators (IMAP or LDAP) so they can clear it if it's not needed.
	 * 
	 * @return bool
	 */
	public function hasPassword() {
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
	public function clearPassword() {
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
		
		return App::get()->getAuthState()->isAdmin();		
	}
	
	protected function internalValidate() {
		
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
			$id = \go\core\model\User::find()->selectSingleValue('id')->where(['email' => $this->email])->single();
			
			if($id && $id != $this->id){
				$this->setValidationError('email', ErrorCode::UNIQUE, 'The e-mail address must be unique in the system');
			}
		}

		$this->validateMaxUsers();

		if($this->isModified(['timezone'])) {
			try {
				$timezone= new DateTimeZone($this->timezone);
			} catch(Exception $e) {
				$this->setValidationError('timezone', ErrorCode::INVALID_INPUT, go()->t("Invalid timezone"));
			}
		}
		
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
	  if(empty(go()->getConfig()['core']['limits']['maxUsers'])) {
	    return false;
    }

		$stmt = go()->getDbConnection()->query("SELECT count(*) AS count FROM `core_user` WHERE enabled = 1");
		$record = $stmt->fetch();
		$countActive = $record['count'];
		return $countActive >= go()->getConfig()['core']['limits']['maxUsers'];
	}

	private static function count() {
		return (int) (new Query())
						->selectSingleValue('count(*)')
						->from('core_user')
						//->where('deletedAt is null')
						->single();
	}


	public function getPermissionLevel()
	{
		if($this->id == App::get()->getAuthState()->getUserId()) {
			return Acl::LEVEL_WRITE;
		}

		return parent::getPermissionLevel();
	}
	
	protected static function textFilterColumns() {
		return ['username', 'displayName', 'email'];
	}
	
	protected static function defineFilters() {
		return parent::defineFilters()
      ->add('permissionLevel', function(Criteria $criteria, $value, Query $query) {
        if(!$query->isJoined('core_group', 'g')) {
          $query->join('core_group', 'g', 'u.id = g.isUserGroupFor');
        }
        Acl::applyToQuery($query, 'g.aclId', $value);
      })
      ->add('showDisabled', function (Criteria $criteria, $value){
        if($value === false) {
          $criteria->andWhere('enabled', '=', true);
        }
      })
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
	public function isAdmin() {
		return (new Query)
			->select('*')
			->from('core_user_group')
			->where(['groupId' => Group::ID_ADMINS, 'userId' => $this->id])->single() !== false;
	}

  /**
   * Alias for making isAdmin() a public property
   * @return bool
   * @throws Exception
   */
	public function getIsAdmin() {
		return $this->isAdmin();
	}

	private static $authMethods;

	public static function findAuthMethods() {
		if(!isset(self::$authMethods)) {
			self::$authMethods = Method::find()->orderBy(['sortOrder' => 'DESC']);
		}
		return self::$authMethods;
	}

	/**
	 * Get available authentication methods
	 * 
	 * @return Method[]
	 */
	public function getAuthenticationMethods() {

		$methods = [];

		$authMethods = self::findAuthMethods();

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
   * @throws Exception
   */
	public function sendRecoveryMail($to, $redirectUrl = ""){
		
		$this->recoveryHash = bin2hex(random_bytes(20));
		$this->recoverySendAt = new DateTime();

		if(!$this->save()) {
			throw new \Exception("Could not save user");
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
			throw new \Exception("Could not send mail. The notication system setttings may be incorrect.");
		}
	}
	
	protected function internalSave() {
		
		if(isset($this->plainPassword)) {
			$this->password = $this->passwordHash($this->plainPassword);
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
		
		return true;		
	}
	
	/**
	 * Hash a password for users
	 * 
	 * @param string $password
	 * @return string
	 */
	public static function passwordHash($password) {
		return password_hash($password, PASSWORD_DEFAULT);
	}
	
	private function saveContact() {
		
//		if(!isset($this->contact) ){// || $this->isModified(['displayName', 'email', 'avatarId'])) {
//			$this->contact = $this->getProfile();
//		}
		
		if(!isset($this->contact)) {			
			return true;
		}
		
		$this->contact->photoBlobId = $this->avatarId;
		if(!isset($this->contact->emailAddresses[0])) {
			$this->contact->emailAddresses = [(new \go\modules\community\addressbook\model\EmailAddress())->setValues(['email' => $this->email])];
		}
		if(empty($this->contact->name) || $this->isModified(['displayName'])) {
			$this->contact->name = $this->displayName;
			$parts = explode(' ', $this->displayName);
			$this->contact->firstName = array_shift($parts);
			$this->contact->lastName = implode(' ', $parts);		
		}
		
		$this->contact->goUserId = $this->id;
		return $this->contact->save();
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
	
	private function createPersonalGroup() {
		if($this->isNew() || $this->isModified('groups')) {				
			if($this->isNew()){// !in_array($this->getPersonalGroup()->id, $groupIds)) {
				$personalGroup = new Group();
				$personalGroup->name = $this->username;
				$personalGroup->isUserGroupFor = $this->id;
				$personalGroup->users[] = $this->id;
				
				if(!$personalGroup->save()) {
					throw new Exception("Could not create home group");
				}

				$this->personalGroup = $personalGroup;
			} else
			{
				$personalGroup = $this->getPersonalGroup();
			}
			
			if(!in_array($personalGroup->id, $this->groups)) {			
				$this->groups[] = $personalGroup->id;
			}			
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
	public function hasModule($package, $name) {
		return Module::isAvailableFor($package, $name, $this->id);		
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
			return go()->getStorageQuota();
		}
	}
	
	public function getStorageFreeSpace() {
		if(!empty($this->disk_quota)) {
			return $this->disk_quota*1024*1024 - $this->disk_usage;
		} else
		{
			return go()->getStorageFreeSpace();
		}
	}
	
	protected static function internalDelete(Query $query) {

		$query->andWhere('id != 1');
				
		go()->getDbConnection()->beginTransaction();

		if(!static::legacyOnDelete($query) || !parent::internalDelete($query)) {
			go()->getDbConnection()->rollBack();
			return false;
		}

		return go()->getDbConnection()->commit();
	}
	
	
	public static function legacyOnDelete(Query $query) {

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
	 * @throws \go\core\exception\ConfigurationException
	 */
	public static function getAuthenticationDomains() {
		
		$domains = go()->getCache()->get("authentication-domains");
		if(is_array($domains)) {
			return $domains;
		}
		
		
		$classFinder = new \go\core\util\ClassFinder();
		$classes = $classFinder->findByParent(\go\core\auth\DomainProvider::class);
		
		$domains = [];
		foreach($classes as $cls) {
			$domains = array_merge($domains, $cls::getDomainNames());
		}
		
		go()->getCache()->set("authentication-domains", $domains);
		
		return $domains;		
	}
	
	/**
	 *
	 * @var \go\modules\community\addressbook\model\Contact
	 */
	private $contact;
	
	public function getProfile() {
		if(!Module::findByName('community', 'addressbook')) {
			return null;
		}
		
		$contact = \go\modules\community\addressbook\model\Contact::findForUser($this->id);
		if(!$contact) {
			$contact = new \go\modules\community\addressbook\model\Contact();
			$contact->addressBookId = go()->getSettings()->userAddressBook()->id;				
		}
		
		return $contact;
	}
	
	public function setProfile($values) {
		if(!Module::findByName('community', 'addressbook')) {
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
	public static function converters()
	{
		return array_merge(parent::converters(), [UserCsv::class]);
	}

	/**
	 * Decorate the message for newsletter sending.
	 * This function should at least add the to address.
	 *
	 * @param Message $message
	 * @return bool
	 */
	public function decorateMessage(Message $message) {
		$message->setTo($this->email, $this->displayName);
	}

	private $country;

	public function getCountry() {
		if(!isset($this->country)) {
			$tz = new \DateTimeZone($this->timezone);
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
	 */
	private function archiveUser()
	{
		$aclIds = [];

		if ($defAddressBookId = $this->addressBookSettings->getDefaultAddressBookId()) {
			$addressBook = AddressBook::findById($defAddressBookId);
			$aclIds[] = $addressBook->findAclId();
			AddressBook::entityType()->change($addressBook);
		};
		if ($defNoteBookId = $this->notesSettings->getDefaultNoteBookId()) {
			$noteBook = NoteBook::findById($defNoteBookId);
			$aclIds[] = $noteBook->findAclId();
			NoteBook::entityType()->change($noteBook);
		}
		if ($defTaskListId = $this->taskSettings->default_tasklist_id) {
			$aclIds[] = \GO\Tasks\Model\Tasklist::model()->findByPk($defTaskListId)->findAclId();
		}
		if ($calendarId = $this->calendarSettings->calendar_id) {
			$aclIds[] = \GO\Calendar\Model\Calendar::model()->findByPk($calendarId)->findAclId();
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
}
