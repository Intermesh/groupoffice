<?php
namespace GO\Freebusypermissions\Model;

use go\core\orm\Mapping;
use go\core\orm\Property;
use function GO;


/**
 * Temporary workaround for saving old settings form a user property;
 * @todo replace with behaviours
 */
class UserSettings extends Property {
	
	public $user_id;
	public $acl_id;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('fb_acl');
	}	
	
	protected function init()
	{
		parent::init();
		
		if(!isset($this->acl_id)) {
			$acl = new \GO\Base\Model\Acl();
			$acl->ownedBy = $this->user_id;
			$acl->usedIn = FreeBusyAcl::model()->tableName();
			$acl->save();

			$this->acl_id = $acl->id;
			if($this->owner){
				$this->user_id = $this->owner->id;
			}

			go()->getDbConnection()
				->insert('fb_acl', [
					'user_id' => $this->user_id,
					'acl_id' => $acl->id
				])->execute();

		}
	}

}
