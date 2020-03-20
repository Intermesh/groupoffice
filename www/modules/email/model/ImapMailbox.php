<?php

/**
 * @var string $name
 * @var boolean $noinferiors
 * @var boolean $marked
 * @var boolean $haschildren
 * @var boolean $hasnochildren
 * @var boolean $noselect
 * @var boolean $nonexistent
 * @var int $unseen
 * @var int $messages
 * @var string $delimiter

 */

namespace GO\Email\Model;

use GO;

class ImapMailbox extends \GO\Base\Model {

	/**
	 *
	 * @var Account
	 */
	private $_account;
	private $_children;

	/**
	 *
	 * @var StringHelper
	 */
	private $_attributes;

	public function __construct(Account $account, $attributes) {
		$this->_account = $account;

		//\GO::debug("GO\Email\Model\ImapMailbox:".var_export($attributes,true));

		$this->_attributes = $attributes;

//		if(isset($this->_attributes['name']))
//			$this->_attributes['name']=\GO\Base\Mail\Utils::utf7_decode($this->_attributes["name"]);

		//throw new \Exception(var_export($attributes, true));

		//$this->_children = array();
	}

	public function __isset($name) {
		$var = $this->__get($name);
		return isset($var);
	}

	public function __get($name) {

		$getter = "get".$name;
		if(method_exists($this, $getter))
			return $this->$getter();

		return isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
	}

	public function getHasChildren($asSubscribedMailbox=false)
	{
		if($this->isRootMailbox()) {
			return false;
		}

		//todo make compatible with servers that can't return subscribed flag

		if(isset($this->_attributes['haschildren']) && $this->_attributes['haschildren']) {
			return true;
		}
		if(isset($this->_attributes['hasnochildren']) && $this->_attributes['hasnochildren']) {
			return false;
		}

		if(isset($this->_attributes['noinferiors']) && $this->_attributes['noinferiors']) {
			return false;
		}

		//\GO::debug($this->_attributes['haschildren'])	;

		//oh oh, bad mailserver can't tell us if it has children. Let's find out the expensive way
		$folders = $this->getAccount()->openImapConnection()->list_folders($asSubscribedMailbox, false,"",$this->name.$this->delimiter.'%');
		//store values for caching
		$this->_attributes['haschildren']= count($folders)>0;
		$this->_attributes['hasnochildren']= count($folders)==0;
		return $this->_attributes['haschildren'];

	}

	public function getSubscribed(){

		//todo make compatible with servers that can't return subscribed flag

		return !empty($this->_attributes['subscribed']);

	}

	public function areFlagsPermitted()
	{
		return !empty(GO::config()->email_enable_labels);

		/**
		 * Use config until we found better way how detect flags from IMAP headers
		 */
		if(!isset($this->_attributes["permittedFlags"])) {
			$this->_attributes["permittedFlags"]=$this->getAccount()->openImapConnection ()->permittedFlags;
		}
		return $this->_attributes["permittedFlags"];
	}

	public function getDelimiter(){
		if(!isset($this->_attributes["delimiter"]))
			$this->_attributes["delimiter"]=$this->getAccount()->openImapConnection ()->get_mailbox_delimiter ();

		return $this->_attributes["delimiter"];
	}

	public function getParentName() {
		$pos = strrpos($this->name, $this->delimiter);

		if ($pos === false)
			return false;

		return substr($this->name, 0, $pos);
	}

//	public function getName($decode=false){
//		return $decode ? \GO\Base\Mail\Utils::utf7_decode($this->_attributes["name"]) : $this->_attributes["name"];
//	}

	public function getBaseName() {
		$name = $this->name;
		$pos = strrpos($name, $this->delimiter);

		if ($pos !== false)
			$name= substr($this->name, $pos + 1);


		return $name;
	}

	public function getDisplayName() {
		switch ($this->name) {
			case 'INBOX':
				return \GO::t("Inbox", "email");
				break;
			case $this->getAccount()->sent:
				return \GO::t("Sent items", "email");
				break;
			case $this->getAccount()->trash:
				return \GO::t("Trash", "email");
				break;
			case $this->getAccount()->drafts:
				return \GO::t("Drafts", "email");
				break;
			case 'Spam':
				return \GO::t("Spam", "email");
			default:
				return $this->getBaseName(true);
				break;
		}
	}

	public function addChild(ImapMailbox $mailbox) {
		if(!isset($this->_children)){
			$this->_children = array();
		}
		$this->_children[] = $mailbox;
	}

	public function isRootMailbox(){
		//throw new \Exception($this->name.$this->delimiter.' = '.$this->getAccount()->mbroot);
		return $this->name.$this->delimiter==$this->getAccount()->mbroot;
	}

	/**
	 * Get all child nodes of mailbox
	 * @param boolean $subscribed Only get subscribed folders
	 * @param boolean $withStatus Get the status of the folder (Unseen and message count)
	 * @return ImapMailbox[] the mailbox folders
	 */
	public function getChildren($subscribed=false, $withStatus=true) {
		if(!isset($this->_children)){

			$imap = $this->getAccount()->openImapConnection();

			$this->_children = array();

			if(!$this->isRootMailbox())
			{
				$folders = $imap->list_folders($subscribed,$withStatus,"","$this->name$this->delimiter%");
				foreach($folders as $folder){
					if (rtrim($folder['name'], $this->delimiter) != $this->name) {
						$mailbox = new ImapMailbox($this->account,$folder);
						$this->_children[]=$mailbox;
					}
				}
			}

		}

		return $this->_children;
	}

	/**
	 *
	 * @return Account
	 */
	public function getAccount() {
		return $this->_account;
	}

//	public function isSent(){
//		return $this->name==$this->_account->sent;
//	}

	public function rename($name){

	  if($this->getAccount()->getPermissionLevel() <= \GO\Base\Model\Acl::READ_PERMISSION)
		  throw new \GO\Base\Exception\AccessDenied();

		$name = trim($name);
		$this->_validateName($name);

		$parentName = $this->getParentName();
		$newMailbox = empty($parentName) ? $name : $parentName.$this->delimiter.$name;

//		throw new \Exception($this->name." -> ".$newMailbox);
		
		if($this->getAccount()->openImapConnection()->rename_folder($this->name, $newMailbox)) {
			$this->_attributes['name'] = $newMailbox;
			
			return true;
		}
		return false;
	}

	public function delete(){
	  if($this->getAccount()->getPermissionLevel() <= \GO\Base\Model\Acl::READ_PERMISSION)
		 throw new \GO\Base\Exception\AccessDenied();
		
		if($this->getHasChildren()) {
			
			foreach ($this->getChildren() as $mailBox) {
				
				
				if(!$mailBox->delete()) {
					return false;
				}
			}
			
		}
		
	  return $this->getAccount()->openImapConnection()->delete_folder($this->name);
	}

	public function truncate(){
	  if($this->getAccount()->getPermissionLevel() <= \GO\Base\Model\Acl::READ_PERMISSION)
		  throw new \GO\Base\Exception\AccessDenied();
		$imap = $this->getAccount()->openImapConnection($this->name);
		$sort = $imap->sort_mailbox();
		return $imap->delete($sort);
	}

	private function _validateName($name){
		$illegalChars = '/';

		if($this->getDelimiter()!='/'){
			$illegalChars .=$this->getDelimiter();
		}

		if(preg_match('/['.preg_quote($illegalChars,'/').']/', $name)){
			throw new \Exception(sprintf(\GO::t("The name contained one of the following illegal characters %s"),': '.$illegalChars));
		}else
		{
			return true;
		}
	}

	public function createChild($name, $subscribe=true){
	  if($this->getAccount()->getPermissionLevel() <= \GO\Base\Model\Acl::READ_PERMISSION)
		  throw new \GO\Base\Exception\AccessDenied();
		$name = trim($name);
		$newMailbox = empty($this->name) ? $name : $this->name.$this->delimiter.$name;

		$this->_validateName($name);

		//throw new \Exception($newMailbox);

		return $this->getAccount()->openImapConnection()->create_folder($newMailbox, $subscribe);
	}

	public function move(ImapMailbox $targetMailbox){
		if($this->getAccount()->getPermissionLevel() <= \GO\Base\Model\Acl::READ_PERMISSION) {
			throw new \GO\Base\Exception\AccessDenied();
		}
		$newMailbox = "";

		if(!empty($targetMailbox->name)) {
			$newMailbox .= $targetMailbox->name . $this->delimiter;
		}

		$newMailbox .= $this->getBaseName();

		$success = $this->getAccount()->openImapConnection()->rename_folder($this->name, $newMailbox);
		if(!$success) {
			return false;
		}
		$this->_attributes['name'] = $newMailbox;

		return true;
	}

	public function setSubscribed($value){
		if($value)
			$this->_attributes['subscribed'] =  $this->getAccount()->openImapConnection()->subscribe($this->name);
		else
			$this->_attributes['subscribed'] = !$this->getAccount()->openImapConnection()->unsubscribe($this->name);
	}

	public function subscribe(){
		$this->subscribed=true;
		return $this->subscribed;
	}

	public function unsubscribe(){
		$this->subscribed=false;
		return !$this->subscribed;
	}

	public function __toString() {
		return $this->_attributes['name'];
	}

	private function _getCacheKey(){
		$user_id = \GO::user() ? \GO::user()->id : 0;
		return $user_id.':'.$this->_account->id.':'.$this->name;
	}


	public function getUnseen(){
		if(!isset($this->_attributes['unseen'])){
			if(!$this->noselect){
				$unseen=$this->getAccount()->openImapConnection($this->name)->get_unseen();
				$this->_attributes['unseen']=$unseen['count'];
			}  else {
				$this->_attributes['unseen']=0;
			}
		}
		return $this->_attributes['unseen'];
	}
	/**
	 * Check if this mailbox exists
	 * @return boolean
	 */
	public function exists(){
		$imap = $this->getAccount()->justConnect();

		$exists = $imap->select_mailbox($this->name);

		if(!$exists)
			$imap->last_error(); //clear the not exist error

		return $exists;
	}

	public function hasAlarm(){
		//caching is required. We don't use the session because we need to close
		//session writing when checking email accounts. Otherwise it can block the
		//session to long.
		if(\GO::cache() instanceof \GO\Base\Cache\None)
			return false;

		$cached = \GO::cache()->get($this->_getCacheKey());
		return ($cached != $this->unseen && $this->unseen>0);
	}

	/**
	 * Set's the cache to the number of unseen messages
	 */
	public function snoozeAlarm(){
		GO::cache()->set($this->_getCacheKey(), $this->unseen);
	}

	/**
	 * Returns true if this is the sent, trash or drafts folder.
	 *
	 * @return boolean
	 */
	public function isSpecial(){
		return (
						$this->name==$this->account->sent ||
						$this->name==$this->account->trash ||
						$this->name==$this->account->drafts
						);
	}

	/**
	 * Return true if this mailbox should be displayed in the main tree.
	 * Only subscribed folders should be visible. But some folders can't be subscribed like shared namespaces.
	 * If they have children then they must be displayd too.
	 * @return boolean
	 */
	public function isVisible(){
			return $this->subscribed ||  $this->getHasChildren(true);
	}
}
