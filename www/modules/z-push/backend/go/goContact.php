<?php

use GO\Base\Db\FindCriteria;
use GO\Base\Db\FindParams;
use GO\Base\Fs\File;
use GO\Base\Model\Acl as Acl2;
use GO\Base\Util\StringHelper;
use go\core\acl\model\Acl;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\Date;
use go\modules\community\addressbook\model\PhoneNumber;
use go\modules\community\addressbook\model\Url;
use GO\Sync\Model\UserAddressbook;

class goContact extends GoBaseBackendDiff {

	public function DeleteMessage($folderid, $id, $contentparameters) {
		ZLog::Write(LOGLEVEL_DEBUG, 'goContact->DeleteMessage('.$folderid.','.$id.')');
		
		$contact = Contact::findById($id);		
		
		if (!$contact) {
			return true;
		} else if(!$contact->getPermissionLevel() >= Acl::LEVEL_DELETE){
			return true;
		} else {
			return $contact->delete(); // This throws an error when the contact is read only
		}
	}
	
	
	private function _checkBirthday($birthday){
		
		if(preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $birthday) === 0){
			ZLog::Write(LOGLEVEL_WARN, "No valid birthday!");
			return false;
		}
		if(date('Y',  strtotime($birthday)) < '1900'){
			ZLog::Write(LOGLEVEL_WARN, "No valid birthday!");
			return false;
		}
		return true;
	}
	
	/**
	 * Get the item object that needs to be synced to the phone.
	 * This information will be send to the phone.
	 * 
	 * Direction: SERVER -> PHONE
	 * 
	 * @param StringHelper $folderid
	 * @param int $id
	 * @param array $contentparameters
	 * @return SyncContact
	 */
	public function GetMessage($folderid, $id, $contentparameters) {

		ZLog::Write(LOGLEVEL_DEBUG, "GetMessage($folderid, $id, ...)");
		
		$contact = Contact::findById($id);		

		$message = new SyncContact();

		if (!$contact) {
			ZLog::Write(LOGLEVEL_WARN, "Contact with ID: ".$id." not found!");
			return false;
		}
		
		$birthday = $contact->findDateByType(Date::TYPE_BIRTHDAY, false);
		if($birthday) {
			$message->birthday = $birthday->date->format("U");
		}

				
		$bpReturnType = GoSyncUtils::getBodyPreferenceMatch($contentparameters->GetBodyPreference());

		if (Request::GetProtocolVersion() >= 12.0) {
			$message->asbody = GoSyncUtils::createASBodyForMessage($contact,'notes',$bpReturnType);
		} else {
			$message->body = StringHelper::normalizeCrlf($contact->comment);
			$message->bodysize = strlen($message->body);
			$message->bodytruncated = 0;
		}
		
		$this->hasManyToFlat($contact->phoneNumbers, $message, [
				PhoneNumber::TYPE_WORK => [["number"=> "businessphonenumber"], ["number"=> "business2phonenumber"], ["number"=> "companymainphone"]],
				PhoneNumber::TYPE_FAX => [["number"=> "businessfaxnumber"]],
				PhoneNumber::TYPE_MOBILE => [["number"=> "mobilephonenumber"], ["number"=> "carphonenumber"]],
				PhoneNumber::TYPE_HOME => [["number"=> "homephonenumber"], ["number"=> "home2phonenumber"]],
		]);
		
		$this->hasManyToFlat($contact->dates, $message, [
				Date::TYPE_BIRTHDAY => [["date" => "birthday"]],
				Date::TYPE_ANNIVERSARY => [["date" => "anniversary"]]
				
		]);
		
		
		
		foreach($contact->emailAddresses as $e) {
			if(!isset($message->email1address)) {
				$message->email1address = $e->email;
			} elseif(!isset($message->email2address)) {
				$message->email2address = $e->email;
			} elseif(!isset($message->email3address)) {
				$message->email3address = $e->email;
			}
		}

		//$message->department = $contact->department;
		$message->jobtitle = $contact->jobTitle;
		
		$message->fileas = $contact->name;
		if(!$contact->isOrganization) {
			$message->firstname = $contact->firstName;
			$message->lastname = $contact->lastName;
			$message->middlename = $contact->middleName;	
			$message->suffix = $contact->suffixes;
			$message->title = $contact->prefixes;
			
			$companies = Contact::find()
							->withLink($contact)
							->andWhere('isOrganization', '=', true)
							->selectSingleValue('name')
							->all();
			
			$message->companyname = implode(',', $companies);
		} else
		{
			$message->companyname = $contact->name;
		}
		
		$this->hasManyToFlat($contact->addresses, $message, [
				\go\modules\community\addressbook\model\Address::TYPE_POSTAL => [[
					"zipCode" => "otherpostalcode",
					"street" => function($i, $message) {$message->otherstreet .= trim($i->street.' '.$i->street2);  },					
					"city" => 'othercity',
					"state" => "otherstate",
					"country" => "othercountry"
			]],
			\go\modules\community\addressbook\model\Address::TYPE_HOME => [[
					"zipCode" => "homepostalcode",
					"street" => function($i, $message) {$message->homestreet .= trim($i->street.' '.$i->street2);  },					
					"city" => 'homecity',
					"state" => "homestate",
					"country" => "homecountry"
			]],
			\go\modules\community\addressbook\model\Address::TYPE_WORK => [[
					"zipCode" => "businesspostalcode",
					"street" => function($i, $message) {$message->businessstreet .= trim($i->street.' '.$i->street2);  },					
					"city" => 'businesscity',
					"state" => "businessstate",
					"country" => "businesscountry"
			]],
			\go\modules\community\addressbook\model\Address::TYPE_VISIT => [[
					"zipCode" => "homepostalcode",
					"street" => function($i, $message) {$message->homestreet .= trim($i->street.' '.$i->street2);  },					
					"city" => 'homecity',
					"state" => "homestate",
					"country" => "homecountry"
			],[
					"zipCode" => "otherpostalcode",
					"street" => function($i, $message) {$message->otherstreet .= trim($i->street.' '.$i->street2);  },					
					"city" => 'othercity',
					"state" => "otherstate",
					"country" => "othercountry"
			]],			
		]);		
		
		
		
		
		
		$homepage = $contact->findUrlByType(Url::TYPE_HOMEPAGE);
		if($homepage) {
			$message->webpage = $homepage->url;
		}


		$blob = isset($contact->photoBlobId) ? \go\core\fs\Blob::findById($contact->photoBlobId) : false;
		if($blob && $blob->getFile()->exists()) {		
			$pic = base64_encode($blob->getFile()->getContents());			
			$message->picture = $pic;			
		}

		
		ZLog::Write(LOGLEVEL_DEBUG, var_export($message, true));
		return $message;		
	}
	
	/**
	 * Convert GO has many properties with a type to the syncmessage names.
	 * 
	 * @param type $items
	 * @param SyncContact $message
	 * @param array $mapping
	 * @param string $propName
	 * @return type
	 */
	private function hasManyToFlat($items, SyncContact $message, array $mapping) {
		
		$undefined = [];
		
		foreach($items as $i) {
			if(!$this->applyItem($i, $message, $mapping)){ 		
				$undefined[] = $i;
			}
		}
		
		if(empty($undefined)) {
			return;
		}
		
		foreach($undefined as $i) {
			$this->applyItem($i, $message, $mapping, true);
		}		
	}
	
	private function applyItem($i, $message, $mapping,  $ignoreType = false) {
//		ZLog::Write(LOGLEVEL_DEBUG, "Applying ". var_export($i->toArray(), true));
		if(!$ignoreType) {
			if(!isset($mapping[$i->type])) {
				return false;
			}			
			$m = $mapping[$i->type];
		} else
		{
			$m = [];
			foreach($mapping as $s) {
				$m = array_merge($m, $s);
			}
		}
		
		foreach($m as $syncProps) {
			$firstPropName = array_values($syncProps)[0];
			if(empty($message->$firstPropName)) {
//				ZLog::Write(LOGLEVEL_DEBUG, "Applying to ". $firstPropName);
				foreach($syncProps as $from => $to) {
					if(is_callable($to)) {
						call_user_func($to, $i, $message);
					} else
					{
						$message->$to = $i->$from;
					}
				}
				return true;
			} else
			{
				ZLog::Write(LOGLEVEL_DEBUG, "Already set: ". $firstPropName);
			}
		}
		
		return false;
	}

	/**
	 * Save the information from the phone to Group-Office.
	 * 
	 * Direction: PHONE -> SERVER
	 * 
	 * @param StringHelper $folderid
	 * @param int $id
	 * @param SyncContact $message
	 * @return array
	 */
	public function ChangeMessage($folderid, $id, $message, $contentParameters)
	{

		try {
			
			return false;

			$contact = \GO\Addressbook\Model\Contact::model()->findByPk($id);

			if (!$contact) {
				$addressbook = GoSyncUtils::getUserSettings()->getDefaultAddressbook();

				if (!$addressbook)
					throw new Exception("FATAL: No default addressbook configured");

				$contact = new \GO\Addressbook\Model\Contact();
				$contact->addressbook_id = $addressbook->id;
			}

			if ($contact->permissionLevel < Acl2::WRITE_PERMISSION) {
				ZLog::Write(LOGLEVEL_DEBUG, "Skipping update of read-only contact " . $contact->name);
				return $this->StatMessage($folderid, $id);
			}

			// PHONE IS RETURNING THIS: 1987-12-05T08:00:00.000Z AND Z-PUSH CONVERTS IT TO A TIMESTAMP( 1378857600 )
			if (isset($message->birthday))
				$contact->birthday = date('Y-m-d',  $message->birthday); 

//			if (isset($message->businessfaxnumber))
				$contact->work_fax = $message->businessfaxnumber;
//			if (isset($message->businessphonenumber))
				$contact->work_phone = $message->businessphonenumber;
//			if (isset($message->department))
				$contact->department = $message->department;
//			if (isset($message->email1address))
				$contact->email = StringHelper::get_email_from_string ($message->email1address);
//			if (isset($message->email2address))
				$contact->email2 = StringHelper::get_email_from_string ($message->email2address);
//			if (isset($message->email3address))
				$contact->email3 = StringHelper::get_email_from_string ($message->email3address);
//			if (isset($message->firstname))
				$contact->first_name = $message->firstname;
//			if (isset($message->homecity))
				$contact->city = $message->homecity;
//			if (isset($message->homecountry))
				$contact->country = $message->homecountry;
//			if (isset($message->homepostalcode))
				$contact->zip = $message->homepostalcode;
//			if (isset($message->homestate))
				$contact->state = $message->homestate;

//			if (isset($message->homestreet)) {
				if (trim($contact->address . ' ' . $contact->address_no) != $message->homestreet) {
					/*
					 * ActiveSync doesn't support a separate Housenumber field so try to
					 * match it and store it separately.
					 */
					$match = preg_match('/(.*) ([0-9]+[A-Za-z\-]*)$/', $message->homestreet, $matches);

					if ($match) {
						$contact->address = $matches[1];
						$contact->address_no = $matches[2];
					} else {
						$contact->address = $message->homestreet;
						$contact->address_no = "";
					}
//				}
			}

//			if (isset($message->homefaxnumber))
				$contact->fax = $message->homefaxnumber;
//			if (isset($message->homephonenumber))
				$contact->home_phone = $message->homephonenumber;
//			if (isset($message->jobtitle))
				$contact->function = $message->jobtitle;
//			if (isset($message->lastname))
				$contact->last_name = $message->lastname;
//			if (isset($message->middlename))
				$contact->middle_name = $message->middlename;
//			if (isset($message->mobilephonenumber))
				$contact->cellular = $message->mobilephonenumber;
//			if (isset($message->carphonenumber))
				$contact->cellular2 = $message->carphonenumber;
//			if (isset($message->suffix))
				$contact->suffix = $message->suffix;
//			if (isset($message->title))
				$contact->title = $message->title;
//			if (isset($message->webpage))
				$contact->homepage = $message->webpage;

			//			$message->anniversary;
			//			$message->assistantname;
			//			$message->assistnamephonenumber;
			//			$message->bodysize;
			//			$message->bodytruncated;
			//			
			//			$message->carphonenumber;
			//			$message->categories;
			//			$message->children;
			
			if (isset($message->companyname)) {
				$company = \GO\Addressbook\Model\Company::model()->findSingleByAttributes(array('name' => $message->companyname, 'addressbook_id' => $contact->addressbook_id));
				if (!$company) {
					$company = new \GO\Addressbook\Model\Company();
					$company->addressbook_id = $contact->addressbook_id;
				}
				$company->name = $message->companyname;
				
				// Removed after request of a ticket: #201612770
//				if(isset($message->business2phonenumber) && empty($company->phone)){
//					$company->phone = $message->business2phonenumber;
//				}	
				
				if(isset($message->businessstreet) && empty($company->address)){
					
					if (trim($company->address . ' ' . $company->address_no) != $message->businessstreet) {
						/*
						 * ActiveSync doesn't support a separate Housenumber field so try to
						 * match it and store it separately.
						 */
						$match = preg_match('/(.*) ([0-9]+[A-Za-z\-]*)$/', $message->businessstreet, $matches);

						if ($match) {
							$company->address = $matches[1];
							$company->address_no = $matches[2];
						} else {
							$company->address = $message->businessstreet;
							$company->address_no = "";
						}
					}
				}	
				if(isset($message->businesscity) && empty($company->city)){
					$company->city = $message->businesscity;
				}				
				if(isset($message->businessstate) && empty($company->state)){
					$company->state = $message->businessstate;
				}				
				if(isset($message->businesspostalcode) && empty($company->zip)){
					$company->zip = $message->businesspostalcode;
				}				
				if(isset($message->businesscountry) && empty($company->country)){
					$company->country = $message->businesscountry;
				}				
				if(isset($message->otherstreet) && empty($company->post_address)){
					if (trim($company->post_address . ' ' . $company->post_address) != $message->otherstreet) {
						/*
						 * ActiveSync doesn't support a separate Housenumber field so try to
						 * match it and store it separately.
						 */
						$match = preg_match('/(.*) ([0-9]+[A-Za-z\-]*)$/', $message->otherstreet, $matches);

						if ($match) {
							$company->post_address = $matches[1];
							$company->post_address_no = $matches[2];
						} else {
							$company->post_address = $message->otherstreet;
							$company->post_address_no = "";
						}
					}
				}
				if(isset($message->othercity) && empty($company->post_city)){
					$company->post_city = $message->othercity;
				}
				if(isset($message->otherstate) && empty($company->post_state)){
					$company->post_state = $message->otherstate;
				}
				if(isset($message->otherpostalcode) && empty($company->post_zip)){
					$company->post_zip = $message->otherpostalcode;
				}
				if(isset($message->othercountry) && empty($company->post_country)){
					$company->post_country = $message->othercountry;
				}

				if($company->save()){
					$contact->setAttribute('company_id', $company->id);
				}
			}else
			{
				$contact->setAttribute('company_id', 0);
			}

			//			$message->home2phonenumber;
			//			$message->fileas;
			//			$message->officelocation;
			//			
			//			$message->pagernumber;
			//			$message->radiophonenumber;
			//			$message->spouse;
			//			$message->yomicompanyname;
			//			$message->yomifirstname;
			//			$message->yomilastname;
			//			$message->rtf;
			//			$message->nickname;


			$contact->comment = GoSyncUtils::getBodyFromMessage($message);

			$contact->cutAttributeLengths();

			if(!$contact->save()){
				ZLog::Write(LOGLEVEL_WARN, 'ZPUSH2CONTACT::Could not save contact ' . $contact->id);
				
				ZLog::Write(LOGLEVEL_WARN, var_export($contact->getValidationErrors(), true));
				return false;
			}
			
			if (!empty($message->picture)) {
				try {
					$photo_content = base64_decode($message->picture);
					$destinationFile = File::tempFile('','jpg');
					$destinationFile->putContents($photo_content);
					$contact->setPhoto($destinationFile);
					$contact->save();
				} catch (Exception $e) {
					ZLog::Write(LOGLEVEL_DEBUG, (string) $e);
				}
			}
			
			$id = $contact->id;
		} catch (Exception $e) {
			ZLog::Write(LOGLEVEL_WARN, 'ZPUSH2CONTACT::EXCEPTION ~~ ' . (string) $e);
		}

		return $this->StatMessage($folderid, $id);
	}

	/**
	 * Get the status of an item
	 * 
	 * @param StringHelper $folderid
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

		return $stat;
	}

	/**
	 * Get the list of the items that need to be synced
	 * 
	 * @param StringHelper $folderid
	 * @param type $cutoffdate
	 * @return array
	 */
	public function GetMessageList($folderid, $cutoffdate) {

		ZLog::Write(LOGLEVEL_DEBUG, "GetMessageList($folderid, $cutoffdate)");
		
		$list = Contact::find()
						->select('c.id, UNIX_TIMESTAMP(c.modifiedAt) AS `mod`, "1" AS flags')
						->fetchMode(\PDO::FETCH_ASSOC)
						->filter([
								"permissionLevel" => Acl::LEVEL_READ
						])->all();
		
		return $list;
	}

	/**
	 * Get the syncFolder that is attached to the given id
	 * 
	 * @param StringHelper $id
	 * @return SyncFolder
	 */
	public function GetFolder($id) {

		$folder = new SyncFolder();
		$folder->serverid = $id;
		$folder->parentid = "0";
		$folder->displayname = 'Contacts';
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
		$folder = $this->StatFolder(BackendGoConfig::CONTACTBACKENDFOLDER);
		$folders[] = $folder;

		return $folders;
	}
	
	public function getNotification($folder=null) {
		return false;// Contact::getState();
	}

}
