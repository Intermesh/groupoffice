<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: LinkedEmail.php 7607 2011-09-01 15:38:01Z <<USERNAME>> $
 * @copyright Copyright Intermesh
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */

namespace GO\Email\Model;

use GO;

/**
 * The LinkedEmail model
 *
 * @property boolean $ignore_sent_folder
 * @property int $password_encrypted
 * @property string $smtp_password
 * @property string $smtp_username
 * @property string $smtp_encryption 'ssl' or 'tls'
 * @property boolean $smtp_allow_self_signed Allow SSL/TLS and STARTTLS connection with self signed certificates. Enabling this will not check the identity of the server
 * @property boolean $imap_allow_self_signed Allow SSL/TLS and STARTTLS connection with self signed certificates. Enabling this will not check the identity of the server
 * @property string $imap_encryption 'ssl' or 'tls'
 * @property int $smtp_port
 * @property StringHelper $smtp_host
 * @property StringHelper $spam
 * @property StringHelper $trash
 * @property StringHelper $drafts
 * @property StringHelper $sent
 * @property StringHelper $mbroot
 * @property StringHelper $password
 * @property StringHelper $username
 * @property boolean $novalidate_cert
 * @property boolean $deprecated_use_ssl
 * @property boolean $do_not_mark_as_read
 * @property int $port
 * @property StringHelper $host
 * @property StringHelper $type
 * @property int $acl_id
 * @property int $user_id
 * @property int $id
 * @property StringHelper $check_mailboxes
 * @property boolean $signature_below_reply
 * @property int $sieve_port
 * @property boolean $sieve_tls
 * @property boolean $sieve_usetls
 */
class Account extends \GO\Base\Db\ActiveRecord {
	
	const ACL_DELEGATED_PERMISSION=15;
	
	/**
	 * Set to false if you don't want the IMAP connection on save.
	 * 
	 * @var boolean 
	 */
	public $checkImapConnectionOnSave=true;
	
	
	private $_imap;

	/**
	 * Set to false if you want to keep the password in the session only.
	 * 
	 * @var boolean 
	 */
	public $store_password=true;
	
	/**
	 * Set to false if, for example from the imapauth module, the smtp password
	 * should not be stored in the database, only in the session.
	 * @var boolean
	 */
	public $store_smtp_password=true;
	
	
	/**
	 * Holds the password temporaily while saving the account model without storing it in the database. ($this->store_password=false)
	 * 
	 * @var boolean 
	 */
	private $_session_password='';
	private $_session_smtp_password='';
	

	/**
	 * Returns a static model of itself
	 *
	 * @param String $className
	 * @return Account
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * Enable this function if you want this model to check the acl's automatically.
	 */
	public function aclField() {
		return 'acl_id';
	}

	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'em_accounts';
	}
	
	protected function init() {
		
		$this->columns['host']['required']=true;
		$this->columns['username']['required']=true;
		$this->columns['password']['required']=true;
		parent::init();
	}
	
	public function attributeLabels() {
		$attr = parent::attributeLabels();
		
		$attr['username']=\GO::t("Username");
		$attr['password']=\GO::t("Password");
		
		return $attr;
	}

	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 */
	public function relations() {
		return array(
				'aliases' => array('type'=>self::HAS_MANY, 'model'=>'GO\Email\Model\Alias', 'field'=>'account_id','delete'=>true),
				'filters' => array('type'=>self::HAS_MANY, 'model'=>'GO\Email\Model\Filter', 'field'=>'account_id','delete'=>true, 'findParams'=>  \GO\Base\Db\FindParams::newInstance()->order("priority")),
				'portletFolders' => array('type'=>self::HAS_MANY, 'model'=>'GO\Email\Model\PortletFolder', 'field'=>'account_id','delete'=>true)
		);
	}

	protected function _trimSpacesFromAttributes() {
		if(!static::$trimOnSave)
			return;
		foreach($this->columns as $field=>$col){

			// For passwords it is allowed to apply spaces at the begin or end.
			if($field == 'password' || $field == 'smtp_password'){
				return;
			}
			
			if(isset($this->_attributes[$field]) && $col['type'] == \PDO::PARAM_STR){
				$this->_attributes[$field] = trim($this->_attributes[$field]);
			}
		}
	}
	
	protected function beforeSave() {
		if($this->isModified('password')){	
//			$decrypted = \GO\Base\Util\Crypt::decrypt($this->getOldAttributeValue('password'));
//			
//			if($decrypted==$this->password){
//				$this->resetAttribute('password');
//			}else
//			{
				$encrypted = \GO\Base\Util\Crypt::encrypt($this->password);		
				if($encrypted){
					$this->password = $encrypted;
					$this->password_encrypted=2;//deprecated. remove when email is mvc style.
				}
//			}
			
			unset(GO::session()->values['emailModule']['accountPasswords'][$this->id]);
		}

//		if (!empty($this->id) && !empty(GO::session()->values['emailModule']['accountPasswords'][$this->id]))
//			unset(GO::session()->values['emailModule']['accountPasswords'][$this->id]);
		
		if($this->isModified('smtp_password')){
			$encrypted = \GO\Base\Util\Crypt::encrypt($this->smtp_password);		
			if($encrypted)
				$this->smtp_password = $encrypted;
		}
		
		if(
				($this->isNew || $this->isModified("mbroot") || $this->isModified("host") || $this->isModified("port") || $this->isModified("username")  || $this->isModified("password") || $this->isModified("imap_encryption"))
				&& $this->checkImapConnectionOnSave
			){

			$imap = $this->openImapConnection();
			$this->mbroot=$imap->check_mbroot($this->mbroot);

			$this->_createDefaultFolder('sent');
			$this->_createDefaultFolder('trash');
			$this->_createDefaultFolder('spam');
			$this->_createDefaultFolder('drafts');	
		}
		
		if (empty($this->store_password)) {
			$this->_session_password = $this->password;
			$this->password = '';
			$this->password_encrypted = 0;
		}
		
		if (empty($this->store_smtp_password)) {
			$this->_session_smtp_password = $this->smtp_password;
			$this->smtp_password = '';
		}
		
		return parent::beforeSave();
	}

	protected function afterLoad() {		
		$this->store_smtp_password = $this->store_password = !isset(\GO::session()->values['emailModule']['accountPasswords'][$this->id]);
		
		return parent::afterLoad();
	}
	
	protected function afterSave($wasNew) {
		if (!empty($this->_session_password)) {
			
			if (!isset(\GO::session()->values['emailModule']) || !isset(\GO::session()->values['emailModule']['accountPasswords']) || !is_array(\GO::session()->values['emailModule']['accountPasswords'])) {
				\GO::session()->values['emailModule']['accountPasswords'] = array();
			}
			\GO::session()->values['emailModule']['accountPasswords'][$this->id] = $this->_session_password;
		}
		if (!empty($this->_session_smtp_password)) {
			if (!isset(\GO::session()->values['emailModule']) || !isset(\GO::session()->values['emailModule']['smtpPasswords']) || !is_array(\GO::session()->values['emailModule']['smtpPasswords'])) {
				\GO::session()->values['emailModule']['smtpPasswords'] = array();
			}
			\GO::session()->values['emailModule']['smtpPasswords'][$this->id] = $this->_session_smtp_password;
		}

		if ($wasNew) {
			Label::model()->createDefaultLabels($this->id);
		}
		
		if($wasNew) {
			$user = \go\core\model\User::findById($this->user_id);
			if($user->isAdmin()) {
				//add admin group
				$group = \go\core\model\Group::find()->where(['isUserGroupFor' => $user->id])->single();
				$this->getAcl()->addGroup($group->id, \go\core\model\Acl::LEVEL_MANAGE);
			}
		}

		return parent::afterSave($wasNew);
	}

	protected function afterDelete() {
		Label::model()->deleteAccountLabels($this->id);
		return true;
	}
		
	private $_mailboxes;

	/**
	 * Get all mailboxes on the active connection in a namesapce
	 * 
	 * @return array All mailboxes
	 */
	public function getMailboxes(){
		if(!isset($this->_mailboxes)){
			$this->_mailboxes= $this->openImapConnection()->get_folders($this->mbroot);
		}
		return $this->_mailboxes;
	}

	private $_subscribed;

	/**
	 * Get the mailboxes the user is subscribed to in a namespace
	 * 
	 * @return array Subscribed mailboxes
	 */
	public function getSubscribed(){
		if(!isset($this->_subscribed)){
			$this->_subscribed= $this->openImapConnection()->get_folders($this->mbroot, true);
		}
		return $this->_subscribed;
	}


	private function _createDefaultFolder($name){

		if(empty($this->$name))
			return false;

		$mailboxes = $this->getMailboxes();
		
		if(!isset($mailboxes[$this->$name])){			
			$imap = $this->openImapConnection();
			if(!$imap->create_folder($this->$name)){
				//clear errors like:
				//A5 NO Client tried to access nonexistent namespace. ( Mailbox name should probably be prefixed with: INBOX. )
				$imap->clear_errors();
				$this->mbroot= $this->openImapConnection()->check_mbroot("INBOX");

				$this->$name = $this->mbroot.$this->$name;

				if(!isset($mailboxes[$this->$name])){
					$imap->create_folder($this->$name);
				}
			}
		}
	}
	

	public function decryptPassword(){

		if (!empty(GO::session()->values['emailModule']['accountPasswords'][$this->id])) {
			$decrypted = \GO\Base\Util\Crypt::decrypt(GO::session()->values['emailModule']['accountPasswords'][$this->id]);
		} else {
			
			//support for z-push without storing passwords
			if (empty($this->password) &&	method_exists('Request','GetAuthPassword') && Request::GetAuthUser()==$this->username) {
				
				$decrypted = Request::GetAuthPassword();
			}else
			{			
				if(empty($this->password)) {
					return "";
				}
				$decrypted = \GO\Base\Util\Crypt::decrypt($this->password);
			}
		}
		
		return $decrypted ? $decrypted : $this->password;
	}

	public function decryptSmtpPassword(){
		if (!empty(\GO::session()->values['emailModule']['smtpPasswords'][$this->id])) {
			$decrypted = \GO\Base\Util\Crypt::decrypt(\GO::session()->values['emailModule']['smtpPasswords'][$this->id]);
		} else {
			
			//support for z-push without storing passwords
			if (empty($this->smtp_password) &&	method_exists('Request','GetAuthPassword') && Request::GetAuthUser()==$this->smtp_username) {
				
				$decrypted = Request::GetAuthPassword();
			}else
			{			
				$decrypted = \GO\Base\Util\Crypt::decrypt($this->smtp_password);
			}
		}
		
		return $decrypted ? $decrypted : $this->smtp_password;
	}
	
	/**
	 * Open a connection to the imap server.
	 *
	 * @param StringHelper $mailbox
	 * @return \GO\Base\Mail\Imap
	 */
	public function openImapConnection($mailbox = 'INBOX')
	{

		if (empty($mailbox)) {
			$mailbox = "INBOX";
		}

		$imap = $this->justConnect();

		if (!$imap->select_mailbox($mailbox)) {
			throw new \GO\Base\Mail\Exception\MailboxNotFound($mailbox, $imap);
			//throw new \Exception ("Could not open IMAP mailbox $mailbox\nIMAP error: ".$imap->last_error());
		}
		return $imap;
	}
	
	/**
	 * Connect to the IMAP server without selecting a mailbox
	 * 
	 * @return \GO\Base\Mail\Imap
	 */
	public function justConnect(){
		if(empty($this->_imap)){
			$this->_imap = new \GO\Base\Mail\Imap();	
			$this->_imap->ignoreInvalidCertificates = $this->imap_allow_self_signed;
			$useTLS = $this->imap_encryption == 'tls'?true:false;
			$useSSL = $this->imap_encryption == 'ssl'?true:false;
			$this->_imap->connect($this->host, $this->port, $this->username, $this->decryptPassword(), $useSSL, $useTLS);
		}else
		{
			$this->_imap->checkConnection();
		}

		return $this->_imap;
	}
	
	/**
	 * Close the connection to imap
	 */
	public function closeImapConnection(){
		if(!empty($this->_imap)){
			$this->_imap->disconnect();
			$this->_imap=null;		
		}
	}
	
	public function __wakeup() {
		//reestablish imap connection after deserialization
		$this->_imap=null;
	}
	
	/**
	 * Get the imap connection if it's open.
	 * 
	 * @return \GO\Base\Mail\Imap 
	 */
	public function getImapConnection(){
		if(isset($this->_imap)){
			return $this->_imap;
		}else
			return false;
	}
	
//	private function _getCacheKey(){
//		$user_id = \GO::user() ? \GO::user()->id : 0;
//		return $user_id.':'.$this->id.':uidnext';
//	}
	
//	protected function getHasNewMessages(){
//		
//		\GO::debug("getHasNewMessages UIDNext ".(isset($this->_imap->selected_mailbox['uidnext']) ? $this->_imap->selected_mailbox['uidnext'] : ""));
//		
//		if(isset($this->_imap->selected_mailbox['name']) && $this->_imap->selected_mailbox['name']=='INBOX' && !empty($this->_imap->selected_mailbox['uidnext'])){
//			
//			$cacheKey = $this->_getCacheKey();
//			
//			$uidnext = $value = \GO::cache()->get($cacheKey);
//			
//			\GO::cache()->set($cacheKey, $this->_imap->selected_mailbox['uidnext']);					
//			
//			if($uidnext!==false && $uidnext!=$this->_imap->selected_mailbox['uidnext']){
//				return true;
//			}			
//		}
//			
//		return false;
//	}


	/**
	 * Find an account by e-mail address.
	 *
	 * @param StringHelper $email
	 * @return Account
	 */
	public function findByEmail($email){

		$joinCriteria = \GO\Base\Db\FindCriteria::newInstance()
						->addRawCondition('t.id', 'a.account_id');

		$findParams = \GO\Base\Db\FindParams::newInstance()
						->single()
						->join(Alias::model()->tableName(), $joinCriteria,'a')
						->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('email', $email,'=','a'));

		return $this->find($findParams);
	}

	/**
	 * Get the default alias for this account.
	 *
	 * @return Alias
	 */
	public function getDefaultAlias(){
		return Alias::model()->findSingleByAttributes(array(
				'default'=>1,
				'account_id'=>$this->id
		));
	}
	
	/**
	 * Get an array of mailboxes that should be checked periodically for new mail
	 * 
	 * @return array
	 */
	public function getAutoCheckMailboxes(){
		$checkMailboxArray = empty($this->check_mailboxes) ? array() : explode(',',$this->check_mailboxes);
//		if(!in_array("INBOX", $checkMailboxArray))
//			$checkMailboxArray[]="INBOX";
		return $checkMailboxArray;
	}


	public function addAlias($email, $name, $signature='', $default=1){
		$a = new Alias();
		$a->account_id=$this->id;
		$a->email=$email;
		$a->name=$name;
		$a->signature=$signature;
		$a->default=$default;
		$a->save(true);

		return $a;
	}
	
	/**
	 *
	 * @return \ImapMailbox 
	 */
	public function getRootMailboxes($withStatus=false, $subscribed=false){
		$imap = $this->openImapConnection();
		
		$rootMailboxes = array();
	
		$folders = $imap->list_folders($subscribed,$withStatus,"","{$this->mbroot}%", true);
//		\GO::debug($folders);
		foreach($folders as $folder){
			$mailbox = new ImapMailbox($this,$folder);
			$rootMailboxes[]=$mailbox;
		}
		
		$namespaces = $imap ->get_namespaces();
		
		foreach($namespaces as $namespace){
			if($namespace['name']!=''){
				$namespace['noselect']=  strtoupper($namespace['name'])!='INBOX';
				$namespace['subscribed']=true;
				$rootMailboxes[]=new ImapMailbox($this, $namespace);
			}
		}
		
		return $rootMailboxes;
	}

	
	/**
	 *
	 * @return \ImapMailbox 
	 */
	public function getAllMailboxes($hierarchy=true, $withStatus=false){
		$imap = $this->openImapConnection();
		
		$folders = $imap->list_folders(true, $withStatus,'','*',true);
		
		//$node= array('name'=>'','children'=>array());
		
		$rootMailboxes = array();
		
		$mailboxModels =array();
		
		foreach($folders as $folder){
			$mailbox = new ImapMailbox($this,$folder);
			if($hierarchy){
				$mailboxModels[$folder['name']]=$mailbox;
				$parentName = $mailbox->getParentName();
				if($parentName===false){
					$rootMailboxes[]=$mailbox;
				}else{
					$mailboxModels[$parentName]->addChild($mailbox);
				}
			}else
			{
				$rootMailboxes[]=$mailbox;
			}
			
		}
		
		return $rootMailboxes;
	}
	
	public function defaultAttributes() {
		$attr = parent::defaultAttributes();
		
		$attr['check_mailboxes']="INBOX";
//		if (\GO::modules()->isInstalled('sieve')) {
			$attr['sieve_port'] = !empty(\GO::config()->sieve_port) ? \GO::config()->sieve_port : '4190';
			if (isset(\GO::config()->sieve_usetls))
				$attr['sieve_usetls'] = !empty(\GO::config()->sieve_usetls);
			else
				$attr['sieve_usetls'] = true;
//		}	
		return $attr;
	}

	public function getDefaultTemplate() {
//		if (\GO::modules()->addressbook) {
			$defaultAccountTemplateModel = \GO\Email\Model\DefaultTemplateForAccount::model()->findByPk($this->id);
			if (!$defaultAccountTemplateModel) {
				$defaultUserTemplateModel = \GO\Email\Model\DefaultTemplate::model()->findByPk(\GO::user()->id);
				if (!$defaultUserTemplateModel)
					return false;
				else
					return $defaultUserTemplateModel;
			} else {
				return $defaultAccountTemplateModel;
			}

	}
	
}
