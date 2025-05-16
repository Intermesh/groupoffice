<?php

use GO\Base\Util\StringHelper;
use go\core\model\Acl;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;

class ContactStore extends Store implements ISearchProvider {
	
	private $convertor;
	public function __construct() {
		parent::__construct();
		
		$this->convertor = new ContactConvertor();
	}

	public function DeleteMessage($folderid, $id, $contentParameters) {
		ZLog::Write(LOGLEVEL_DEBUG, 'goContact->DeleteMessage('.$folderid.','.$id.')');

		if(!go()->getAuthState()->getUser(['syncSettings'])->syncSettings->allowDeletes) {
			ZLog::Write(LOGLEVEL_DEBUG, 'Deleting by sync is disabled in user settings');
			throw new StatusException("Access denied", SYNC_ITEMOPERATIONSSTATUS_DL_ACCESSDENIED);
		}

		$contact = Contact::findById($id);
		
		if (!$contact) {
			return true;
		} else if($contact->getPermissionLevel() < Acl::LEVEL_DELETE){
			throw new StatusException("Access denied", SYNC_ITEMOPERATIONSSTATUS_DL_ACCESSDENIED);
		} else {
			return $contact->delete($contact->primaryKeyValues()); // This throws an error when the contact is read only
		}
	}
	
	/**
	 * Get the item object that needs to be synced to the phone.
	 * This information will be send to the phone.
	 * 
	 * Direction: SERVER -> PHONE
	 * 
	 * @param string $folderid
	 * @param int $id
	 * @param SyncParameters $contentParameters
	 * @return SyncContact
	 */
	public function GetMessage($folderid, $id, $contentParameters) {

		ZLog::Write(LOGLEVEL_DEBUG, "GetMessage($folderid, $id, ...)");
		
		$contact = Contact::findById($id);	
		if (!$contact) {
			ZLog::Write(LOGLEVEL_WARN, "Contact with ID: ".$id." not found!");
			return false;
		}

		if(!$contact->getPermissionLevel()) {
			throw new StatusException("Access denied", SYNC_ITEMOPERATIONSSTATUS_DL_ACCESSDENIED);
		}

		try {
			$message = $this->convertor->GO2AS($contact, $contentParameters);
		} catch(Exception $e) {
			ZLog::Write(LOGLEVEL_FATAL, 'Contacts::EXCEPTION ~~ ' .  $e->getMessage());
			ZLog::Write(LOGLEVEL_DEBUG, $e->getTraceAsString());
			return false;
		}
		
//		ZLog::Write(LOGLEVEL_DEBUG, var_export($message, true));
		return $message;		
	}


	/**
	 * Save the information from the phone to Group-Office.
	 *
	 * Direction: PHONE -> SERVER
	 *
	 * @param string $folderid
	 * @param int $id
	 * @param SyncContact $message
	 * @param SyncParameters $contentParameters
	 * @return array
	 * @throws StatusException
	 */
	public function ChangeMessage($folderid, $id, $message, $contentParameters)
	{
		
		ZLog::Write(LOGLEVEL_DEBUG, "ChangeMessage($folderid, $id, .., ..)");
//		ZLog::Write(LOGLEVEL_DEBUG, var_export($message, true));

		$contact = empty($id) ? false : Contact::findById($id);

		if (!$contact) {
			$contact = new Contact();
			//address list can't be determined on the iPhone :( and a user reported it's the same for
			// Android. Therefore we use the default.
			$contact->addressBookId = $this->convertor->getDefaultAddressBook()->id;
		} else
		{
			ZLog::Write(LOGLEVEL_DEBUG, "Found contact");
		}

		if ($contact->getPermissionLevel() < Acl::LEVEL_WRITE) {
			throw new StatusException("Access denied", SYNC_ITEMOPERATIONSSTATUS_DL_ACCESSDENIED);
		}

		try {
			$this->convertor->AS2GO($message, $contact);
		} catch(Exception $e) {
			ZLog::Write(LOGLEVEL_FATAL, 'Contacts::EXCEPTION ~~ ' .  $e->getMessage());
			ZLog::Write(LOGLEVEL_DEBUG, $e->getTraceAsString());
			return false;
		}
	
		return $this->StatMessage($folderid, $contact->id);		

	}

	/**
	 * Get the status of an item
	 * 
	 * @param string $folderid
	 * @param int $id
	 * @return array
	 */
	public function StatMessage($folderid, $id) {
		ZLog::Write(LOGLEVEL_DEBUG, "StatMessage($folderid, $id)");
		
		$contact = Contact::findById($id);
		
		$stat = false;
		
		if ($contact) {
			$stat = array();
			$stat["id"] = $contact->id . "";
			$stat["flags"] = "1";
			$stat['mod'] = $contact->modifiedAt->format("U");			
		}


		// ZLog::Write(LOGLEVEL_DEBUG, var_export($stat, true));

		return $stat;
	}

	/**
	 * Get the list of the items that need to be synced
	 * 
	 * @param string $folderid
	 * @param type $cutoffdate
	 * @return array
	 */
	public function GetMessageList($folderid, $cutoffdate) {

		ZLog::Write(LOGLEVEL_DEBUG, "GetMessageList($folderid, $cutoffdate)");
		
		$list = Contact::find()
						->select('c.id, UNIX_TIMESTAMP(c.modifiedAt) AS `mod`, "1" AS flags')
//						->join("sync_addressbook_user", "u", "u.addressBookId = c.addressBookId")
//						->andWhere('u.userId', '=', go()->getAuthState()->getUserId())
						->andWhere('c.addressBookId', '=', $folderid)
						->andWhere(['c.isOrganization' => false])	 //Does not work reliably on ios
						->fetchMode(PDO::FETCH_ASSOC)
						->filter([
								"permissionLevel" => Acl::LEVEL_READ
						])->all();
		// ZLog::Write(LOGLEVEL_DEBUG, var_export($list, true));
		return $list;
	}

	/**
	 * Get the syncFolder that is attached to the given id
	 * 
	 * @param string $id
	 * @return SyncFolder
	 */
	public function GetFolder($id) {

		$addressBook = AddressBook::findById($id);
		if(!$addressBook) {
			ZLog::Write(LOGLEVEL_WARN, "Contact folder '$id' not found");
			return false;
		}

		$folder = new SyncFolder();
		$folder->serverid = $id;
		$folder->parentid = "0";
		$folder->displayname = $addressBook->name;

		$folder->type = SYNC_FOLDER_TYPE_CONTACT;

		return $folder;
	}

	/**
	 * Get a list of folders that are located in the current folder
	 * 
	 * @return array
	 */
	public function GetFolderList() {
		$folders = array();

		$addressBooks = AddressBook::find()
			->selectSingleValue('a.id')
			->join("sync_addressbook_user", "u", "u.addressBookId = a.id")
			->andWhere('u.userId', '=', go()->getAuthState()->getUserId())
			->filter([
				"permissionLevel" => Acl::LEVEL_READ
			])->all();

		foreach($addressBooks as $id) {
			$folder = $this->StatFolder($id);
			$folders[] = $folder;
		}

		return $folders;
	}

	
	public function getNotification($folder = null) {

		$stmt = Contact::find()
			->removeJoins()
			->fetchMode(PDO::FETCH_ASSOC)
			->select('COALESCE(count(*), 0) AS count, COALESCE(max(modifiedAt), 0) AS modifiedAt')
			->where('addressBookId = :addressBookId')
			->createStatement();

		$stmt->bindValue(':addressBookId', $folder, PDO::PARAM_INT);
		$stmt->execute();
		$record = $stmt->fetch();

		$newstate = $record ? 'M'.$record['modifiedAt'].':C'.$record['count'] : "M0:C0";
		ZLog::Write(LOGLEVEL_DEBUG,'goContact->getNotification('.$folder.') State: '.$newstate);

		return $newstate;
	}


	public function GetGALSearchResults($searchquery, $searchrange, $searchpicture) {
		ZLog::Write(LOGLEVEL_DEBUG, sprintf("ContactStore->GetGALSearchResults(%s, %s)", $searchquery, $searchrange));



			// range for the search results, default symbian range end is 50, wm 99,
			// so we'll use that of nokia
			$rangestart = 0;
			$rangeend = 50;

			if ($searchrange != '0') {
				$pos = strpos($searchrange, '-');
				$rangestart = substr($searchrange, 0, $pos);
				$rangeend = substr($searchrange, ($pos + 1));
			}
			$items = array();


			$contacts = Contact::find()->filter([
				"permissionLevel" => Acl::LEVEL_READ,
				"text" => $searchquery
			])
				->limit($rangeend - $rangestart)
				->offset($rangestart)
				->calcFoundRows();

			$items['searchtotal'] = $contacts->foundRows();

			//do not return more results as requested in range
			$querylimit = (($rangeend + 1) < $items['searchtotal']) ? ($rangeend + 1) : ($items['searchtotal'] == 0 ? 1 : $items['searchtotal']);
			$items['range'] = $rangestart.'-'.($querylimit - 1);


			ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->GetGALSearchResults : %s entries found, returning %s to %s", $items['searchtotal'], $rangestart, $querylimit));

			$rc = 0;
			foreach ($contacts as $contact) {
				$items[$rc][SYNC_GAL_EMAILADDRESS] = !empty($contact->emailAddresses) ? $contact->emailAddresses[0] : '';
				$items[$rc][SYNC_GAL_DISPLAYNAME] = $contact->name;
				$items[$rc][SYNC_GAL_FIRSTNAME] = $contact->firstName;
				$items[$rc][SYNC_GAL_LASTNAME] = $contact->lastName;

				$items[$rc][SYNC_GAL_PHONE] = $contact->findPhoneNumberByType(\go\modules\community\addressbook\model\PhoneNumber::TYPE_WORK);
				$items[$rc][SYNC_GAL_HOMEPHONE] = $contact->findPhoneNumberByType(\go\modules\community\addressbook\model\PhoneNumber::TYPE_HOME);
				$items[$rc][SYNC_GAL_MOBILEPHONE] = $contact->findPhoneNumberByType(\go\modules\community\addressbook\model\PhoneNumber::TYPE_MOBILE);


				$items[$rc][SYNC_GAL_TITLE] = $contact->jobTitle;
				$org = $contact->findOrganizations(['name'])->single();
				if ($org) {
					$items[$rc][SYNC_GAL_COMPANY] = $org->name;
				}
				$items[$rc][SYNC_GAL_OFFICE] = $contact->department;
				$rc++;
			}

			return $items;
	}

	/**
	 * Indicates if a search type is supported by this SearchProvider
	 * Currently only the type ISearchProvider::SEARCH_GAL (Global Address List) is implemented
	 *
	 * @param string        $searchtype
	 *
	 * @access public
	 * @return boolean
	 */
	public function SupportsType($searchtype) {

		return ($searchtype == ISearchProvider::SEARCH_GAL);

	}


	/**
	 * Searches for the emails on the server
	 *
	 * @param ContentParameter $cpo
	 *
	 * @return array
	 */
	public function GetMailboxSearchResults($cpo) {
		return false;
	}

	public function TerminateSearch($pid)
	{
		return true;
	}

	public function Disconnect()
	{
		return true;
	}
}
