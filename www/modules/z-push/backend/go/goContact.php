<?php

class goContact extends GoBaseBackendDiff {

	public function DeleteMessage($folderid, $id, $contentparameters) {
		ZLog::Write(LOGLEVEL_DEBUG, 'goContact->DeleteMessage('.$folderid.','.$id.')');
		
		$contact = \GO\Addressbook\Model\Contact::model()->findByPk($id);
		
		if (!$contact) {
			return true;
		} else if(!$contact->checkPermissionLevel(\GO\Base\Model\Acl::DELETE_PERMISSION)){
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
	 * @return \SyncContact
	 */
	public function GetMessage($folderid, $id, $contentparameters) {

		$contact = \GO\Addressbook\Model\Contact::model()->findByPk($id);

		$message = new SyncContact();

		if ($contact) {

			// TODO: CHECK IF THIS ONE IS NEEDED (copied from z-push 1.5 implementation)
			// had to add 12 hours for Android but this screws up other devices
			if (!empty($contact->birthday) && $contact->birthday != '0000-00-00' && $this->_checkBirthday($contact->birthday)) {
				$tz = date_default_timezone_get();
				date_default_timezone_set('UTC');
				$message->birthday = strtotime($contact->birthday);
				date_default_timezone_set($tz);
			}
				
			$bpReturnType = GoSyncUtils::getBodyPreferenceMatch($contentparameters->GetBodyPreference());

			if (Request::GetProtocolVersion() >= 12.0) {
				$message->asbody = GoSyncUtils::createASBodyForMessage($contact,'comment',$bpReturnType);
			} else {
				$message->body = \GO\Base\Util\StringHelper::normalizeCrlf($contact->comment);
				$message->bodysize = strlen($message->body);
				$message->bodytruncated = 0;
			}
			
			$message->businessfaxnumber = $contact->work_fax;
			$message->businessphonenumber = $contact->work_phone; // is overwritten if company is attached to contact
			$message->department = $contact->department;
			$message->email1address = $contact->email;
			$message->email2address = $contact->email2;
			$message->email3address = $contact->email3;
			$message->fileas = $contact->name;
			$message->firstname = $contact->first_name;
			$message->homecity = $contact->city;
			$message->homecountry = $contact->country;
			$message->homepostalcode = $contact->zip;
			$message->homestate = $contact->state;
			$message->homestreet = trim($contact->address . ' ' . $contact->address_no);
			$message->homefaxnumber = $contact->fax;
			$message->homephonenumber = $contact->home_phone;
			$message->jobtitle = $contact->function;
			$message->lastname = $contact->last_name;
			$message->middlename = $contact->middle_name;
			$message->mobilephonenumber = $contact->cellular;
			$message->suffix = $contact->suffix;
			$message->title = $contact->title;
			$message->webpage = $contact->homepage;
//			$message->anniversary;
//			$message->assistantname;
//			$message->assistnamephonenumber;
//			$message->home2phonenumber;
			$message->carphonenumber = $contact->cellular2;
//			$message->categories;
//			$message->children;
//			$message->officelocation;
//			$message->pagernumber;
//			$message->radiophonenumber;
//			$message->spouse;
//			$message->yomicompanyname;
//			$message->yomifirstname;
//			$message->yomilastname;
//			$message->rtf;
//			$message->nickname;

			$photoFile = $contact->getPhotoFile();
			if ($photoFile->exists()) {
				$pic=base64_encode($photoFile->getContents());				
				if(strlen($message->picture)<=49152)
					$message->picture=$pic;
				
				unset($pic);
			}

			// Check for a company for this contact
			$company = $contact->company;
			/* @var $company \GO\Addressbook\Model\Company */
			if ($company) {
//				$message->business2phonenumber = $company->phone; //Disabled due to problems with the display on the mobile device of contact->work_phone and company->phone
				$message->companyname = $company->name;
				$message->businessstreet = trim($company->address . ' ' . $company->address_no);
				$message->businesscity = $company->city;
				$message->businessstate = $company->state;
				$message->businesspostalcode = $company->zip;
				$message->businesscountry = $company->country;
				$message->otherstreet = $company->post_address;
				$message->othercity = $company->post_city;
				$message->otherstate = $company->post_state;
				$message->otherpostalcode = $company->post_zip;
				$message->othercountry = $company->post_country;
			}
		}else
		{
			ZLog::Write(LOGLEVEL_WARN, "Contact with ID: ".$id." not found!");
		}

		return $message;
		
	}

	/**
	 * Save the information from the phone to Group-Office.
	 * 
	 * Direction: PHONE -> SERVER
	 * 
	 * @param StringHelper $folderid
	 * @param int $id
	 * @param \SyncContact $message
	 * @return array
	 */
	public function ChangeMessage($folderid, $id, $message, $contentParameters)
	{

		try {

			$contact = \GO\Addressbook\Model\Contact::model()->findByPk($id);

			if (!$contact) {
				$addressbook = GoSyncUtils::getUserSettings()->getDefaultAddressbook();

				if (!$addressbook)
					throw new \Exception("FATAL: No default addressbook configured");

				$contact = new \GO\Addressbook\Model\Contact();
				$contact->addressbook_id = $addressbook->id;
			}

			if ($contact->permissionLevel < \GO\Base\Model\Acl::WRITE_PERMISSION) {
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
				$contact->email = \GO\Base\Util\StringHelper::get_email_from_string ($message->email1address);
//			if (isset($message->email2address))
				$contact->email2 = \GO\Base\Util\StringHelper::get_email_from_string ($message->email2address);
//			if (isset($message->email3address))
				$contact->email3 = \GO\Base\Util\StringHelper::get_email_from_string ($message->email3address);
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
					$destinationFile = \GO\Base\Fs\File::tempFile('','jpg');
					$destinationFile->putContents($photo_content);
					$contact->setPhoto($destinationFile);
					$contact->save();
				} catch (\Exception $e) {
					ZLog::Write(LOGLEVEL_DEBUG, (string) $e);
				}
			}
			
			$id = $contact->id;
		} catch (\Exception $e) {
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

		$contact = \GO\Addressbook\Model\Contact::model()->findByPk($id);
		
		$stat = false;
		
		if ($contact) {
			$stat = array();
			$stat["id"] = $contact->id;
			$stat["flags"] = 1;

			if ($contact->company && $contact->company->mtime > $contact->mtime)
				$stat['mod'] = $contact->company->mtime;
			else
				$stat['mod'] = $contact->mtime;
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

		$messages = array();
		if (\GO::modules()->addressbook) {

			$params = \GO\Base\Db\FindParams::newInstance()
							->ignoreAcl()
							->select('t.id,t.mtime,t.company_id')
							->joinModel(array(
									'model' => 'GO\Sync\Model\UserAddressbook',
									'tableAlias' => 'ua',
									'localTableAlias' => 't',
									'localField' => 'addressbook_id',
									'foreignField' => 'addressbook_id'
							))
							->criteria(
							\GO\Base\Db\FindCriteria::newInstance()
							->addCondition('user_id', \GO::user()->id, '=', 'ua')
			);
			$stmt = \GO\Addressbook\Model\Contact::model()->find($params);

			while ($contact = $stmt->fetch()) {
				$message = array();
				$message['id'] = $contact->id;
				$message['mod'] = $contact->mtime;

				if ($contact->company)
					$message['mod'] = $contact->company->mtime > $contact->mtime ? $contact->company->mtime : $contact->mtime;

				$message['flags'] = 1;
				$messages[] = $message;
			}
			
			ZLog::Write(LOGLEVEL_DEBUG, 'Number of contacts to sync: ' . count($messages));
		}

		return $messages;
	}

	/**
	 * Get the syncFolder that is attached to the given id
	 * 
	 * @param StringHelper $id
	 * @return \SyncFolder
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
		
		

		$params = \GO\Base\Db\FindParams::newInstance()
						->ignoreAcl()
						->single(true, true)
						->select('count(*) AS count, max(mtime) AS lastmtime')
						->join(\GO\Sync\Model\UserAddressbook::model()->tableName(), \GO\Base\Db\FindCriteria::newInstance()
						->addCondition('addressbook_id', 's.addressbook_id', '=', 't', true, true)
						->addCondition('user_id', \GO::user()->id, '=', 's')
						, 's');

		$record = \GO\Addressbook\Model\Contact::model()->find($params);

		$lastmtime = isset($record->lastmtime) ? $record->lastmtime : 0;
		$newstate = 'M'.$lastmtime.':C'.$record->count;
		
		
		ZLog::Write(LOGLEVEL_DEBUG,'goContact->getNotification() State: '.$newstate);

		return $newstate;
	}

}
