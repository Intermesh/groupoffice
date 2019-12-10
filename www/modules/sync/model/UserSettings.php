<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

namespace GO\Sync\Model;

use go\core\model\Module;
use go\core\orm\Property;
use GO\Base\Model\User as GOUser;
use go\core\model\User;

/**
 * The Settings model
 *
 * @package GO.modules.Tasks
 * @version $Id$
 * @copyright Copyright Intermesh BV.
 * @author Michael de Hart mdhart@intermesh.nl
 *
 * @property int $user_id
 */
class UserSettings extends Property
{

	public $user_id;
	/**
	 * Email account
	 * @var int
	 */
	public $account_id;

	public $noteBooks = [];

	public $addressBooks = [];

	protected static function defineMapping()
	{
		return parent::defineMapping()
			->addTable("sync_settings", "syncs")
			->addArray('noteBooks', UserNoteBook::class, ['user_id' => 'userId'])
			->addArray('addressBooks', UserAddressBook::class, ['user_id' => 'userId']);
	}


	protected function setup() {

	  if(empty($this->account_id)) {
      if (Module::isInstalled('legacy', 'email')) {
        $account = \GO\Email\Model\Account::model()->findSingleByAttribute('user_id', $this->user_id);
        if ($account) {
          $this->account_id = $account->id;
        }
      }
	  }

		if (empty($this->addressBooks) || empty($this->noteBooks)) {
			$user = User::findById($this->user_id, ['addressBookSettings', 'notesSettings']);

			if (empty($this->addressBooks)) {
				if (isset($user->addressBookSettings) && ($addressBookId = $user->addressBookSettings->getDefaultAddressBookId())) {
					$this->addressBooks[] = (new UserAddressBook())->setValues(['addressBookId' => $addressBookId, 'isDefault' => true]);
				}
			}

			if (empty($this->noteBooks)) {
				if (isset($user->notesSettings) && ($noteBookId = $user->notesSettings->getDefaultNoteBookId())) {
					$this->noteBooks[] = (new UserNoteBook())->setValues(['noteBookId' => $noteBookId, 'isDefault' => true]);
				}
			}

			if ($user->isModified()) {
				$user->save();
			}
		}
	}

	public function toArray($properties = [])
	{
		$this->setup();

		return parent::toArray($properties);
	}
}
