<?php /** @noinspection PhpUnused */

namespace go\core\model;

use DateTimeZone;
use Exception;
use GO\Base\Model\AbstractUserDefaultModel;
use GO\Base\Model\User as LegacyUser;
use GO\Base\Util\Http;
use GO\Calendar\Model\Calendar;
use GO\Calendar\Model\UserSettings as CalendarUserSettings;
use go\core\acl\model\AclItemEntity;
use go\core\App;
use go\core\auth\Authenticate;
use go\core\auth\BaseAuthenticator;
use go\core\auth\DomainProvider;
use go\core\convert\UserSpreadsheet;
use go\core\data\Model;
use go\core\db\Column;
use go\core\db\Criteria;
use go\core\Environment;
use go\core\exception\ConfigurationException;
use go\core\exception\NotFound;
use go\core\Installer;
use go\core\jmap\Request;
use go\core\mail\Address;
use go\core\mail\Message;
use go\core\mail\Util;
use go\core\orm\exception\SaveException;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\PrincipalTrait;
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
use go\modules\community\addressbook\model\UserSettings;
use go\modules\community\notes\model\NoteBook;
use go\modules\community\notes\model\UserSettings as NotesUserSettings;
use go\modules\community\tasks\model\Task;
use go\modules\community\tasks\model\TaskList;
use go\modules\community\tasks\model\UserSettings as TasksUserSettings;
use http\Exception\InvalidArgumentException;


/**
 * @property ?TasksUserSettings $tasksSettings
 * @property ?NotesUserSettings $notesSettings
 * @property ?UserSettings $addressBookSettings
 * @property ?CalendarUserSettings $calendarSettings
 */
class User extends AclItemEntity {
	
	use CustomFieldsTrait;
	use PrincipalTrait;

	const ID_SUPER_ADMIN = 1;

	/**
	 * Fires when the password is verified during login via the web only.
	 * Login might not be complete.
	 *
	 * @param User $user
	 * @param string $password
	 */
	const EVENT_PASSWORD_VERIFIED = 'passwordverified';

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
	
	private bool $validatePassword = true;

	/**
	 * Check or set if password validation is enabled
	 *
	 * @param bool|null $enabled Supply if you want to change the setting
	 * @return bool
	 */
	public function validatePasswordEnabled(bool $enabled = null) {

		$old = $this->validatePassword;

		if(isset($enabled)) {
			$this->validatePassword = $enabled;
		}

		return $old;
	}

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

	/**
	 * User profile picture blob ID. Can be downloaded via the download endpoint.
	 *
	 * @var string
	 */
	public $avatarId;

	/**
	 * Enabled for login
	 *
	 * @var bool
	 */
	public $enabled;
	/**
	 * E-mail address
	 * 
	 * @var string
	 */
	public $email;


	/**
	 * Flag indicating if the user is allowed to receive newsletters
	 *
	 * @var bool
	 */
	public $newsletterAllowed = true;

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
	public $theme;
	public $themeColorScheme;
	public $firstWeekday;
	public $sort_name;

	public $mute_sound;
	public $mute_reminder_sound;
	public $mute_new_mail_sound;
	public $show_smilies;
	public $auto_punctuation;


	/**
	 * @var bool
	 */
	public $enableSendShortcut = true;
	
	
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
	
	protected ?DateTime $passwordModifiedAt;
	public bool $forcePasswordChange = false;

	public function getDateTimeFormat(): string
	{
		return $this->dateFormat . ' ' . $this->timeFormat;
	}

	/**
	 * The user password hashed
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
	private ?bool $passwordVerified = null;

	public $clients;


	public function isPasswordVerified() : ?bool {
		return $this->passwordVerified;
	}

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('core_user', 'u')
			->addTable('core_auth_password', 'p', ['id' => 'userId'])
			->addMap('clients', Client::class, ['id' => 'userId'])
			->addScalar('groups', 'core_user_group', ['id' => 'userId']);
	}

public function historyLog(): bool|array
{
	$log = parent::historyLog();

	if(isset($log['otp'])) {
		if(isset($log['otp'][0])) {
			$log['otp'][0] = "MASKED";
		}

		if(isset($log['otp'][1])) {
			$log['otp'][1] = "MASKED";
		}
	}

	if(isset($log['password'])) {
		if(isset($log['password'][0])) {
			$log['password'][0] = "MASKED";
		}

		if(isset($log['password'][1])) {
			$log['password'][1] = "MASKED";
		}
	}

	if(isset($log['recoveryHash'])) {
		if(isset($log['recoveryHash'][0])) {
			$log['recoveryHash'][0] = "MASKED";
		}

		if(isset($log['password'][1])) {
			$log['recoveryHash'][1] = "MASKED";
		}
	}

	return $log;
}


	/**
	 * @var Group
	 */
	private $personalGroup;

  /**
   * Get the user's personal group used for granting permissions
   *
   * @return Group
   */
	public function getPersonalGroup(): ?Group
	{
		if(empty($this->personalGroup)){
			$this->personalGroup = Group::find()->where(['isUserGroupFor' => $this->id])->single();
			if(!$this->personalGroup) {
				$this->createPersonalGroup();
			}
		}

		return $this->personalGroup;
	}

	/**
	 * @throws Exception
	 */
	public function setPersonalGroup($values) {
		$this->getPersonalGroup()->setValues($values);
	}
	
	public function setValues(array $values, bool $checkAPIRights = true) : Model
	{
		$this->passwordVerified = null;
		return parent::setValues($values, $checkAPIRights);
	}


	/**
	 * @throws Forbidden
	 */
	public function setArchive($v)
	{
		if(!go()->getAuthState()->isAdmin()) {
			throw new Forbidden("Only admins can archive");
		}
		$this->archive = $v;
	}

	protected function canCreate(): bool
	{
		return go()->getModel()->getUserRights()->mayChangeUsers;
	}

	/** @noinspection PhpCastIsUnnecessaryInspection */
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
		$passwordLen = strlen($password);
		if($passwordLen > go()->getSettings()->passwordMaxLength) {
			return false;
		}
		return password_verify($password, $this->password);
	}

	private $plainPassword;
	
	public function plainPassword() {
		return $this->plainPassword;
	}

	public function setPassword($password, $recoveryHashChecked = false) {
		$this->recoveryHash = null;
		$this->recoverySendAt = null;
		$this->plainPassword = $password;
		if ($recoveryHashChecked) {
			$this->passwordVerified = true;
		}
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
   * @depcreated Please remove after some time as the recovery hash check was refactored in the API auth script
	public function checkRecoveryHash(string $hash): bool
	{
		if($hash === $this->recoveryHash) {
			$this->passwordVerified = true;
			$this->recoveryHash = null;
			return true;
		}
		return false;
	}
  */

	private function validatePasswordChange(): bool
	{

		if($this->passwordVerified || (go()->getAuthState() && go()->getAuthState()->isAdmin())) {
			return true;
		}

		if(!$this->isModified(['password']) || $this->getOldValue('password') == null) {
			return true;
		}

		if(App::get()->getInstaller()->isInProgress()) {
			return true;
		}

		return false;
	}

	protected function internalValidate() {

		if(!isset($this->homeDir) && in_array("homeDir", $this->selectedProperties)) {
			$this->homeDir = "users/" . $this->username;
		}

		if(empty($this->recoveryEmail) && in_array("recoveryEmail", $this->selectedProperties)) {
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
			if($this->passwordVerified === null) {
				$this->setValidationError('currentPassword', ErrorCode::REQUIRED);
			} else {
				$this->setValidationError('currentPassword', ErrorCode::INVALID_INPUT);
			}
		}

		if(isset($this->plainPassword) && $this->validatePassword) {
			$passwordLen = strlen($this->plainPassword);
			if($passwordLen < go()->getSettings()->passwordMinLength) {
				$this->setValidationError('password', ErrorCode::INVALID_INPUT, "Minimum password length is ".go()->getSettings()->passwordMinLength." chars");
			}

			if($passwordLen > go()->getSettings()->passwordMaxLength) {
				$this->setValidationError('password', ErrorCode::INVALID_INPUT, "Maximum password length is ".go()->getSettings()->passwordMaxLength." chars");
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

	/**
	 * @throws Exception
	 */
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
      })
			->addText("username", function(Criteria $criteria, $comparator, $value) {
				$criteria->where('username', $comparator, $value);
			})
			->addText("email", function(Criteria $criteria, $comparator, $value) {
				$criteria->where('email', $comparator, $value);
			})
			->addText("displayName", function(Criteria $criteria, $comparator, $value) {
				$criteria->where('displayName', $comparator, $value);
			});
	}

	private $isAdmin;

  /**
   * Check if use is an admin
   *
   * @return boolean
   */
	public function isAdmin(): bool
	{
		if(!isset($this->isAdmin)) {
			$this->isAdmin = !!(new Query)
				->select()
				->from('core_user_group')
				->where(['groupId' => Group::ID_ADMINS, 'userId' => $this->id])
				->single();
		}

		return $this->isAdmin;
	}

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
   */
	public function getIsAdmin(): bool
	{
		return $this->isAdmin();
	}


	/**
	 * Check if this user is in a group
	 *
	 * @param int $groupId
	 * @return bool
	 * @throws Exception
	 */
	public function isInGroup(int $groupId): bool
	{
		if(!$this->isFetched("groups")) {
			$this->fetchRelation("groups");
		}

		return in_array($groupId, $this->groups);
	}

	private array $authenticators;


	/**
	 * Get available authentication methods
	 * 
	 * @return BaseAuthenticator[]
	 */
	public function getAuthenticators(): array
	{

		if(isset($this->authenticators)) {
			return $this->authenticators;
		}

		$this->authenticators = [];

		$auth = new Authenticate();
		$primary = $auth->getPrimaryAuthenticatorForUser($this->username);

		if($primary) {
			$this->authenticators[] = $primary;
		}

		foreach ($auth->getSecondaryAuthenticatorsForUser($this->username) as $authenticator) {
			$this->authenticators[] = $authenticator;
		}

		return $this->authenticators;
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
			->setTo(new Address(!empty($to) ? $to : $this->recoveryEmail, $this->displayName))
			->setSubject(go()->t('Lost password'))
			->setBody($emailBody);
		
		$message->send();

	}
	
	protected function internalSave(): bool
	{
		if($this->archive) {
			$this->groups = [Group::ID_EVERYONE, $this->getPersonalGroup()->id];
		}
		
		if(isset($this->plainPassword)) {
			$this->password = $this->passwordHash($this->plainPassword);

			if(!$this->isModified(['forcePasswordChange'])) {
				$this->forcePasswordChange = false;
			}

			if(!$this->isNew()) {

				//remove persistent remember me cookies on password change
				if(!RememberMe::delete(['userId' => $this->id])) {
					return false;
				}
			}
		}

		if($this->isModified(['password'])) {
			$this->passwordModifiedAt = new DateTime();
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
		$this->checkPersonalGroup();

		if($this->isNew()) {
			$this->legacyOnSave();	
		}

		if($this->archive) {
			$this->archiveUser();
		}

		$this->changeHomeDir();

		if($this->isModified(['password'])) {
			Token::destroyOtherSessons($this->id);
		}

		return true;
	}


	protected function internalGetModified(array|string &$properties = [], bool $forIsModified = false): bool|array
	{
		if(!is_array($properties)) {
			$properties = [$properties];
		}
		// check if it's empty because the parent method will fill it with all props
		$allProps = empty($properties);

		$modified = parent::internalGetModified($properties, $forIsModified);

		if($forIsModified && $modified) {
			return true;
		}

		// Add contact profile
		if (($allProps || in_array('profile', $properties)) && isset($this->contact) && $this->contact->isModified()) {
			if($forIsModified) {
				return true;
			}

			$modified['profile'] = $this->contact->getModified();
		}

		return $modified;
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
		$passwordLen = strlen($password);

		if($passwordLen > go()->getSettings()->passwordMaxLength) {
			throw new \InvalidArgumentException("Maximum password length is ".go()->getSettings()->passwordMaxLength." chars");
		}
		return password_hash($password, PASSWORD_DEFAULT);
	}

	/**
	 * @throws Exception
	 */
	private function saveContact(): bool
	{
		$contact = $this->getProfile();

		if(!$contact) {
			return true;
		}

		if(!$this->isModified(['displayName', 'email', 'avatarId']) && !$contact->isModified()) {
			return true;
		}

		$contact->photoBlobId = $this->avatarId;

		$compare = $this->isModified('email') ? $this->getOldValue("email") : $this->email;
		if($this->isModified("email")) {
			$hasEmail = false;
			foreach ($contact->emailAddresses as $emailAddress) {
				if ($emailAddress->email == $compare) {
					$hasEmail = $emailAddress;
					break;
				}
			}

			if (!$hasEmail) {
				$contact->emailAddresses[] = (new EmailAddress($contact))->setValues(['email' => $this->email]);
			} else if($hasEmail != $this->email) {
				$hasEmail->email = $this->email;
			}
		}

		if (empty($contact->name) || ($this->isModified(['displayName']) && $contact->name != $this->displayName)) {
			$contact->name = $this->displayName;
			$parts = explode(' ', $this->displayName);
			$contact->firstName = array_shift($parts);
			$contact->middleName = "";
			$contact->lastName = implode(' ', $parts);
		}

		$contact->goUserId = $this->id;

		return $contact->save();
	}



	/**
	 * @throws SaveException
	 * @throws Exception
	 */
	private function checkPersonalGroup()
	{
		if ($this->isNew() || $this->isModified(['groups', 'username'])) {
			if ($this->isNew()) {// !in_array($this->getPersonalGroup()->id, $groupIds)) {
				$personalGroup = $this->createPersonalGroup();
			} else {
				$personalGroup = $this->getPersonalGroup();
				if ($this->isModified('username')) {
					$personalGroup->name = $this->username;
					if (!$this->appendNumberToGroupNameIfExists($personalGroup)) {
						throw new SaveException($personalGroup);
					}
				}
			}

			if (!in_array($personalGroup->id, $this->groups)) {
				$this->groups[] = $personalGroup->id;
			}
		}
	}

	private function createPersonalGroup(): Group
	{
		$personalGroup = new Group();
		$personalGroup->name = $this->username;
		$personalGroup->isUserGroupFor = $this->id;
		$personalGroup->users[] = $this->id;

		if (!$this->appendNumberToGroupNameIfExists($personalGroup)) {
			throw new SaveException($personalGroup);
		}

		$this->personalGroup = $personalGroup;

		return $personalGroup;
	}

	private function appendNumberToGroupNameIfExists(Group $personalGroup): bool {
		$i = 0;
		$name = $personalGroup->name;

		while (!$personalGroup->save()) {
			$personalGroup->name = $name .' (' . ++$i .')';
			if($i == 10) {
				//give up
				return false;
			}
		}

		return true;
	}

	public function legacyOnSave() {
		//for old framework. Remove when all is refactored!
		$defaultModels = AbstractUserDefaultModel::getAllUserDefaultModels($this->id);
		/** @noinspection PhpUnhandledExceptionInspection */
		$user = LegacyUser::model()->findByPk($this->id, false, true);
		foreach($defaultModels as $model){
			$model->getDefault($user);
		}
	}
	
	public function currentClient() : ?Client {
        if(Environment::get()->isCli()) {
            $where = [
                'userId' => $this->id,
                'ip' => 'CLI',
                'platform' => 'CLI',
                'name' => 'CLI'
            ];
        } else {
            $ua_info = \donatj\UserAgent\parse_user_agent();
            $where = [
                'userId' => $this->id,
                'ip' => Request::get()->getRemoteIpAddress() ?? 'CLI',
                'platform' => $ua_info['platform'],
                'name' => $ua_info['browser']
            ];
        }

		return Client::internalFind([],false, $this)->where($where)->single();
	}

	public function clientByDevice($deviceId) {
		if($deviceId === '-'){
			return null;
		}
		$client = Client::internalFind([],false, $this)->where(['deviceId' => $deviceId, 'userId' => $this->id])->single();
		if(empty($client)) {
			$client = new Client($this);
			$client->userId = $this->id;
			$client->deviceId = $deviceId;
			return $client;
		}
		return $client;
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
	 * @param ?string $package
	 * @param string $name
	 * 
	 * @return boolean
	 */
	public function hasModule(?string $package,string $name): bool
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

		$query->andWhere($query->getTableAlias() . '.id != 1');

		go()->getDbConnection()->delete('go_settings', (new Query)->where('user_id', 'in', $query))->execute();
		//go()->getDbConnection()->delete('go_reminders', (new Query)->where('user_id', 'in', $query))->execute();
		go()->getDbConnection()->delete('go_reminders_users', (new Query)->where('user_id', 'in', $query))->execute();

		Group::delete( (new Query)->where('isUserGroupFor', 'in', $query));

		if(!static::legacyOnDelete($query) || !parent::internalDelete($query)) {
			go()->getDbConnection()->rollBack();
			return false;
		}

		return true;
	}

	public static function legacyOnDelete(Query $query): bool
	{
		foreach($query as $pk) {
			/** @noinspection PhpUnhandledExceptionInspection */
			$user = LegacyUser::model()->findByPk($pk['id'], false, true);
			LegacyUser::model()->fireEvent("beforedelete", [$user, true]);
			//delete all acl records
			$defaultModels = AbstractUserDefaultModel::getAllUserDefaultModels();

			foreach($defaultModels as $model){
				$model->deleteByAttribute('user_id',$pk['id']);
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
	public function getProfile(): ?Contact
	{
		if(!Module::isInstalled('community', 'addressbook', true)) {
			return null;
		}

		if(isset($this->contact)) {
			return $this->contact;
		}
		
		$this->contact = !$this->isNew() ? Contact::findForUser($this->id) : null;
		if(!$this->contact) {
			$this->contact = new Contact();
			$this->contact->addressBookId = go()->getSettings()->userAddressBook()->id;
			if($this->email) {
				$this->contact->emailAddresses[0] = (new EmailAddress($this->contact))->setValues(['email' => $this->email]);
			}
			$nameParts = explode(" ", $this->displayName);
			$this->contact->firstName = array_shift($nameParts);
			$this->contact->middleName = "";
			$this->contact->lastName = implode(" ", $nameParts);
		}
		
		return $this->contact;
	}

	/**
	 * @throws Exception
	 */
	public function setProfile($values) {
		if(!Module::isInstalled('community', 'addressbook')) {
			throw new Exception("Can't set profile without address book module.");
		}
		if(isset($values['id'])) {
			$contact = \go\modules\community\addressbook\model\Contact::findById($values['id']);
			if(!empty($contact)){
				$this->contact = $contact;
			}
		} else {
			$this->contact = $this->getProfile();
			$this->contact->setValues($values);
			if ($this->contact->isModified("name")) {
				$this->displayName = $this->contact->name;
			}
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
	public function decorateMessage(Message $message)
	{
		$message->setTo(new Address($this->email, $this->displayName));
	}

	private $country;

	/**
	 * Get ISO country code by using the timezone
	 *
	 * @return string|null
	 */
	public function getCountry() : ?string {
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
	 * @todo Make this modular?
	 */
	private function archiveUser()
	{
		$aclIds = [];

		if(Module::isInstalled("community", "addressbook")) {

			$addressBooks = AddressBook::find()->where('createdBy','=', $this->id);
			foreach($addressBooks as $addressBook) {
				$aclIds[] = $addressBook->findAclId();
				AddressBook::entityType()->change($addressBook);
			}
			$profile = Contact::find()->where('goUserId', '=', $this->id)->single();
			if($profile) {
				$archivedAb = go()->getSettings()->archivedUsersAddressBook();
				$profile->addressBookId = $archivedAb->id;
				$profile->save();
			}
		}

		if(Module::isInstalled("community", "notes")) {
			$noteBooks = NoteBook::find()->where('createdBy','=', $this->id);
			foreach($noteBooks as $noteBook) {
				$aclIds[] = $noteBook->findAclId();
				NoteBook::entityType()->change($noteBook);
			}
		}

		if(Module::isInstalled("community", "tasks")) {
			$taskLists  = TaskList::find()->where('createdBy','=', $this->id)->andWhere('role','=', TaskList::List);
			foreach($taskLists as $taskList) {
				$aclIds[] = $taskList->findAclId();
				TaskList::entityType()->change($taskList);
			}
		}

		if(Module::isInstalled("legacy", "calendar")) {
			if (($calendarId = $this->calendarSettings->calendar_id) && ($calendar = Calendar::model()->findByPk($calendarId))) {
				$aclIds[] = $calendar->findAclId();
			}
		}

		if (Module::isInstalled("legacy", "projects2")) {
			go()->getDbConnection()->delete('pr2_default_resources', ['user_id' => $this->id] )->execute();
		}

		if (count($aclIds)) {
			$grpId = $this->getPersonalGroup()->id();
			foreach (Acl::findByIds($aclIds) as $rec) {
				foreach ($rec->groups as $aclGrp) {
					if ($aclGrp->groupId != $grpId) {
						$rec->removeGroup($aclGrp->groupId);
					}
				}
				$rec->save();
			}
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

	public function findAclId(): ?int
	{
		return $this->getPersonalGroup()->findAclId();
	}


	public static function findOrCreateByUsername(string $username, string $email, string $displayName, array $values = [], array $properties = []) : User {

		$user = User::find($properties)
			->where(['username' => $username])
			->orWhere(['email' => $email])
			->single();
		if (!$user) {

			if(!go()->getSettings()->allowRegistration) {
				throw new NotFound("User not found and registration not allowed");
			}

			$user = new User();
			$user->setValues($values);
			$user->username = $username;
			$user->email = $email;
			$user->displayName = $displayName;
			if(!isset($user->recoveryEmail)) {
				$user->recoveryEmail = $email;
			}
			if (!$user->save()) {
				throw new SaveException($user);
			}
		}

		return $user;
	}

	protected static function aclEntityClass(): string
	{
		return Group::class;
	}

	protected static function aclEntityKeys(): array
	{
		return ['id' => 'isUserGroupFor'];
	}

	protected function principalAttrs(): array
	{
		return [
			'name' => $this->displayName,
			'description' => $this->username,
			'email' => $this->email,
			'timeZone' => $this->timezone,
			'avatarId' => $this->avatarId
		];
	}

	protected function isPrincipalModified(): bool
	{
		return $this->isModified(['displayName', 'email', 'username', 'timezone', 'avatarId']) !== false;
	}

	protected function principalType(): string
	{
		return Principal::Individual;
	}
}
