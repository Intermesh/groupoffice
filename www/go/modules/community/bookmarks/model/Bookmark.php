<?php
namespace go\modules\community\bookmarks\model;

use go\core\acl\model\AclItemEntity;
use go\core\db\Criteria;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\util\ArrayObject;
use go\modules\community\bookmarks\controller\Bookmark as GoBookmark;

/**
 * Bookmark model
 *
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Bookmark extends AclItemEntity {

	public ?string $id;
	public string $categoryId;
	public ?string $createdBy;
	public string $name;
	public string $content;
	public ?string $description;
	public ?string $logo;
	public bool $openExtern = true;
	public bool $behaveAsModule = false;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable("bookmarks_bookmark", "bookmarks");
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()->add('categoryId', function(Criteria $criteria, $value, Query $query, array $filter){
			$criteria->andWhere('categoryId', '=', $value);
		})->add('behaveAsModule', function(
			Criteria $criteria, $value
		){
			$criteria->andWhere('behaveAsModule', '=', !empty($value));
		});
	}

	protected static function textFilterColumns(): array
	{
		return ['name', 'description'];
	}

	public function loadMetaData() {
		$c = new GoBookmark();
		$response = $c->description(['url' => $this->content]);

		if(!empty($response['title'])) {
			$this->description = $response['description'];
		}

		if(!empty($response['logo'])) {
			$this->logo = $response['logo'];
		}
	}

	protected static function aclEntityClass(): string
	{
		return Category::class;
	}

	/**
	 * Get the keys for joining the aclEntityClass table.
	 * 
	 * @return array eg. ['folderId' => 'id']
	 */
	protected static function aclEntityKeys(): array
	{
		return ['categoryId' => 'id'];
	}

	public static function sort(Query $query, ArrayObject $sort): Query
	{
		if(isset($sort['category'])) {
			$query->join("bookmarks_category", 'category', 'category.id = bookmarks.categoryId', 'INNER');
			$sort->renameKey('category', 'category.name');
		}
		return parent::sort($query, $sort);
	}
}