<?php

// https://github.com/fmbiete/Z-Push-contrib/issues/135
// This line should be in php.ini to completely avoid the $HTTP_RAW_POST_DATA deprecated message
// ini_set('always_populate_raw_post_data', -1);

use go\core\auth\Authenticate;
use go\core\auth\TemporaryState;
use go\core\model\Settings;
use go\core\orm\Entity;
use go\core\util\DateTime;

class BackendGO extends Backend implements IBackend, ISearchProvider {

	const VERSION = 407;

	const FolderProviders = [
		SYNC_FOLDER_TYPE_INBOX => 'm',
		SYNC_FOLDER_TYPE_DRAFTS => 'm',
		SYNC_FOLDER_TYPE_WASTEBASKET => 'm',
		SYNC_FOLDER_TYPE_SENTMAIL => 'm',
		SYNC_FOLDER_TYPE_OUTBOX => 'm',
		SYNC_FOLDER_TYPE_TASK => 't',
		SYNC_FOLDER_TYPE_APPOINTMENT => 'a',
		SYNC_FOLDER_TYPE_CONTACT => 'c',
		SYNC_FOLDER_TYPE_NOTE => 'n',
		SYNC_FOLDER_TYPE_USER_MAIL => 'm',
	];
	/**
	 * Map with 5 provider for mail, notes, contacts, calendar and tasks
	 * Mapped by their root directory name
	 * @var Store[]
	 */
	public $backends;

	/**
	 * Needed by the combined importer/exported
	 */
	public $config = [
		'backends' => [
			'c' => 'ContactStore',
			't' => 'TaskStore',
			'n' => 'NoteStore',
			'a' => 'CalendarStore',
			'm' => 'MailStore'
		],
		'delimiter' => '/', // also for combined importer
		'rootcreatefolderbackend' => 'm' // needed by combined importer
	];

	public function __construct() {
		parent::__construct();
		go()->getDebugger()->setRequestId("ActiveSync");
	}

	public function GetBackend($folderid) {
		ZLog::Write(LOGLEVEL_DEBUG, "BackendGO->GetBackend($folderid)");
		$root = strtok($folderid, '/');
		if (isset($this->backends[$root])){
			return $this->backends[$root];
		}
		ZLog::Write(LOGLEVEL_ERROR,"Backend ".$root. ' does not exist');
	}

	public function Setup($store, $checkACLonly = false, $folderid = false, $readonly = false) {
		//$this->store = $store;

		// we don't know if and how diff backends implement the "admin" check, but this will disable it for the webservice
		// backends which want to implement this, need to overwrite this method explicitely. For more info see https://jira.zarafa.com/browse/ZP-462
		if ($store == "SYSTEM" && $checkACLonly == true)
			return false;

		return true;
	}

	/**
	 * Login to Group-Office.
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
		
		
		ZLog::Write(LOGLEVEL_INFO, 'ZPUSH2::Logon(GO version: ' . \GO::config()->version . ', backend version: ' . self::VERSION . ', user: ' . $username . ', domain: ' . $domain . ')');

		try {

			$auth = new Authenticate();
			$user = $auth->passwordLogin($username, $password);
			if(!$user) {
				return false;
			}

			$state = new TemporaryState($user->id);
			go()->setAuthState($state);

			$devId = \Request::GetDeviceID();
			
			if(!GO::modules()->sync) {
				ZLog::Write(LOGLEVEL_INFO, 'User '. $user->username .' logged on but has no access to the sync module or it\'s not installed');
				return false;
			}

			if (!$user) {
				ZLog::Write(LOGLEVEL_INFO, 'Username and/or password incorrect.');
				return false;
			} else {

				if (\GO::modules()->isInstalled("zpushadmin")) {
					ZLog::Write(LOGLEVEL_INFO, 'The zpushadmin module is installed, checking access for device.');

					if (empty($devId)) {
						ZLog::Write(LOGLEVEL_INFO, 'Cannot identify the device ID, problably you are using the webbrowser to access z-push.');
					} else {
						if (!\GO\Zpushadmin\Model\Device::requestAccess()) {
							ZLog::Write(LOGLEVEL_ERROR, 'This device is not enabled for sync. Your deviceId is: ' . Request::GetDeviceID());
							//Throw new \GO\Base\Exception\AccessDenied('This device is not enabled for sync. Your deviceId is: ' . Request::GetDeviceID());
							return false;
						}
					}
				}
			}
		} catch (\Exception $e) {
			ZLog::Write(LOGLEVEL_ERROR, 'Could not authenticate to Group-Office');
			ZLog::Write(LOGLEVEL_INFO, $e->getMessage());
			return false;
		}

		if(empty($devId) || !$this->getClient($devId, $user)->isAllowed()) {
			ZLog::Write(LOGLEVEL_INFO, 'Device not fully authenticated, using stub');
			$this->config['backends'] = [
				'm' => 'StubStore'
			];
		}

		foreach ($this->config['backends'] as $i => $b) {
			$this->backends[$i] = new $b();
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

	private function getClient($deviceId, \go\core\model\User $user) {
		$client = $user->clientByDevice($deviceId);
		if($client->isNew()) {
			$client->deviceId = \Request::GetDeviceID();
			$client->platform = \Request::GetDeviceType();
			$client->ip = \Request::GetRemoteAddr();
			$client->name = 'ActiveSync ' . \Request::GetProtocolVersion();
			$client->status = Settings::get()->activeSyncEnable2FA ? 'new' : 'allowed';
			$client->version = \Request::GetUserAgent();
		} else if($client->needResync) {
			$client->needResync = false;
			//ZLOG::Write(LOGLEVEL_INFO, sprintf("Resync of device '%s' of user '%s'", $deviceId, $user->username));
			//ZPushAdmin::ResyncDevice(\Request::GetAuthUser(), $deviceId);
		}
		$client->lastSeen = new DateTime("now", new DateTimeZone("UTC"));
		if(!$client->save()) {
			ZLog::Write(LOGLEVEL_ERROR, 'Failed to save the client identity by device '.var_export($client->getValidationErrors(), true));
		}
		return $client;
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
	 * Returns an understandable folderid for the backend
	 * For example it looks like: "c/1" become "1"
	 *
	 * @param string        $folderid       combinedid of the folder
	 * @return string
	 */
	public function GetBackendFolder($folderid) {
		$pos = strpos($folderid, '/');
		if($pos === false)
			return false;
		return substr($folderid,$pos + strlen('/'));
	}

    /**
     * Returns backend id for a folder
     *
     * @param string        $folderid       combinedid of the folder
     *
     * @access public
     * @return object
     */
    public function GetBackendId($folderid){
        $pos = strpos($folderid, $this->config['delimiter']);
        if($pos === false)
            return false;
        return substr($folderid, 0, $pos);
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
	public function MeetingResponse($requestid, $folderid, $response, $instanceId) {
		ZLog::Write(LOGLEVEL_DEBUG, "Combined->MeetingResponse($requestid , $folderid , $response, $instanceId)");
		$backend = $this->GetBackend($folderid);
		if ($backend === false)
			return false;
		ZLog::Write(LOGLEVEL_DEBUG, "Combined->MeetingResponse($requestid , $folderid , $response, $instanceId) success");
		return $backend->MeetingResponse($requestid, $this->GetBackendFolder($folderid), $response, $instanceId);
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
		
		$attachment = $this->backends['m']->GetAttachmentData($attname);
		if ($attachment instanceof SyncItemOperationsAttachment)
			return $attachment;
		else
			throw new StatusException("Combined->GetAttachmentData(): no backend found", SYNC_ITEMOPERATIONSSTATUS_INVALIDATT);
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
		if (isset($sm->source->folderid)) {
			$sm->source->folderid = $this->GetBackendFolder($sm->source->folderid);
		}
		if ($this->backends['m']->SendMail($sm) == true)
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

		// id might be in the new longid format, so we have to split it here
		list($fsk, $sk) = Utils::SplitMessageId($id);

		$backend = $this->GetBackend($folderid);
		if ($backend == false)
			return false;
		return $backend->Fetch($this->GetBackendFolder($folderid), $sk, $contentparameters);
	}

	function GetWasteBasket() {
		return false; // none of the providers implement this
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

			$h = $this->backends[$i]->GetHierarchy();
			if (is_array($h)) {
				foreach ($h as $j => $f) {
					$h[$j]->serverid = $i . '/' . $h[$j]->serverid;
					if ($h[$j]->parentid != '0') {
						$h[$j]->parentid = $i . '/' . $h[$j]->parentid;
					}
					if (isset(self::FolderProviders[$h[$j]->type]) && self::FolderProviders[$h[$j]->type] != $i) {
						$h[$j]->type = SYNC_FOLDER_TYPE_OTHER;
					}
				}
				$ha = array_merge($ha, $h);
			}
		}
		ZLog::Write(LOGLEVEL_DEBUG, "Combined->GetHierarchy() success");
		return $ha;
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

			//return new ExportChangesDiff($backend, $this->GetBackendFolder($folderid));
			return $backend->GetExporter($this->GetBackendFolder($folderid));
		}
		return new ExportChangesCombined($this);
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
				return new ImportChangesCombined($this, $folderid, $importer);
			}
			return false;
		} else {
			ZLog::Write(LOGLEVEL_DEBUG, "Combined->GetImporter() -> Hierarchy: ImportChangesCombined()");
			//return our own hierarchy importer which send each change to the right backend
//			throw new StatusException();
			return new ImportChangesCombined($this);
		}
	}

    /**
     * Indicates which AS version is supported by the backend.
     * Return the lowest version supported by the backends used.
     *
     * @access public
     * @return string       AS version constant
     */
    public function GetSupportedASVersion() {

        return ZPush::ASV_141;
//        $version = ZPush::ASV_14;
//        foreach ($this->backends as $i => $b) {
//            $subversion = $this->backends[$i]->GetSupportedASVersion();
//            if ($subversion < $version) {
//                $version = $subversion;
//            }
//        }
//        return $version;
    }

    /**
     * Returns the BackendCombined as it implements the ISearchProvider interface
     * This could be overwritten by the global configuration
     *
     * @access public
     * @return object       Implementation of ISearchProvider
     */
    public function GetSearchProvider() {
        return $this;
    }


    /*-----------------------------------------------------------------------------------------
    -- ISearchProvider
    ------------------------------------------------------------------------------------------*/
    /**
     * Indicates if a search type is supported by this SearchProvider
     * It supports all the search types, searches are delegated.
     *
     * @param string        $searchtype
     *
     * @access public
     * @return boolean
     */
    public function SupportsType($searchtype) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Combined->SupportsType('%s')", $searchtype));
        $i = $this->getSearchBackend($searchtype);

        return $i !== false;
    }


    /**
     * Queries the LDAP backend
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
        ZLog::Write(LOGLEVEL_DEBUG, "Combined->GetGALSearchResults()");
        $i = $this->getSearchBackend(ISearchProvider::SEARCH_GAL);

        $result = false;
        if ($i !== false) {
            $result = $this->backends[$i]->GetGALSearchResults($searchquery, $searchrange, $searchpicture);
        }

        return $result;
    }

    /**
     * Searches for the emails on the server
     *
     * @param ContentParameter $cpo
     *
     * @return array
     */
    public function GetMailboxSearchResults($cpo) {
        ZLog::Write(LOGLEVEL_DEBUG, "Combined->GetMailboxSearchResults()");
        $i = $this->getSearchBackend(ISearchProvider::SEARCH_MAILBOX);

        $result = false;
        if ($i !== false) {
            //Convert $cpo GetSearchFolderid
            $cpo->SetSearchFolderid($this->GetBackendFolder($cpo->GetSearchFolderid()));
            $result = $this->backends[$i]->GetMailboxSearchResults($cpo, $i . $this->config['delimiter']);
        }

        return $result;
    }


    /**
     * Terminates a search for a given PID
     *
     * @param int $pid
     *
     * @return boolean
     */
    public function TerminateSearch($pid) {
        ZLog::Write(LOGLEVEL_DEBUG, "BackendGO->TerminateSearch()");
        foreach ($this->backends as $i => $b) {
            if ($this->backends[$i] instanceof ISearchProvider) {
                $this->backends[$i]->TerminateSearch($pid);
            }
        }

        return true;
    }


    /**
     * Disconnects backends
     *
     * @access public
     * @return boolean
     */
    public function Disconnect() {
        ZLog::Write(LOGLEVEL_DEBUG, "BackendGO->Disconnect()");
        foreach ($this->backends as $i => $b) {
            if ($this->backends[$i] instanceof ISearchProvider) {
                $this->backends[$i]->Disconnect();
            }
        }

        return true;
    }


    /**
     * Returns the first backend that support a search type
     *
     * @param string    $searchtype
     *
     * @access private
     * @return string
     */
    private function getSearchBackend($searchtype) {
        foreach ($this->backends as $i => $b) {
            if ($this->backends[$i] instanceof ISearchProvider) {
                if ($this->backends[$i]->SupportsType($searchtype)) {
                    return $i;
                }
            }
        }
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendGO->getSearchBackend('%s') No support found!", $searchtype));

        return false;
    }

	
	protected $syncstates = [];
	protected $sinkfolders = [];
	
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
		foreach ($this->sinkfolders as $folder) {

			$f = $this->GetBackendFolder($folder);
			$b = $this->GetBackend($folder);
			if(!$b) {
                  ZLog::Write(LOGLEVEL_DEBUG, "Backend not found for $folder");
                  continue;
              }
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

		// don't use db connection constantly on push connection
		ZLog::Write(LOGLEVEL_DEBUG, "Closing DB connection: " . go()->getDbConnection()->getId());
		go()->getDbConnection()->disconnect();

		// keep memory usage as low as possible when sleeping
		ZLog::Write(LOGLEVEL_DEBUG, "Clearing cache memory");
		\go\core\db\Table::destroyInstances();
		go()->getCache()->disableMemory();
		gc_collect_cycles();

		ZLog::Write(LOGLEVEL_DEBUG, "Memory: " . memory_get_usage());

		if (empty($notifications)){

			ZLog::Write(LOGLEVEL_DEBUG, "Sleeping ".$timeout." seconds");
			sleep($timeout);
		}
		return $notifications;
	}
}
