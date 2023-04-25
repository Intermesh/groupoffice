<?php

abstract class Store extends \BackendDiff {
	
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
				ZLog::Write(LOGLEVEL_INFO, 'No e-mail account. Settings: '.var_export($settings->getAttributes(), true));
				return false;
			}
		}
		return self::$_account;
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
	 * @param string $folderid
	 * @return string
	 */
	protected function _replaceDotWithServerDelimiter($folderid) {
		return str_replace('.', $this->_getServerDelimiter(), $folderid);
	}
	
	/**
	 * Get the server delimiter of the imap server
	 * 
	 * @return string
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
	 * @param string $id
	 * @return array
	 */
	public function StatFolder($id) {
		$folder = $this->GetFolder($id);
		if(!$folder) {
			ZLog::Write(LOGLEVEL_DEBUG, "Folder with id $id could not be found");
			return false;
		}
		ZLog::Write(LOGLEVEL_DEBUG, 'ZPUSH2::StatFolder('.$id.')');
		return [
			'id' => $id,
			'parent' => $folder->parentid,
			'mod' => $folder->displayname
		];
	}

	/**
	 * This function is a dummy one,
	 * the login process is already been handled in the go.php file.
	 */
	public function Logon($username, $domain, $password){
		return true;
	}

	public function Logoff(){
		return true;
	}

    // These classes are broken! They won't sync more than 25 items per folder and keep syncing forever.
//	public function GetImporter($folderid = false) {
//		return new ChangeImporter($this, $folderid);
//	}
//
//	public function GetExporter($folderid = false) {
//		return new ChangeExporter($this, $folderid);
//	}

	abstract public function GetFolder($id);

	abstract public function GetMessage($folderid, $id, $contentparameters);

	abstract public function ChangeMessage($folderid, $id, $message, $contentParameters);

	abstract public function StatMessage($folderid, $id);

	abstract public function GetMessageList($folderid, $cutoffdate);

	abstract public function GetFolderList();

	abstract public function DeleteMessage($folderid, $id, $contentParameters) ;

	public function MoveMessage($folderid, $id, $newfolderid, $contentParameters) {
		return false;
	}

	public function ChangeFolder($folderid, $oldid, $displayname, $type) {
		return false;
	}

	public function DeleteFolder($id, $parentid) {
		return false;
	}

	public function SendMail($sm) {
		return false; // only implemented in MailProvider
	}

	public function SetReadFlag($folderid, $id, $flags, $contentParameters) {
		return false; // only implemented in MailProvider
	}

	public function GetWasteBasket() {
		return false; // unused, deletes are permanent
	}

	public function GetAttachmentData($attname) {
		return false; // only implemented in MailProvider
	}

    /**
     * Indicates which AS version is supported by the backend.
     *
     * @access public
     * @return string       AS version constant
     */
    public function GetSupportedASVersion() {
        return ZPush::ASV_14;
    }
}
