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

	public function __construct(Account $account, $attributes)
	{
		$this->_account = $account;

		$this->_attributes = $attributes;
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

		if(!$asSubscribedMailbox) {
			if (isset($this->_attributes['haschildren']) && $this->_attributes['haschildren']) {
				return true;
			}
			if (isset($this->_attributes['hasnochildren']) && $this->_attributes['hasnochildren']) {
				return false;
			}

			if (isset($this->_attributes['noinferiors']) && $this->_attributes['noinferiors']) {
				return false;
			}
		}

		//oh oh, bad mailserver can't tell us if it has children. Let's find out the expensive way
		$folders = $this->getAccount()->openImapConnection()->list_folders($asSubscribedMailbox, false,"",$this->name.$this->delimiter.'%');
		//store values for caching
		$this->_attributes['haschildren']= count($folders)>0;
		$this->_attributes['hasnochildren']= count($folders)==0;
		return $this->_attributes['haschildren'];

	}

	public function getSubscribed() :bool
	{
		//todo make compatible with servers that can't return subscribed flag
		return !empty($this->_attributes['subscribed']);

	}

	public function areFlagsPermitted() :bool
	{
		return !empty(GO::config()->email_enable_labels);
	}

	public function getDelimiter(){
		if(!isset($this->_attributes["delimiter"])) {
			$this->_attributes["delimiter"] = $this->getAccount()->openImapConnection()->get_mailbox_delimiter();
		}
		return $this->_attributes["delimiter"];
	}

	public function getParentName() :string
	{
		$pos = strrpos($this->name, $this->delimiter);

		if ($pos === false) {
			return false;
		}

		return substr($this->name, 0, $pos);
	}


	public function getBaseName() :string
	{
		$name = $this->name;
		$pos = strrpos($name, $this->delimiter);

		if ($pos !== false) {
			$name = substr($this->name, $pos + 1);
		}

		return $name;
	}

	public function getDisplayName() :string
	{
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

	public function addChild(ImapMailbox $mailbox)
	{
		if(!isset($this->_children)){
			$this->_children = array();
		}
		$this->_children[] = $mailbox;
	}

	public function isRootMailbox() :bool
	{
		return $this->name.$this->delimiter==$this->getAccount()->mbroot;
	}

	/**
	 * Get all child nodes of mailbox
	 * @param boolean|null $subscribed Only get subscribed folders
	 * @param boolean|null $withStatus Get the status of the folder (Unseen and message count)
	 * @return ImapMailbox[] the mailbox folders
	 */
	public function getChildren(?bool $subscribed = false, ?bool $withStatus = true)
	{
		if(!isset($this->_children)){

			$imap = $this->getAccount()->openImapConnection();

			$this->_children = array();

			if(!$this->isRootMailbox()) {
				$folders = $imap->list_folders($subscribed,$withStatus,"","$this->name$this->delimiter%");
				foreach ($folders as $folder) {
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
	public function getAccount()
	{
		return $this->_account;
	}


	/**
	 * Rename a mailbox
	 *
	 * @param string $name
	 * @return bool
	 * @throws GO\Base\Exception\AccessDenied
	 * @throws GO\Base\Mail\Exception\ImapAuthenticationFailedException
	 * @throws GO\Base\Mail\Exception\MailboxNotFound
	 */
	public function rename(string $name) :bool
	{

	  if($this->getAccount()->getPermissionLevel() <= \GO\Base\Model\Acl::READ_PERMISSION) {
		  throw new \GO\Base\Exception\AccessDenied();
	  }

		$name = trim($name);
		$this->_validateName($name);

		$parentName = $this->getParentName();
		$newMailbox = empty($parentName) ? $name : $parentName.$this->delimiter.$name;

		if($this->getAccount()->openImapConnection()->rename_folder($this->name, $newMailbox)) {
			$this->_attributes['name'] = $newMailbox;
			return true;
		}
		return false;
	}


	/**
	 * @return bool
	 * @throws GO\Base\Exception\AccessDenied
	 * @throws GO\Base\Mail\Exception\ImapAuthenticationFailedException
	 * @throws GO\Base\Mail\Exception\MailboxNotFound
	 */
	public function delete() :bool
	{
	  if($this->getAccount()->getPermissionLevel() <= \GO\Base\Model\Acl::READ_PERMISSION) {
		  throw new \GO\Base\Exception\AccessDenied();
	  }
		
		if($this->getHasChildren()) {
			foreach ($this->getChildren() as $mailBox) {
				if(!$mailBox->delete()) {
					return false;
				}
			}
		}
		
	  return $this->getAccount()->openImapConnection()->delete_folder($this->name);
	}

	/**
	 * @return bool
	 * @throws GO\Base\Exception\AccessDenied
	 * @throws GO\Base\Mail\Exception\ImapAuthenticationFailedException
	 * @throws GO\Base\Mail\Exception\MailboxNotFound
	 */
	public function truncate() :bool
	{
		if ($this->getAccount()->getPermissionLevel() <= \GO\Base\Model\Acl::READ_PERMISSION) {
			throw new \GO\Base\Exception\AccessDenied();
		}
		$imap = $this->getAccount()->openImapConnection($this->name);
		$success = true;

		if($this->_account->trash && $this->_account->trash == $this->name) {
			foreach ($imap->get_folders($this->_account->trash) as $folder) {
				if ($folder['name'] == $this->_account->trash || empty($folder['name'])) {
					continue;
				}
				$success = $success && $imap->delete_folder($folder['name']);
			}
		}
		$sort = $imap->sort_mailbox();
		return $imap->delete($sort);
	}

	/**
	 * @param string $name
	 * @return bool
	 * @throws \Exception
	 */
	private function _validateName(string $name) :bool
	{
		$illegalChars = '/';

		if($this->getDelimiter()!='/'){
			$illegalChars .=$this->getDelimiter();
		}

		if(preg_match('/['.preg_quote($illegalChars,'/').']/', $name)){
			throw new \Exception(sprintf(\GO::t("The name contained one of the following illegal characters %s"),': '.$illegalChars));
		}
		return true;
	}

	/**
	 * @param string $name
	 * @param bool|null $subscribe
	 * @return bool
	 * @throws GO\Base\Exception\AccessDenied
	 * @throws GO\Base\Mail\Exception\ImapAuthenticationFailedException
	 * @throws GO\Base\Mail\Exception\MailboxNotFound
	 */
	public function createChild(string $name, ?bool $subscribe=true) :bool
	{
		if($this->getAccount()->getPermissionLevel() <= \GO\Base\Model\Acl::READ_PERMISSION) {
		  throw new \GO\Base\Exception\AccessDenied();
		}
		$name = trim($name);
		$newMailbox = empty($this->name) ? $name : $this->name.$this->delimiter.$name;

		$this->_validateName($name);

		return $this->getAccount()->openImapConnection()->create_folder($newMailbox, $subscribe);
	}

	/**
	 * @param ImapMailbox $targetMailbox
	 * @return bool
	 * @throws GO\Base\Exception\AccessDenied
	 * @throws GO\Base\Mail\Exception\ImapAuthenticationFailedException
	 * @throws GO\Base\Mail\Exception\MailboxNotFound
	 */
	public function move(ImapMailbox $targetMailbox) :bool
	{
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

	/**
	 * @param bool $value
	 * @throws GO\Base\Mail\Exception\ImapAuthenticationFailedException
	 * @throws GO\Base\Mail\Exception\MailboxNotFound
	 */
	public function setSubscribed(bool $value)
	{
		if($value) {
			$this->_attributes['subscribed'] = $this->getAccount()->openImapConnection()->subscribe($this->name);
		} else {
			$this->_attributes['subscribed'] = !$this->getAccount()->openImapConnection()->unsubscribe($this->name);
		}
	}

	/**
	 * @return bool
	 */
	public function subscribe() :bool
	{
		$this->subscribed=true;
		return $this->subscribed;
	}

	/**
	 * @return bool
	 */
	public function unsubscribe() :bool
	{
		$this->subscribed=false;
		return !$this->subscribed;
	}

	/**
	 * @return mixed
	 */
	public function __toString()
	{
		return $this->_attributes['name'];
	}

	private function _getCacheKey(){
		$user_id = \GO::user() ? \GO::user()->id : 0;
		return $user_id.':'.$this->_account->id.':'.$this->name;
	}


	public function getUnseen(){
		if(!isset($this->_attributes['unseen'])){
			try {
			if(!$this->noselect){
				$unseen=$this->getAccount()->openImapConnection($this->name)->get_unseen();
				$this->_attributes['unseen']=$unseen['count'];
			}  else {
				$this->_attributes['unseen']=0;
			}}
			catch(\Exception $e) {
				$this->_attributes['unseen'] = 0;
				\GO::debug($e);	
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
