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
use go\core\orm\Mapping;
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

	private $doSetup = false;

	protected function init(): void
	{
		if($this->isNew()) {
			$this->doSetup = true;
		}
	}

	public function __clone()
	{
		// when modifications are tracked this object is cloned. We don't want it to
		// run setup twice. When this object is json serialized by the history module
		$this->doSetup = false;
	}



	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("sync_settings", "syncs");
	}


	protected function setup()
	{
		if (empty($this->account_id)) {
			if (Module::isInstalled('legacy', 'email')) {
				$account = \GO\Email\Model\Account::model()->findSingleByAttribute('user_id', $this->user_id);
				if ($account) {
					$this->account_id = $account->id;
				}
			}
		}
	}

	public function toArray(?array $properties = null): array
	{
		if($this->doSetup) {
			$this->setup();
		}

		return parent::toArray($properties);
	}
}
