<?php
namespace go\modules\community\bookmarks\model;

use go\core\acl\model\AclItemEntity;
use go\core\db\Criteria;
use go\core\orm\Query;
use go\modules\community\bookmarks\controller\Bookmark as GoBookmark;

/**
 * Bookmark model
 *
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Bookmark extends AclItemEntity {
	
	/**
	 * 
	 * @var int
	 */							
	public $id;

	/**
	 * 
	 * @var int
	 */							
	public $categoryId;

	/**
	 * 
	 * @var int
	 */							
	public $createdBy;

	/**
	 * 
	 * @var string
	 */							
	public $name;

	/**
	 * 
	 * @var string
	 */							
	public $content;

	/**
	 * 
	 * @var string
	 */							
	public $description;

	/**
	 * 
	 * @var string
	 */							
	public $logo;

	/**
	 * 
	 * @var bool
	 */							
	public $openExtern = true;

	/**
	 * 
	 * @var bool
	 */							
	public $behaveAsModule = false;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("bookmarks_bookmark", "bookmarks");
	}

	protected static function defineFilters() {
		return parent::defineFilters()->add('categoryId', function(Criteria $criteria, $value, Query $query, array $filter){
			$criteria->andWhere('categoryId', '=', $value);
		})->add('behaveAsModule', function(
			Criteria $criteria, $value
		){
			$criteria->andWhere('behaveAsModule', '=', !empty($value));
		});
	}

	protected static function textFilterColumns() {
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

	protected static function aclEntityClass() {
		return Category::class;
	}

	/**
	 * Get the keys for joining the aclEntityClass table.
	 * 
	 * @return array eg. ['folderId' => 'id']
	 */
	protected static function aclEntityKeys() {
		return ['categoryId' => 'id'];
	}
}