<?php
namespace go\modules\community\bookmarks\model;
						
use go\core\jmap\Entity;
use \go\core\acl\model\AclOwnerEntity;
/**
 * Category model
 *
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Category extends \go\core\acl\model\AclOwnerEntity {
	
	/**
	 * 
	 * @var int
	 */							
	public $id;

	/**
	 * 
	 * @var int
	 */							
	public $createdBy;

	/**
	 * 
	 * @var int
	 */							
	public $aclId;

	/**
	 * 
	 * @var string
	 */							
	public $name;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("bookmarks_category", "category");
	}

	public static function getClientName() {
		return "BookmarksCategory";
	}

	protected function internalDelete() {
		if(!Bookmark::find()->where(['categoryId' => $this->id])->delete()) {
			return false;
		}

		return parent::internalDelete();
	}
}