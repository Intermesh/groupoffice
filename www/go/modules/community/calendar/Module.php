<?php
namespace go\modules\community\calendar;

use go\core;

class Module extends core\Module
{

	public function getAuthor(): string
	{
		return "Intermesh BV <info@intermesh.nl>";
	}

	protected function rights(): array
	{
		return [
			'mayChangeAddressbooks', // allows AddressBook/set (hide ui elements that use this)
			'mayExportContacts', // Allows users to export contacts
		];
	}
}