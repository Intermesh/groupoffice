<?php

namespace go\modules\community\addressbook\convert;

use GO;
use go\core\data\convert;
use go\modules\community\addressbook\model\Contact;

class Csv extends convert\Csv {	

	private $organizations = true;

	/**
	 * Override that makes the import run twice. The first time import only organizations so that in the second run organizations can be matched with contacts.
	 */
	public function importFile(\go\core\fs\File $file, $entityClass, $params = array())
	{
		$contacts = parent::importFile($file, $entityClass, $params);
		if(!$contacts['success']) {
			return false;
		}

		$this->organizations = false;

		$orgs = parent::importFile($file, $entityClass, $params);

		return [
			'count' => ($contacts['count'] + $orgs['count']), 
			'errors' => array_merge($contacts['errors'], $orgs['errors']), 
			'success' => ($orgs['success'] && $contacts['success'])
		];
	}

	/**
	 * Override that skips contacts on the first run and imports them in the second
	 */
	protected function importEntity(\go\core\orm\Entity $entity, $fp, $index, array $params)
	{
		$contact = parent::importEntity($entity, $fp, $index, $params);

		if(!$contact) {
			return false;
		}

		return $contact->isOrganization == $this->organizations ? $contact : false;
	}

	
	/**
	 * List headers to exclude
	 * @var string[]
	 */
	public static $excludeHeaders = ['addressBookId', 'goUserId', 'vcardBlobId', 'uri'];
	
	protected function init() {
		parent::init();
		$this->addColumn('isOrganization', go()->t("Is organization", "community", "addressbook"), false);
		$this->addColumn('organizations', go()->t("Organizations", "community", "addressbook"), true);		
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
	
	protected function importOrganizations(Contact $contact, $organizationNames) {
		if(!isset($organizationNames)) {
			return;
		}
		//todo how to handle if org is not imported yet?
		$orgIds = [];
		foreach($organizationNames as $name) {
			$org = Contact::find()->where(['name' => $name, 'isOrganization' => true])->single();
			if(!$org) {
				$org = new Contact();
				$org->name = $name;
				$org->isOrganization = true;
				$org->addressBookId = $contact->addressBookId;
				if(!$org->save()) {
					throw new \Exception("Could not create new organization '" . $name . "'");
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

		return implode($this->multipleDelimiter, array_column($templateValues['organizations'], 'name'));
	}
}
