<?php

namespace go\modules\community\addressbook\controller;

use Exception;
use go\core\cli\Controller;
use go\core\util\DateTime;
use go\modules\community\addressbook\model\Address;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\Date;
use go\modules\community\addressbook\model\EmailAddress;
use go\modules\community\addressbook\model\PhoneNumber;
use go\modules\community\addressbook\model\Url;
use function GO;

class Migrate extends Controller {

	public function run() {

		$db = GO()->getDbConnection();
		//Start from scratch
		$db->query("DELETE FROM addressbook_addressbook");

		$addressBooks = $db->select()->from('ab_addressbooks');

		foreach ($addressBooks as $abRecord) {
			if ($this->isAddressbookEmpty($abRecord['id'])) {
				continue;
			}

			$addressBook = new AddressBook();
			$addressBook->id = $abRecord['id'];
			$addressBook->createdBy = $abRecord['user_id'];
			$addressBook->aclId = $abRecord['acl_id'];
			$addressBook->name = $abRecord['name'];
			if (!$addressBook->save()) {
				throw new Exception("Could not save addressbook");
			}

			$this->copyContacts($addressBook);
		}
	}

	private function isAddressbookEmpty($id) {
		$record = GO()->getDbConnection()->select('id')->from('ab_addressbooks')->where(['id' => $id])->single();

		return $record === false;
	}

	private function copyContacts(AddressBook $addressBook) {
		$db = GO()->getDbConnection();

		$contacts = $db->select()->from('ab_contacts')->where(['addressbook_id' => $addressBook->id]);

		foreach ($contacts as $r) {
			$contact = new Contact();
			$contact->id = $r['id'];
			$contact->addressBookId = $addressBook->id;
			$contact->firstName = $r['first_name'];
			$contact->middleName = $r['middle_name'];
			$contact->lastName = $r['last_name'];
			
			$contact->prefixes = $r['title'];
			$contact->suffixes = $r['suffix'];
			$contact->gender = $r['sex'];

			if (!empty($r['birthday'])) {
				$contact->dates[] = (new Date())
								->setValues([
						'type' => Date::TYPE_BIRTHDAY,
						'date' => DateTime::createFromFormat('Y-m-d', $record['birthday'])
				]);
			}

			if (!empty($r['action_date'])) {
				$contact->dates[] = (new Date())
								->setValues([
						'type' => "action",
						'date' => DateTime::createFromFormat('Y-m-d', $record['action_date'])
				]);
			}

			if (!empty($r['email'])) {
				$contact->emailAddresses[] = (new EmailAddress())
								->setValues([
						'type' => EmailAddress::TYPE_WORK,
						'email' => $r['email']
				]);
			}

			if (!empty($r['email2'])) {
				$contact->emailAddresses[] = (new EmailAddress())
								->setValues([
						'type' => EmailAddress::TYPE_WORK,
						'email' => $r['email']
				]);
			}
			if (!empty($r['email3'])) {
				$contact->emailAddresses[] = (new EmailAddress())
								->setValues([
						'type' => EmailAddress::TYPE_WORK,
						'email' => $r['email']
				]);
			}


			//$r['department'] ???

			$contact->jobTitle = $r['function'];

			if (!empty($r['home_phone'])) {
				$contact->phoneNumbers[] = (new PhoneNumber())
								->setValues([
						'type' => PhoneNumber::TYPE_HOME,
						'number' => $r['home_phone']
				]);
			}

			if (!empty($r['work_phone'])) {
				$contact->phoneNumbers[] = (new PhoneNumber())
								->setValues([
						'type' => PhoneNumber::TYPE_WORK,
						'number' => $r['work_phone']
				]);
			}

			if (!empty($r['fax'])) {
				$contact->phoneNumbers[] = (new PhoneNumber())
								->setValues([
						'type' => PhoneNumber::TYPE_FAX,
						'number' => $r['fax']
				]);
			}

			if (!empty($r['work_fax'])) {
				$contact->phoneNumbers[] = (new PhoneNumber())
								->setValues([
						'type' => PhoneNumber::TYPE_FAX,
						'number' => $r['work_fax']
				]);
			}

			if (!empty($r['cellular'])) {
				$contact->phoneNumbers[] = (new PhoneNumber())
								->setValues([
						'type' => PhoneNumber::TYPE_MOBILE,
						'number' => $r['cellular']
				]);
			}

			if (!empty($r['cellular2'])) {
				$contact->phoneNumbers[] = (new PhoneNumber())
								->setValues([
						'type' => PhoneNumber::TYPE_MOBILE,
						'number' => $r['cellular2']
				]);
			}

			if (!empty($r['homepage'])) {
				$contact->urls[] = (new Url())
								->setValues([
						'type' => Url::TYPE_HOMEPAGE,
						'url' => $r['homepage']
				]);
			}

			if (!empty($r['url_facebook'])) {
				$contact->urls[] = (new Url())
								->setValues([
						'type' => Url::TYPE_FACEBOOK,
						'url' => $r['url_facebook']
				]);
			}

			if (!empty($r['url_linkedin'])) {
				$contact->urls[] = (new Url())
								->setValues([
						'type' => Url::TYPE_LINKEDIN,
						'url' => $r['url_linkedin']
				]);
			}

			if (!empty($r['url_twitter'])) {
				$contact->urls[] = (new Url())
								->setValues([
						'type' => Url::TYPE_TWITTER,
						'url' => $r['url_twitter']
				]);
			}

			if (!empty($r['skype_name'])) {
				$contact->urls[] = (new Url())
								->setValues([
						'type' => "skype",
						'url' => $r['skype_name']
				]);
			}


			$address = new Address();
			$address->type = Address::TYPE_HOME;
			$address->country = $r['country'] ?? null;
			$address->state = $r['state'] ?? null;
			$address->city = $r['city'] ?? null;
			$address->zipCode = $r['zip'] ?? null;
			$address->street = $r['address'] ?? null;
			$address->street2 = $r['address_no'] ?? null;
			$address->latitude = $r['latitude'] ?? null;
			$address->longitude = $r['longitude'] ?? null;

			if ($address->isModified()) {
				$contact->addresses[] = $address;
			}

			$contact->notes = $r['comment'];

			$contact->filesFolderId = $r['files_folder_id'];

			$contact->createdAt = new DateTime("@" . $r['ctime']);
			$contact->modifiedAt = new DateTime("@" . $r['mtime']);
			$contact->createdBy = \go\modules\core\users\model\User::findById($r['user_id'], ['id']) ? $r['user_id'] : 1;
			$contact->modifiedBy = \go\modules\core\users\model\User::findById($r['muser_id'], ['id']) ? $r['muser_id'] : 1;
			$contact->goUserId = empty($r['go_user_id']) || !\go\modules\core\users\model\User::findById($r['go_user_id'], ['id']) ? null : $r['go_user_id'];
			
			if($r['photo']) {
				
				$file = GO()->getDataFolder()->getFile($r['photo']);
				if($file->exists()) {
					$tmpFile = \go\core\fs\File::tempFile($file->getExtension());					
					$file->copy($tmpFile);
					$blob =\go\core\fs\Blob::fromTmp($tmpFile);
					if(!$blob->save()) {
						throw new \Exception("Could not save blob");
					}
					
					$contact->photoBlobId = $blob->id;
				}
			}

			if (!$contact->save()) {
				throw new \Exception("Could not save contact");
			}
		}
	}
}
