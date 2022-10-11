<?php
namespace go\modules\community\calendar;

use go\core;

class Module extends core\Module
{

	public function getAuthor(): string
	{
		return "Intermesh BV <mdhart@intermesh.nl>";
	}

	public static function getTitle(): string
	{
		return 'Calendar GOUI';
	}

	protected function rights(): array
	{
		return [
			'mayChangeAddressbooks', // allows AddressBook/set (hide ui elements that use this)
			'mayExportContacts', // Allows users to export contacts
		];
	}
}