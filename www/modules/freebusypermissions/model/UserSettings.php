<?php
namespace GO\Freebusypermissions\Model;

use go\core\orm\Property;
use function GO;


/**
 * Temporary workaround for saving old settings form a user property;
 * @todo replace with behaviours
 */
class UserSettings extends Property {
	
	public $id;
	public $fbAclId;
	
	protected static function defineMapping() {
		return parent::defineMapping()->addTable('core_user');
	}
	
	protected function init() {
		parent::init();

		$fbAcl = FreeBusyAcl::model()->findSingleByAttribute('user_id', $this->id);
		
		if(!$fbAcl){
			
			$acl = new \GO\Base\Model\Acl();
			$acl->ownedBy = $this->id;
			$acl->usedIn = FreeBusyAcl::model()->tableName();
			$acl->save();
		
			$fbAcl = new FreeBusyAcl();
			$fbAcl->user_id = $this->id;
			$fbAcl->acl_id = $acl->id;
			$fbAcl->save();
		}
		$this->fbAclId = $fbAcl->acl_id;
	
	}

}
