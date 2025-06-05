<?php

namespace go\modules\community\addressbook\model;

use go\core\db\DbException;
use go\core\model\User;
use go\core\orm\exception\SaveException;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\modules\community\addressbook\model\Settings as AddresBookModuleSettings;
use go\core\model;
use go\core\model\Acl;

class UserSettings extends Property {

	/**
	 * Primary key to User id
	 */
	public int $userId;
	
	/**
	 * Default address book ID
	 */
	protected ?int $defaultAddressBookId = null;


	/**
	 * @var string 'allcontacts', 'starred', 'default', 'remember'
	 */
	public string $startIn = "allcontacts";

	/**
	 * Last selected item
	 */
	public ?string $lastAddressBookId = null;


	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable("addressbook_user_settings", "abs");
	}

	/**
	 *
	 * @return ?int the default addressbook ID or null if module not available...
	 *
	 * @throws SaveException
	 * @throws DbException
	 * @throws \Exception
	 */
	public function getDefaultAddressBookId(): ?int
	{
		if (isset($this->defaultAddressBookId)) {
			return $this->defaultAddressBookId;
		}

		if (!model\Module::isAvailableFor('community', 'addressbook', $this->userId)) {
			return null;
		}

		if (AddresBookModuleSettings::get()->createPersonalAddressBooks) {
			$addressBook = AddressBook::find()->where('createdBy', '=', $this->userId)->single();
			if (!$addressBook) {
				$user = User::findById($this->userId, ['displayName', 'enabled']);
				if (!$user || !$user->enabled) {
					return null;
				}
				$addressBook = new AddressBook();
				$addressBook->createdBy = $this->userId;
				$addressBook->name = $user->displayName;
				if (!$addressBook->save()) {
					throw new SaveException($addressBook);
				}
			}
		} else {
			$addressBook = AddressBook::applyAclToQuery(
				AddressBook::find(['id']),
				Acl::LEVEL_WRITE,
				$this->userId)
				->single();
		}

		if ($addressBook) {
			$this->defaultAddressBookId = $addressBook->id;
			go()->getDbConnection()->update("addressbook_user_settings", ['defaultAddressBookId' => $this->defaultAddressBookId], ['userId' => $this->userId])->execute();
		}

		return $this->defaultAddressBookId;

	}

	public function setDefaultAddressBookId(?int $id): void
	{
		$this->defaultAddressBookId = $id;
	}



	public function getSortBy(): string
	{
		return isset($this->userId) ? (User::findById($this->userId, ['sort_name'])->sort_name == 'first_name' ? 'name' : 'lastName') : 'name';
	}

	
}
