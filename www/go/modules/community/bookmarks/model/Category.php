<?php
namespace go\modules\community\bookmarks\model;
use \go\core\acl\model\AclOwnerEntity;
use go\core\orm\Mapping;
use go\core\orm\Query;

/**
 * Category model
 *
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Category extends AclOwnerEntity {

	public ?string $id;

	public ?string $createdBy;

	public string $name;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable("bookmarks_category", "category");
	}

	public static function getClientName(): string
	{
		return "BookmarksCategory";
	}

	protected static function internalDelete(Query $query): bool
	{
		if(!Bookmark::delete(['categoryId' => $query])) {
			return false;
		}

		return parent::internalDelete($query);
	}
}