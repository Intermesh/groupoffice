<?php
namespace GO\Freebusypermissions\Model;

use go\core\orm\Mapping;
use go\core\orm\Property;
use function GO;


class UserSettings extends Property {
	
	public $user_id;
	public $acl_id;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('fb_acl');
	}

	protected function init()
	{
		if(!isset($this->acl_id) && isset($this->owner) && isset($this->owner->id)) {

			$this->user_id = $this->owner->id;

			$this->createAcl();

			go()->getDbConnection()
				->insert('fb_acl', [
					'user_id' => $this->owner->id,
					'acl_id' => $this->acl_id
				])->execute();
		}
	}

	private function createAcl() {
		$acl = new \GO\Base\Model\Acl();
		$acl->ownedBy = $this->user_id;
		$acl->usedIn = FreeBusyAcl::model()->tableName();
		$acl->save();

		$this->acl_id = $acl->id;
	}

	protected function internalSave() : bool
	{
		if(!isset($this->acl_id)) {
			$this->createAcl();
		}

		return parent::internalSave();
	}

}
