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
		
		if(!parent::importFile($file, $entityClass, $params)) {
			return false;
		}

		$this->organizations = false;

		return parent::importFile($file, $entityClass, $params);
	}

	/**
	 * Override that skips contacts on the first run and imports them in the second
	 */
	protected function importEntity(\go\core\orm\Entity $entity, $fp, $index, array $params)
	{
		$contact = parent::importEntity($entity, $fp, $index, $params);

		return $contact->isOrganization == $this->organizations ? $contact : false;
	}

	
	/**
	 * List headers to exclude
	 * @var string[]
	 */
	public static $excludeHeaders = ['addressBookId', 'goUserId', 'vcardBlobId', 'uri'];
	
	protected function init() {
		$this->addColumn('organizations', GO()->t("Organizations", "community", "addressbook"), true);
	}
	
	protected function importOrganizations(Contact $contact, array $values) {
		//todo how to handle if org is not imported yet?
		$orgIds = [];
		foreach($values as $name) {
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
	
	protected function exportOrganizations(Contact $contact) {
		if($contact->isOrganization) {
			return "";
		}

		return implode($this->multipleDelimiter, $contact->findOrganizations()->selectSingleValue('name')->all());
	}
}
