<?php

//// https://github.com/fmbiete/Z-Push-contrib/issues/135
//// This line hsould be in php.ini to completly avoid the $HTTP_RAW_POST_DATA depricated message
//ini_set('always_populate_raw_post_data', -1);
//


/**
 * The base backend of the Group-Office Z-PUSH 2 implementation.
 * This class loads all needed backends
 */
class BackendGO extends Backend implements IBackend, ISearchProvider {

	/**
	 * This is the array with all the used backends
	 * 
	 * @var array 
	 */
	public $backends;

	/**
	 * The configuration for this base backend file
	 * 
	 * @var array 
	 */
	public $config;

	/**
	 * The backend that is currently active in this scope
	 * 
	 * @var BackendDiff 
	 */
	private $_activeBackend;

	/**
	 * The id of the backend that is currently active in this scope
	 * @var BackendDiff 
	 */
	private $_activeBackendID;

	/**
	 * Indicates which AS version is supported by the backend.
	 *
	 * @access public
	 * @return string       AS version constant
	 */
	public function GetSupportedASVersion() {
		return ZPush::ASV_14;
	}

	/**
	 * The constuctor of this class.
	 * This loads the diffBackends that are needed by this base backends
	 */
	public function __construct() {

		parent::__construct();
		$this->config = BackendGoConfig::GetBackendGoConfig();

		foreach ($this->config['backends'] as $i => $b) {
			// load and instatiate backend
//			$this->_includeBackend($b['name']);
			$this->backends[$i] = new $b['name']($b['config']);
		}
		ZLog::Write(LOGLEVEL_INFO, sprintf("Combined %d backends loaded.", count($this->backends)));
	}

//	/**
//	 * Loads a backend class file identified by filename
//	 * 
//	 * @param string $backendname
//	 * @return boolean
//	 */
//	private function _includeBackend($backendname) {
//
//		$backend = REAL_BASE_PATH . "backend/go/" . $backendname . ".php";
//
//		if (is_file($backend))
//			$toLoad = $backend;
//		else
//			return false;
//
//		ZLog::Write(LOGLEVEL_DEBUG, sprintf("Including partial backend file: '%s'", $toLoad));
//		include_once($toLoad);
//		return true;
//	}

	/**
	 * Login to Group-Office with this base backend. 
	 * All other backends are automatically logged in when they are connecting 
	 * through this base backend.
	 * 
	 * @param string $username
	 * @param string $domain
	 * @param string $password
	 * @return boolean
	 */
	public function Logon($username, $domain, $password) {
		
		// if it is e-mail address try to use the name to login
		if(strpos($username, '@') && !(defined('USE_FULLEMAIL_FOR_LOGIN') && USE_FULLEMAIL_FOR_LOGIN)) {
				$username = Utils::GetLocalPartFromEmail($username);
		}
		
		
		ZLog::Write(LOGLEVEL_INFO, 'ZPUSH2::Logon(GO version: ' . \GO::config()->version . ', backend version: ' . BackendGoConfig::GOBACKENDVERSION . ', user: ' . $username . ', domain: ' . $domain . ')');
		
		
		try {

			if(!$user = \go\core\auth\Authenticate::passwordLogin($username, $password)) {
				return false;
			}
			
			if(!GO::modules()->sync) {
				ZLog::Write(LOGLEVEL_INFO, 'User '. $user->username .' logged on but has no access to the sync module');
				return false;
			}

			if (!$user) {
				ZLog::Write(LOGLEVEL_INFO, 'ZPUSH2::ERROR::Username and/or password incorrect.');
				//throw new \Exception('<b>Username and/or password unknown.</b>');
				return false;
			} else {
				
				if (\GO::modules()->isInstalled("zpushadmin")) {
					ZLog::Write(LOGLEVEL_INFO, 'The zpushadmin module is installed, checking access for device.');

					$devId = Request::GetDeviceID();
					if (empty($devId)) {
						ZLog::Write(LOGLEVEL_INFO, 'Cannot identify the device ID, problably you are using the webbrowser to access z-push.');
					} else {
						$deviceRequest = new \GO\Zpushadmin\Model\Devicerequest();
						$deviceRequest->setNotNew();
						if (!$deviceRequest->hasAccess()) {
							ZLog::Write(LOGLEVEL_ERROR, 'This device is not enabled for sync. Your deviceId is: ' . Request::GetDeviceID());
							Throw new \GO\Base\Exception\AccessDenied('This device is not enabled for sync. Your deviceId is: ' . Request::GetDeviceID());
							return false;
						}
					}
				} else {
					ZLog::Write(LOGLEVEL_INFO, 'The zpushadmin module is NOT installed.');
				}
			}
		} catch (\Exception $e) {
			ZLog::Write(LOGLEVEL_ERROR, 'ZPUSH2::ERROR::Could not authenticate to Group-Office');
			ZLog::Write(LOGLEVEL_INFO, $e->getMessage());
			return false;
		}

		$this->_username = $username;

		if (!\GO::modules()->sync) {
			ZLog::Write(LOGLEVEL_ERROR, 'ZPUSH2::ERROR::User doesn\'t have permission for the sync module or it\'s not installed');
			return false;
		}
		return true;
	}

	/**
	 * Returns the email address and the display name of the user. Used by autodiscover.
	 *
	 * @param string        $username           The username
	 *
	 * @access public
	 * @return Array
	 */
	public function GetUserDetails($username) {
		$user = go()->getAuthState()->getUser(['id', 'username', 'displayName', 'email']);
		return array('emailaddress' => $user->email, 'fullname' => $user->displayName);
	}
	
		/**
	 * for old framework to work in GO::session()
	 * 
	 * @param \GO\Dav\Auth\User $user
	 */
	private function oldLogin(\go\core\model\User $user) {
		if(!defined('GO_NO_SESSION')) {
			define("GO_NO_SESSION", true);
		}
		$_SESSION['GO_SESSION'] = ['user_id' => $user->id];
	}

	/**
	 * Logout from Group-Office
	 */
	public function Logoff() {
		foreach ($this->backends as $i => $b) {
			$b->Logoff();
		}
	}

	/**
	 * Setup the backend to work on a specific store or checks ACLs there.
	 * If only the $store is submitted, all Import/Export/Fetch/Etc operations should be
	 * performed on this store (switch operations store).
	 * If the ACL check is enabled, this operation should just indicate the ACL status on
	 * the submitted store, without changing the store for operations.
	 * For the ACL status, the currently logged on user MUST have access rights on
	 *  - the entire store - admin access if no folderid is sent, or
	 *  - on a specific folderid in the store (secretary/full access rights)
	 *
	 * The ACLcheck MUST fail if a folder of the authenticated user is checked!
	 *
	 * @param string        $store              target store, could contain a "domain\user" value
	 * @param boolean       $checkACLonly       if set to true, Setup() should just check ACLs
	 * @param string        $folderid           if set, only ACLs on this folderid are relevant
	 * @return boolean
	 */
	public function Setup($store, $checkACLonly = false, $folderid = false, $readonly = false) {
		ZLog::Write(LOGLEVEL_INFO, "BackendGO->Setup() ~~ START");
	//	ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendGO->Setup('%s', '%s', '%s')", $store, Utils::PrintAsString($checkACLonly), $folderid));
		if (!is_array($this->backends)) {
			return false;
		}
		foreach ($this->backends as $i => $b) {
			$u = $store;
			if (isset($this->config['backends'][$i]['users']) && isset($this->config['backends'][$i]['users'][$store]['username'])) {
				$u = $this->config['backends'][$i]['users'][$store]['username'];
			}
			if ($this->backends[$i]->Setup($u, $checkACLonly, $folderid) == false) {
				ZLog::Write(LOGLEVEL_FATAL, "BackendGO->Setup() failed");
				return false;
			}
		}
//		ZLog::Write(LOGLEVEL_INFO, "BackendGO->Setup() ~~ SUCCESS");
		return true;
	}

	/**
	 * Returns an understandable folderid for the backend
	 * For example it looks like: "c/GroupOfficeContacts"
	 *
	 * @param string        $folderid       combinedid of the folder
	 * @return string
	 */
	public function GetBackendFolder($folderid) {
	//	ZLog::Write(LOGLEVEL_DEBUG, "BackendGO->GetBackendFolder('.$folderid.')");
		$pos = strpos($folderid, $this->config['delimiter']);
		if ($pos === false)
			return false;
//		ZLog::Write(LOGLEVEL_INFO, "BackendGO->GetBackendFolder('.$folderid.') ~~ SUCCESS");
		return substr($folderid, $pos + strlen($this->config['delimiter']));
	}

	/**
	 * Returns backend id for a folder
	 *
	 * @param string $folderid	combined id of the folder
	 * @return object
	 */
	public function GetBackendId($folderid) {
	//	ZLog::Write(LOGLEVEL_DEBUG, "BackendGO->GetBackendId('.$folderid.')");
		$pos = strpos($folderid, $this->config['delimiter']);
		if ($pos === false)
			return false;
//		ZLog::Write(LOGLEVEL_INFO, 'BackendGO->GetBackendId('.$folderid.') ~~ SUCCESS');
		return substr($folderid, 0, $pos);
	}

	/**
	 * Finds the correct backend for a folder
	 *
	 * @param string $folderid	combined id of the folder
	 * @return object
	 */
	public function GetBackend($folderid) {
		//ZLog::Write(LOGLEVEL_DEBUG, "Combined->GetBackend($folderid)");
		$pos = strpos($folderid, $this->config['delimiter']);
		if ($pos !== false){			
			$id = substr($folderid, 0, $pos);
		}else
		{
			$id=$folderid;
		}
		if (!isset($this->backends[$id]))
			return false;

		$this->_activeBackend = $this->backends[$id];
		$this->_activeBackendID = $id;
//		ZLog::Write(LOGLEVEL_DEBUG, 'BackendGO->GetBackend('.$folderid.') ~~ SUCCESS');
		return $this->backends[$id];
	}

	/**
	 * Processes a response to a meeting request.
	 *
	 * @param string        $requestid      id of the object containing the request
	 * @param string        $folderid       id of the parent folder of $requestid
	 * @param string        $response
	 * @return string       id of the created/updated calendar obj
	 * @throws StatusException
	 */
	public function MeetingResponse($requestid, $folderid, $error) {
		ZLog::Write(LOGLEVEL_DEBUG, "Combined->MeetingResponse($requestid , $folderid , $error)");
		$backend = $this->GetBackend($folderid);
		if ($backend === false)
			return false;
		ZLog::Write(LOGLEVEL_DEBUG, "Combined->MeetingResponse($requestid , $folderid , $error) success");
		return $backend->MeetingResponse($requestid, $this->GetBackendFolder($folderid), $error);
	}


	/**
	 * Deletes all contents of the specified folder.
	 * This is generally used to empty the trash (wastebasked), but could also be used on any
	 * other folder.
	 *
	 * @param string        $folderid
	 * @param boolean       $includeSubfolders      (opt) also delete sub folders, default true
	 *
	 * @access public
	 * @return boolean
	 * @throws StatusException
	 */
	public function EmptyFolder($folderid, $includeSubfolders = true) {
		$backend = $this->GetBackend($folderid);
		if($backend === false)
			return false;
		return $backend->EmptyFolder($this->GetBackendFolder($folderid), $includeSubfolders);
	}

	/**
	 * Returns the content of the named attachment as stream.
	 * There is no way to tell which backend the attachment is from, so we try them all
	 *
	 * @param string        $attname
	 * @return SyncItemOperationsAttachment
	 * @throws StatusException
	 */
	public function GetAttachmentData($attname) {
		ZLog::Write(LOGLEVEL_DEBUG, sprintf("Combined->GetAttachmentData('%s')", $attname));
		
		$attachment = $this->backends[BackendGoConfig::MAILBACKENDID]->GetAttachmentData($attname);
		if ($attachment instanceof SyncItemOperationsAttachment)
			return $attachment;
		else
			throw new StatusException("Combined->GetAttachmentData(): no backend found", SYNC_ITEMOPERATIONSSTATUS_INVALIDATT);
	}

	/**
	 * Returns the exporter to send changes to the mobile
	 * the exporter from right backend for contents exporter and our own exporter for hierarchy exporter
	 *
	 * @param string        $folderid (opt)
	 * @return object(ExportChanges)
	 */
	public function GetExporter($folderid = false) {
		ZLog::Write(LOGLEVEL_DEBUG, "Combined->GetExporter($folderid)");
		if ($folderid) {
			$backend = $this->GetBackend($folderid);
			if ($backend == false)
				return false;
			return $backend->GetExporter($this->GetBackendFolder($folderid));
		}
		return new GoExporter($this);
	}

	/**
	 * Sends an e-mail
	 * This messages needs to be saved into the 'sent items' folder
	 *
	 * @param SyncSendMail  $sm     SyncSendMail object
	 * @return boolean
	 * @throws StatusException
	 */
	public function SendMail($sm) {
		if ($this->backends[BackendGoConfig::MAILBACKENDID]->SendMail($sm) == true)
			return true;
		return false;
	}

	/**
	 * Returns all available data of a single message
	 *
	 * @param string            $folderid
	 * @param string            $id
	 * @param ContentParameters $contentparameters flag
	 * @return object(SyncObject)
	 * @throws StatusException
	 */
	public function Fetch($folderid, $id, $contentparameters) {
		ZLog::Write(LOGLEVEL_DEBUG, sprintf("Combined->Fetch('%s', '%s', CPO)", $folderid, $id));
		$backend = $this->GetBackend($folderid);
		if ($backend == false)
			return false;
		return $backend->Fetch($this->GetBackendFolder($folderid), $id, $contentparameters);
	}

	/**
	 * Returns the waste basket
	 * If the wastebasket is set to one backend, return the wastebasket of that backend
	 * else return the first waste basket we can find
	 * @return string
	 */
	function GetWasteBasket() {
		ZLog::Write(LOGLEVEL_DEBUG, "Combined->GetWasteBasket()");

		if (isset($this->_activeBackend)) {
			if (!$this->_activeBackend->GetWasteBasket())
				return false;
			else
				return $this->_activeBackendID . $this->config['delimiter'] . $this->_activeBackend->GetWasteBasket();
		}

		return false;
	}

	/**
	 * Returns an array of SyncFolder types with the entire folder hierarchy
	 * from all backends combined
	 *
	 * provides AS 1.0 compatibility
	 * 
	 * @return array SYNC_FOLDER
	 */
	public function GetHierarchy() {
		ZLog::Write(LOGLEVEL_DEBUG, "Combined->GetHierarchy()");
		$ha = array();
		foreach ($this->backends as $i => $b) {
			if (!empty($this->config['backends'][$i]['subfolder'])) {
				$f = new SyncFolder();
				$f->serverid = $i . $this->config['delimiter'] . '0';
				$f->parentid = '0';
				$f->displayname = $this->config['backends'][$i]['subfolder'];
				$f->type = SYNC_FOLDER_TYPE_OTHER;
				$ha[] = $f;
			}
			$h = $this->backends[$i]->GetHierarchy();
			if (is_array($h)) {
				foreach ($h as $j => $f) {
					$h[$j]->serverid = $i . $this->config['delimiter'] . $h[$j]->serverid;
					if ($h[$j]->parentid != '0' || !empty($this->config['backends'][$i]['subfolder'])) {
						$h[$j]->parentid = $i . $this->config['delimiter'] . $h[$j]->parentid;
					}
					if (isset($this->config['folderbackend'][$h[$j]->type]) && $this->config['folderbackend'][$h[$j]->type] != $i) {
						$h[$j]->type = SYNC_FOLDER_TYPE_OTHER;
					}
				}
				$ha = array_merge($ha, $h);
			}
		}
		
		//ZLog::Write(LOGLEVEL_DEBUG, var_export($ha, true));

		ZLog::Write(LOGLEVEL_DEBUG, "Combined->GetHierarchy() success");
		return $ha;
	}

	/**
	 * Returns the importer to process changes from the mobile
	 *
	 * @param string $folderid (opt)
	 * @return object(ImportChanges)
	 */
	public function GetImporter($folderid = false) {
		
		ZLog::Write(LOGLEVEL_DEBUG, sprintf("Combined->GetImporter() Content: ImportChangesCombined:('%s')", $folderid));
		
		if ($folderid !== false) {		

			// get the contents importer from the folder in a backend
			// the importer is wrapped to check foldernames in the ImportMessageMove function
			$backend = $this->GetBackend($folderid);
			if ($backend === false)
				return false;
			$importer = $backend->GetImporter($this->GetBackendFolder($folderid));
			if ($importer) {
				return new GoImporter($this, $folderid, $importer);
			}
			return false;
		} else {
			ZLog::Write(LOGLEVEL_DEBUG, "Combined->GetImporter() -> Hierarchy: ImportChangesCombined()");
			//return our own hierarchy importer which send each change to the right backend
//			throw new StatusException();
			return new GoImporter($this);
		}
	}

	// Functions for search

	/**
	 * Disconnect function to close things after the search is completed.
	 * 
	 * Note: Not needed for us now.
	 * 
	 * @return boolean Success
	 */
	public function Disconnect() {
		ZLog::Write(LOGLEVEL_INFO, 'ZPUSH2Search::Disconnect');
		return true;
	}


	public function GetMailboxSearchResults($cpo) {
		$searchFolderId = $cpo->GetSearchFolderid();
		
		if(empty($searchFolderId)){
			
			ZLog::Write(LOGLEVEL_WARN, 'Client sent empty search folder! Defaulting to INBOX');
			
			$searchFolderId = 'm/INBOX';
		}
		
		$mailBackend = $this->GetBackend($searchFolderId);
		if(!$mailBackend){
			
			ZLog::Write(LOGLEVEL_ERROR, 'Search folder: '.$searchFolderId.' not found');
			
			return array();
		}
		
		return $mailBackend->GetMailboxSearchResults($cpo);
	}

	/**
	 * Get the supported search options for the GO backend
	 * 
	 * @param string $searchtype GAL / MAILBOX
	 * @return boolean
	 */
	public function SupportsType($searchtype) {
		//return in_array($searchtype,array(ISearchProvider::SEARCH_GAL, ISearchProvider::SEARCH_MAILBOX));
		return in_array(strtoupper($searchtype), array(ISearchProvider::SEARCH_MAILBOX));
	}
	
	/**
	 * Returns the backend as it implements the ISearchProvider interface
	 * This could be overwritten by the global configuration
	 *
	 * @access public
	 * @return object       Implementation of ISearchProvider
	 */
	public function GetSearchProvider() {
		return $this;
	}

	/**
	 * Terminate the search while it is searching.
	 * 
	 * Note: Not needed for us now.
	 * 
	 * @param int $pid
	 * @return boolean Success
	 */
	public function TerminateSearch($pid) {
		ZLog::Write(LOGLEVEL_INFO, 'ZPUSH2Search::TerminateSearch');
		return true;
	}

//	/**
//	 * Get the Group-Office Sync settings for the current user
//	 * 
//	 * @return \GO\Sync\Model\Settings 
//	 */
//	private function _getSettings() {
//		return \GO\Sync\Model\Settings::model()->findForUser(\GO::user());
//	}
	
	protected $syncstates=array();
	protected $sinkfolders=array();
	
	/**
	 * Indicates if the backend has a ChangesSink.
	 * A sink is an active notification mechanism which does not need polling.
	 * The IMAP backend simulates a sink by polling status information of the folder
	 *
	 * @access public
	 * @return boolean
	 */
	public function HasChangesSink() {
		ZLog::Write(LOGLEVEL_DEBUG, "HasChangesSink()");
		return true;
	}

	/**
	 * The folder should be considered by the sink.
	 * Folders which were not initialized should not result in a notification
	 * of IBacken->ChangesSink().
	 *
	 * @param string        $folderid
	 *
	 * @access public
	 * @return boolean      false if found can not be found
	 */
	public function ChangesSinkInitialize($folderid) {
		ZLog::Write(LOGLEVEL_DEBUG, sprintf("GO->ChangesSinkInitialize(): folderid '%s'", $folderid));

		$this->sinkfolders[]=$folderid;

		return true;
	}

	/**
	 * The actual ChangesSink.
	 * For max. the $timeout value this method should block and if no changes
	 * are available return an empty array.
	 * If changes are available a list of folderids is expected.
	 *
	 * @param int           $timeout        max. amount of seconds to block
	 *
	 * @access public
	 * @return array
	 */
	public function ChangesSink($timeout = 30) {

		ZLog::Write(LOGLEVEL_DEBUG, "ChangesSink($timeout)");

		$notifications = array();
//		$stopat = time() + $timeout - 1;


//		while ($stopat > time() && empty($notifications)) {
			
			foreach ($this->sinkfolders as $folder) {
				
				$f = $this->GetBackendFolder($folder);
				$b = $this->GetBackend($folder);
				
				$newstate = $b->getNotification($f);
				
				ZLog::Write(LOGLEVEL_DEBUG, $folder.': '.$newstate);
				
				if (!isset($this->sinkstates[$folder])) {					
					$this->sinkstates[$folder] = $newstate;
				}		
				
				if ($this->sinkstates[$folder] != $newstate) {
					$notifications[] = $folder;
					$this->sinkstates[$folder] = $newstate;
				}				
			}
			
			ZLog::Write(LOGLEVEL_DEBUG, "All sink folders checked");
			
			ZLog::Write(LOGLEVEL_DEBUG, "Closing DB connection");
			\GO::unsetDbConnection();
			
			if (empty($notifications)){
				
				ZLog::Write(LOGLEVEL_DEBUG, "Sleeping ".$timeout." seconds");
				sleep($timeout);
			}
		return $notifications;
	}

	/**
     * Searches the GAL.
     *
     * @param string                        $searchquery        string to be searched for
     * @param string                        $searchrange        specified searchrange
     * @param SyncResolveRecipientsPicture  $searchpicture      limitations for picture
     *
     * @access public
     * @return array        search results
     * @throws StatusException
     */
		public function GetGALSearchResults($searchquery, $searchrange, $searchpicture) {
			return [];
		}
}
