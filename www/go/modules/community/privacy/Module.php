<?php
namespace go\modules\community\privacy;
							
use Exception;
use go\core;
use go\core\model\Acl;
use go\core\model\Group;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\privacy;

/**						
 * @copyright (c) 2023, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Module extends core\Module
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
			$trashAB->setAcl([
				Group::ID_INTERNAL => Acl::LEVEL_DELETE
			]);
			$incomingAB->save();
		}

		// Create default settings in core_setting
		$settings = new privacy\model\Settings();
		$settings->setValues([
            'warnXDaysBeforeDeletion' => 7,
			'monitorAddressBooks' => $incomingAB->id,
			'trashAddressBook' => $trashAB->id,
            'trashAfterXMonths' => 6
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
		// TODO: Contacts!
	}

	public function getSettings()
	{
		return privacy\model\Settings::get();
	}

	public function setSettings($value) {
		$v = $value['monitorAddressBooks'] ?? [];
		unset($value['monitorAddressBooks']);
		$value['monitorAddressBooks'] = implode(',', $v);
		$this->getSettings()->setValues($value);
		$this->change(true);
	}

}