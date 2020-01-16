<?php

class GoBaseBackendDiff extends \BackendDiff {
	
	protected $_server_delimiter;
	private static $_account=false;
	
	/**
	 * Get the imap account of the current user
	 * 
	 * @return \GO\Email\Model\Account
	 */
	public function getImapAccount(){
		if(!\GO::modules()->email){
			ZLog::Write(LOGLEVEL_INFO, 'GoBaseBackendDiff->getImapAccount() ~~ EMAIL MODULE IS NOT INSTALLED!');
			return false;
		}
			
		if(!self::$_account){
			$settings  = GoSyncUtils::getUserSettings();
			if(!empty($settings->account_id)) {
				try{
					self::$_account = \GO\Email\Model\Account::model()->findByPk($settings->account_id);
					if(!self::$_account){
						ZLog::Write(LOGLEVEL_FATAL, 'E-mail account not found!');
					}
				}catch(\GO\Base\Exception\AccessDenied $e){
					ZLog::Write(LOGLEVEL_FATAL, 'GoBaseBackendDiff->getImapAccount() ~~ ACCESS DENIED to e-mail account configured in sync settings('.(string)$e->getMessage().')');
				}
			}else
			{
				ZLog::Write(LOGLEVEL_FATAL, 'No e-mail account. Settings: '.var_export($settings->getAttributes(), true));
			}
		}
		return self::$_account;
	}
	
	/**
	 * Get the folder ID of the imap trash folder
	 * 
	 * @return StringHelper
	 */
	public function getImapTrashFolderId(){
		$imapAccount = $this->getImapAccount();
		return $imapAccount ? $imapAccount->trash : false;
	}
	
	/**
	 * Get the folder ID of the imap sent items folder
	 * 
	 * @return StringHelper
	 */
	public function getImapSentFolderId(){
		$imapAccount = $this->getImapAccount();
		return $imapAccount ? $imapAccount->sent : false;
	}
		
	/**
	 * Connect to the imap server
	 * 
	 * @param type $mailbox
	 * @return \GO\Base\Mail\Imap 
	 */
	protected function _imapLogon($mailbox = 'INBOX') {

		if ($mailbox != 'INBOX')
			$mailbox = $this->_replaceDotWithServerDelimiter($mailbox);

		$imapAccount = $this->getImapAccount();
		if(!$imapAccount)
			return false;
		
		$conn = false;

		try {
			$conn = $imapAccount->openImapConnection($mailbox);

			if(!$conn)
				ZLog::Write(LOGLEVEL_FATAL, 'GoBaseBackendDiff->_imapLogon('.$mailbox.') ~~ IMAP LOGIN FAILED (account: '.$imapAccount->username.')');
			else
				ZLog::Write(LOGLEVEL_INFO, 'GoBaseBackendDiff->_imapLogon('.$mailbox.') ~~ IMAP LOGIN SUCCESS (account: '.$imapAccount->username.')');
			
		} catch (\Exception $e) {
			ZLog::Write(LOGLEVEL_FATAL, 'GoBaseBackendDiff->_imapLogon('.$mailbox.') ~~ OPEN IMAP CONNECTION FAILED ('.(string)$e->getMessage().')');
		}

		return $conn;
	}
	
	/**
	 * Modify the folderid string so it will use the correct server delimiter
	 * 
	 * @param StringHelper $folderid
	 * @return StringHelper
	 */
	protected function _replaceDotWithServerDelimiter($folderid) {
		return str_replace('.', $this->_getServerDelimiter(), $folderid);
	}
	
	/**
	 * Get the server delimiter of the imap server
	 * 
	 * @return StringHelper
	 */
	protected function _getServerDelimiter() {
		if (!$this->_server_delimiter) {
			$imap = $this->_imapLogon();			
			if(!$imap)
				throw new StatusException("Could not logon to IMAP server");
			
			$this->_server_delimiter = $imap->get_mailbox_delimiter();
		}
		return $this->_server_delimiter;
	}	
			
	/**
	 * Get the stat of the folder with the given id
	 * 
	 * @param StringHelper $id
	 * @return array
	 */
	public function StatFolder($id) {
		$folder = $this->GetFolder($id);
		if(!$folder) {
			return false;
		}
		$stat = array();
		$stat['id'] = $id;
		$stat['parent'] = $folder->parentid;
		$stat['mod'] = $folder->displayname;
		ZLog::Write(LOGLEVEL_DEBUG, 'ZPUSH2::StatFolder'.$id);
		return $stat;
	}
		
	/**
	 * Get the folder
	 * 
	 * ** THIS FUNCTION NEEDS TO BE OVERRIDDEN BY BACKENDS THAT EXTEND FROM THIS BACKEND **
	 * 
	 * @param StringHelper $id
	 * @return boolean
	 */
	public function GetFolder($id) {
		return false;
	}
	
	
	
	public function GetMessage($folderid, $id, $contentparameters) {
		return false;
	}

	public function ChangeMessage($folderid, $id, $message, $contentParameters) {
		return false;
	}
	
	public function StatMessage($folderid, $id) {
		return false;
	}

	public function GetMessageList($folderid, $cutoffdate) {
		return false;
	}
	
	public function GetFolderList() {
		return false;
	}
	
	/**
	 * Login function
	 * 
	 * This function is a dummy one, 
	 * the login process is already been handled in the go.php file.
	 * 
	 * @param StringHelper $username
	 * @param StringHelper $domain
	 * @param StringHelper $password
	 * @return boolean
	 */
	public function Logon($username, $domain, $password){
		return true;
	}
	
	/**
	 * Logout function
	 * 
	 * This function is a dummy one,
	 * the logout process is already been handled in the go.php file.
	 * 
	 * @return boolean
	 */
	public function Logoff(){
		return true;
	}
	
	public function ChangeFolder($folderid, $oldid, $displayname, $type) {
		return false;
	}

	public function DeleteFolder($id, $parentid) {
		return false;
	}
	
	public function SetReadFlag($folderid, $id, $flags, $contentParameters) {
		return false;
	}

	public function DeleteMessage($folderid, $id, $contentParameters) {
		return false;
	}

	public function MoveMessage($folderid, $id, $newfolderid, $contentParameters) {
		return false;
	}

	public function SendMail($sm) {
		return false;
	}

	public function GetWasteBasket() {
		return false;
	}

	public function GetAttachmentData($attname) {
		return false;
	}
}
