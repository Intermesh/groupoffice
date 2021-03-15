<?php

namespace go\modules\community\addressbook\convert;

use Exception;
use go\core\data\convert;
use go\core\fs\File;
use go\core\model\Acl;
use go\core\orm\Entity;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\Group;

class Spreadsheet extends convert\Spreadsheet {

	private $organizations = true;

	protected function nextImportRecord()
	{
		$hasNext = parent::nextImportRecord();

		if(!$hasNext && $this->organizations) {
			//go to file again for contacts
			$this->organizations = false;

			if($this->extension == 'csv') {
				rewind($this->fp);
			}else{
				$this->spreadsheetRowIterator->rewind();
			}

			//read headers
			$this->readRecord();

			return parent::nextImportRecord();
		} else {
			return $hasNext;
		}
	}

	/**
	 * Override that skips contacts on the first run and imports them in the second
	 *
	 * @inheritDoc
	 */
	protected function importEntity()
	{
		$contact = parent::importEntity();

		if(!$contact) {
			return false;
		}

		return $contact->isOrganization == $this->organizations ? $contact : false;
	}

	protected function createEntity($values)
	{
		if(isset($this->clientParams['values'])) {
			$values = array_merge($values, $this->clientParams['values']);
		}

		$entityClass = $this->entityClass;

		$entity = false;
		//lookup entity by id if given
		if($this->updateBy == 'id' && !empty($values['id'])) {
			$entity = $entityClass::findById($values['id']);
			if($entity && $entity->getPermissionLevel() < Acl::LEVEL_WRITE) {
				$entity = false;
			}
		} elseif($this->updateBy == 'email') {
			$emails = [];
			if(!empty($values['emailAddresses'])) {
				foreach ($values['emailAddresses'] as $emailAddress) {
					if(!empty($emailAddress['email'])) {
						$emails[] = $emailAddress['email'];
					}
				}
			}

			if(!empty($emails)) {
				$entity = Contact::findByEmail($emails)->andWhere(['addressBookId' => $values['addressBookId']])->single();
			}
		}
		if(!$entity) {
			$entity = new $entityClass;
		}

		return $entity;
	}


	/**
	 * List headers to exclude
	 * @var string[]
	 */
	public static $excludeHeaders = ['addressBookId', 'goUserId', 'vcardBlobId', 'uri'];
	
	protected function init() {
		parent::init();
		$this->addColumn('isOrganization', go()->t("Is organization", "community", "addressbook"));
		$this->addColumn('organizations', go()->t("Organizations", "community", "addressbook"));
		$this->addColumn('gender', go()->t("Gender", "community", "addressbook"));
		$this->addColumn('groups', go()->t("Groups", "community", "addressbook"));
	}

	protected function importGroups(Contact $contact, $groups, array &$values) {

		$contact->groups = [];

		$groups = !empty($groups) ? explode(static::$multipleDelimiter, $groups) : [];
		$addressBookId = $contact->addressBookId ?? $this->clientParams['values']['addressBookId'];
		if(empty($addressBookId)) {
			throw new Exception("No address book ID set");
		}
		foreach($groups as $groupName) {
			$group = Group::find()->where(['name' => $groupName, 'addressBookId' => $addressBookId])->single();
			if(!$group) {
				$group = new Group();
				$group->name = $groupName;
				$group->addressBookId = $contact->addressBookId ?? $this->clientParams['values']['addressBookId'];
				if(!$group->save()) {
					throw new Exception("Could not save group");
				}
			}

			$contact->groups[] = $group->id;
		}
	}

	protected function exportGroups(Contact $contact) {
		$groupNames = [];
		foreach($contact->groups as $groupId) {
			$group = Group::findById($groupId);
			$groupNames[] = $group->name;
		}

		return implode(static::$multipleDelimiter, $groupNames);
	}

	protected function importIsOrganization(Contact $contact, $isOrganization, array &$values) {
		if(isset($isOrganization)) {
			//value is present in CSV file so just use it
			$contact->isOrganization = $isOrganization;
			return;
		}

		//A contact will be an organization if there's a name but no firstName or lastName
		$contact->isOrganization = empty($values['firstName']) && empty($values['lastName']);
		
		if($contact->isOrganization && empty($values['name']) && isset($values['organizations'][0])) {
			$contact->name = $values['organizations'][0];
			unset($values['organizations']);
		}
	}

	public function exportIsOrganization(Contact $contact) {
		return $contact->isOrganization;
	}

	public function exportGender(Contact $contact) {
		return $contact->gender;
	}

	public function importGender(Contact $contact, $gender) {
		switch(strtolower($gender)) {
			case 'm':
			case 'male':
				$contact->gender = 'M';
				return;

			case 'f':
			case 'female':
			case 'v':
				$contact->gender = 'F';
				return;
		}
	}
	
	protected function importOrganizations(Contact $contact, $organizationNames, array &$values) {

		$addressBookId = $contact->addressBookId ?? $this->clientParams['values']['addressBookId'];
		if(empty($addressBookId)) {
			throw new Exception("No address book ID set");
		}

		$organizationNames = !empty($organizationNames) ? explode(static::$multipleDelimiter, $organizationNames) : [];

		$orgIds = [];
		foreach($organizationNames as $name) {
			$org = Contact::find()->where(['name' => $name, 'isOrganization' => true])->single();
			if(!$org) {
				$org = new Contact();
				$org->name = $name;
				$org->isOrganization = true;
				$org->addressBookId = $addressBookId;
				if(!$org->save()) {
					throw new Exception("Could not create new organization '" . $name . "'");
				}
			}

			$orgIds[] = $org->id;
		}

		$contact->setOrganizationIds($orgIds);
	}
	
	protected function exportOrganizations(Contact $contact, $templateValues) {
		if($contact->isOrganization) {
			return "";
		}

		return implode(static::$multipleDelimiter, $contact->findOrganizations()->selectSingleValue('name')->all());
	}

}
