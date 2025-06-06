<?php
namespace go\modules\community\addressbook\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\db\Criteria;
use go\core\fs\File;
use go\core\model\Acl;
use go\core\model\Module as CoreModule;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\util\ArrayObject;
use go\modules\community\addressbook\Module;

/**
 * Address book model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class AddressBook extends AclOwnerEntity {

	public ?string $id;
	public string $name;
	public ?string $createdBy;
	public ?string $filesFolderId;
	public ?string $salutationTemplate;

	/**
	 * 
	 * @var int[]
	 */
	public array $groups;

	protected function init()
	{
		if(empty($this->salutationTemplate)) {
			$this->salutationTemplate = go()->t("salutationTemplate", "community", "addressbook");
		}

		parent::init();
	}
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable("addressbook_addressbook", "a")
						->addScalar('groups', 'addressbook_group', ['id' => 'addressBookId']);
	}

	protected function canCreate(): bool
	{
		return CoreModule::findByName('community', 'addressbook')->getUserRights()->mayChangeAddressbooks;
	}


	public function buildFilesPath(): string
	{
		Module::checkRootFolder();

		return "addressbook/" . File::stripInvalidChars($this->name);
	}

	protected static function filesPathProperties(): array
	{
		return ['name'];
	}

	protected static function textFilterColumns(): array
	{
		return ['name'];
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add("name", function(Criteria $criteria, $value) {
				$criteria->andWhere('name', 'LIKE', $value);
			});
	}

	// /**
	//  * Get the group ID's
	//  * 
	//  * @return int[]
	//  */
	// public function getGroups() {
	// 	return (new \go\core\db\Query)
	// 					->selectSingleValue('id')
	// 					->from("addressbook_group")
	// 					->where(['addressBookId' => $this->id])
	// 					->all();
						
	// }
	
	/**
	 * Find or create a default address book for the user
	 * 
	 * @param \go\core\model\User $user
	 * @return \go\modules\community\addressbook\model\AddressBook
	 * @throws \Exception
	 */
	public static function getDefault(\go\core\model\User|null $user = null) {
		
		if(!isset($user)) {
			$user = go()->getAuthState()->getUser(['addressBookSettings']);
		}
			
		if(!isset($user->addressBookSettings)) {
			$user->addressBookSettings = new \go\modules\community\addressbook\model\UserSettings($user);
		}
		
		if(!empty($user->addressBookSettings->defaultAddressBookId)) {
			return static::findById($user->addressBookSettings->defaultAddressBookId);
		}
		
		go()->getDbConnection()->beginTransaction();
		
		$addressBook = new \go\modules\community\addressbook\model\AddressBook();
		$addressBook->name = $user->displayName;
		if(!$addressBook->save()) {
			go()->getDbConnection()->rollBack();
			throw new \Exception("Could not create address book");
		}
		
		$user->addressBookSettings->defaultAddressBookId = $addressBook->id;
		if(!$user->save()) {
			go()->getDbConnection()->rollBack();
			throw new \Exception("Failed to save user");
		}		
		
		go()->getDbConnection()->commit();
		
		return $addressBook;
	}

	protected static function internalDelete(Query $query): bool
	{
		if(!Contact::delete(['addressBookId' => $query])) {
			return false;
		}
		
		if(!Group::delete(['addressBookId' => $query])) {
			return false;
		}
		
		return parent::internalDelete($query);
	}

	public static function sort(Query $query, ArrayObject $sort): Query
	{
		if(!count($sort)) {
			$sort['name'] = 'ASC';
		}
		return parent::sort($query, $sort); // TODO: Change the autogenerated stub
	}

}