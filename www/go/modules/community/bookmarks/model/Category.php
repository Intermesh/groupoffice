<?php
namespace go\modules\community\bookmarks\model;
use \go\core\acl\model\AclOwnerEntity;
use go\core\orm\Query;

/**
 * Category model
 *
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Category extends AclOwnerEntity {
	
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

	protected static function internalDelete(Query $query) {
		if(!Bookmark::delete(['categoryId' => $query])) {
			return false;
		}

		return parent::internalDelete($query);
	}
}