<?php

namespace go\modules\community\addressbook\convert;

use GO;
use go\core\data\convert;
use go\modules\community\addressbook\model\Contact;

class CSV extends convert\CSV {	

	
	/**
	 * List headers to exclude
	 * @var string[]
	 */
	public static $excludeHeaders = ['addressBookId', 'goUserId', 'vcardBlobId', 'uri'];
	
	protected function init() {
		$this->addColumn('organizations', GO()->t("Organizations", "community", "addressbook"), true);
	}
	
	protected function importOrganizations(Contact $contact, array $values) {
	
	}
	
	protected function exportOrganizations(Contact $contact) {
		if($contact->isOrganization) {
			return "";
		}

		return implode($this->multipleDelimiter, $contact->findOrganizations()->selectSingleValue('name')->all());
	}
}
