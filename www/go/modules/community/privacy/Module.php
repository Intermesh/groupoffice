<?php
namespace go\modules\community\privacy;
							
use Exception;
use go\core;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\orm\Mapping;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\privacy;

/**						
 * @copyright (c) 2023, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
final class Module extends core\Module
{

	public function getAuthor(): string
	{
		return "Intermesh BV <info@intermesh.nl>";
	}

	public function getDependencies(): array
	{
		return ['community/addressbook'];
	}


	/**
	 * @throws Exception
	 */
	protected function afterInstall(core\model\Module $model): bool
	{
		cron\TrashContacts::install("45 0 * * *");

		// Create a 'Trash' address book for only admins
		$trashAB = AddressBook::find()->where(['name' => go()->t('Trash')])->single();
		if(!$trashAB) {
			$trashAB = new AddressBook();
			$trashAB->setValues(['name' => go()->t('Trash')]);
			$trashAB->setAcl([
				Group::ID_ADMINS => Acl::LEVEL_MANAGE
			]);
			$trashAB->save();
		}

		// Create an 'Incoming' address book for new contacts
		$incomingAB = AddressBook::find()->where(['name' => go()->t('Incoming')])->single();
		if(!$incomingAB) {
			$incomingAB = new AddressBook();
			$incomingAB->setValues(['name' => go()->t('Incoming')]);
			$incomingAB->setAcl([
				Group::ID_INTERNAL => Acl::LEVEL_DELETE
			]);
			$incomingAB->save();
		}

		// Create default settings in core_setting
		$settings = $this->getSettings();//new privacy\model\Settings();
		$settings->setValues([
            'warnXDaysBeforeDeletion' => 7,
			'monitorAddressBooks' => $incomingAB->id,
			'trashAddressBook' => $trashAB->id,
            'trashAfterXDays' => 42
		]);
		$settings->save();
		return parent::afterInstall($model);

	}

	/**
	 * The development status of this module
	 * @return string
	 */
	public function getStatus(): string
	{
		return self::STATUS_STABLE;
	}


	/**
	 * When this version is installed, contacts are to be extended
	 *
	 * @return void
	 */
	public function defineListeners()
	{
		parent::defineListeners();

		Contact::on(core\orm\Property::EVENT_MAPPING, static::class, 'onContactMap');
		Contact::on(core\orm\Entity::EVENT_BEFORE_SAVE, static::class, 'onContactBeforeSave');
	}

	public function getSettings()
	{
		return privacy\model\Settings::get();
	}

	/**
	 *
	 * @param Mapping $mapping
	 * @return void
	 * @throws Exception
	 */
	public static function onContactMap(Mapping $mapping)
	{
		$mapping->addHasOne('deletionDate', privacy\model\ContactDeletion::class, ['id' => 'contactId']);
	}

	/**
	 * Before a new contact is saved, move it to the "Incoming" address book.
	 *
	 * @param Contact $contact
	 * @return bool
	 * @throws Exception
	 */
	public static function onContactBeforeSave(Contact $contact): bool
	{
		if ($contact->isNew() && !isset($contact->addressBookId)) {
			$settings = privacy\model\Settings::get();
			$arABs = explode(',', $settings->monitorAddressBooks );
			if (!count($arABs)) {
				throw new Exception("Privacy settings incomplete");
			}

			$incomingAB = AddressBook::find()->where(['name' => go()->t('Incoming')])->single();
			if (!$incomingAB || !in_array($incomingAB->id, $arABs)) {
				$tgtAddressBookId = $arABs[0];
			} else {
				$tgtAddressBookId = $incomingAB->id;
			}

			$contact->addressBookId = $tgtAddressBookId;
		}
		return true;
	}
}