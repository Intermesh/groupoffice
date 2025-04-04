<?php
namespace go\core\orm;

use DateTime;
use Exception;
use go\core\db\Criteria;
use go\core\db\Query as OrmQuery;
use go\core\db\Statement;
use go\core\ErrorHandler;
use go\core\model\Link;
use go\core\model\Module;
use go\core\model\Search;
use go\core\util\ClassFinder;
use go\core\util\StringUtil;
use go\modules\community\comments\model\Comment;
use function go;

trait SearchableTrait {

	public static $updateSearch = true;

	/**
	 * Function to determine if entities have search capabilities
	 *
	 * @example
	 * ```
	 * if(method_exists($entity, 'hasSearch')) {
	 *
	 *    //do something
	 * }
	 * ``
	 * @return bool
	 */
	public function hasSearch() : bool {
		return true;
	}
	
	/**
	 * The description in the search results
	 * 
	 * @return string
	 */
	abstract protected function getSearchDescription(): string;
	
	/**
	 * All the keywords that can be searched on.
	 *
	 * Note: All strings you return will  be optimized for searching using {@see StringUtil::splitTextKeywords()}.
	 *
	 * If you have numbers that should be searched on endings then use {@see StringUtil::numberToKeywords()}. For example
	 * an invoice ID "I2022-001234" should be found by searching on "1234" or with phone numbers.
	 * 
	 * @return string[]|null
	 */
	protected function getSearchKeywords(): ?array
	{
		return null;
	}
	
	/**
	 * You can return an optional search filter here.
	 * 
	 * @return string|null
	 */
	protected function getSearchFilter(): ?string
	{
		return null;
	}

	/**
	 * Override and return false if you don't want the entity to be indexed.
	 *
	 * @return bool
	 * @throws Exception
	 */
	protected function includeInSearch(): bool
	{
		return true;
	}


	/**
	 * Save entity to search cache
	 *
	 * @param bool $checkExisting If certain there's no existing record then this can be set to false
	 * @return bool
	 * @throws Exception
	 */
	public function saveSearch(bool $checkExisting = true): bool
	{

		if(!static::$updateSearch || !$this->isFetchedComplete()) {
			return true;
		}

		$search = $checkExisting ?
			Search::find()
				->where('entityTypeId','=', static::entityType()->getId())
				->andWhere('entityId', '=', $this->id)->single()
			: false;

		if(!$this->includeInSearch()) {
			if($search) {
				Search::delete($search->primaryKeyValues());
			}
			return true;
		}


		if(!$search) {
			$search = new Search();
			$search->setEntity(static::entityType());
		}
		
		if(empty($this->id)) {
			throw new Exception("ID is not set");
		}
		
		$search->entityId = $this->id;
		$search->setAclId($this->findAclId());
		$search->name = $this->title();
		$search->description = $this->getSearchDescription();
		$search->filter = $this->getSearchFilter();
		$search->modifiedAt = property_exists($this, 'modifiedAt') ? $this->modifiedAt : new DateTime();
		$search->rebuild = false;
//		$search->createdAt = $this->createdAt;
		
		$keywords = $this->getSearchKeywords();

		if(!isset($keywords)) {
			$keywords = [$search->name, $search->description];
		}

//		$keywords = $this->getCommentKeywords($keywords);

		$links = (new Query())
			->select('description')
			->distinct()
			->from('core_link')
			->where('(toEntityTypeId = :e1 AND toId = :e2)')
			//->orWhere('(fromEntityTypeId = :e3 AND fromId = :e4)')
			->bind([':e1' => static::entityType()->getId(), ':e2' => $this->id]);
				//':e3' => static::entityType()->getId(), ':e4' => $this->id ]);
		foreach($links->all() as $link) {
			if(!empty($link['description']) && is_string($link['description'])) {
				$keywords[] = $link['description'];
			}

		}

		if (method_exists($this, 'getCustomFields')) {
			$keywords = array_merge($keywords, $this->getCustomFieldsSearchKeywords());
		}

		$arr = [];
		foreach($keywords as $keyword) {
			$arr = array_merge($arr, StringUtil::splitTextKeywords($keyword));
		}

		$keywords = StringUtil::filterRedundantSearchWords($arr);

		if(!empty($this->id) && !in_array($this->id, $keywords)) {
			$keywords[] = $this->id;
		}

		//$search->setKeywords(implode(' ', $keywords));
		$isNew = $search->isNew();
		if(!$search->internalSave()) {
			throw new Exception("Could not save search cache: " . var_export($search->getValidationErrors(), true));
		}

		if(!$isNew) {
			go()->getDbConnection()->delete('core_search_word', ['searchId' => $search->id])->execute();
			$search->change(true);
		}

		if(empty($keywords)) {
			return true;
		}

		//array values to make sure index is sequential
		$keywords = array_values(array_map(function ($word) use ($search) {
			return ['searchId' => $search->id, 'word'=> $word];
		}, $keywords));

		return go()->getDbConnection()->insertIgnore(
			'core_search_word',$keywords
		)->execute();

	}

//	private function getCommentKeywords(array $keywords) : array {
//		if(Module::isInstalled("community", "comments")) {
//			$comments = Comment::findForEntity($this, ['text']);
//			foreach($comments as $comment) {
//				$plain = strip_tags($comment->text);
//				$keywords = array_merge($keywords, StringUtil::splitTextKeywords($plain));
//			}
//		}
//
//		return $keywords;
//	}


	/**
	 * @throws Exception
	 */
	public static function deleteSearchAndLinks(Query $query): bool
	{
	  //delete link before search because they depend on eachother.
		if(!Link::delete((new Query)
			->where(['fromEntityTypeId' => static::entityType()->getId()])
			->andWhere('fromId', 'IN', $query)
		)) {
			return false;
		}

		if(!Search::delete(
			(new Query)
				->where(['entityTypeId' => static::entityType()->getId()])
				->andWhere('entityId', 'IN', $query)
		)) {
			return false;
		}
		
		return true;
	}


	/**
	 *
	 * @param int $offset
	 * @return Statement
	 * @throws Exception
	 */
	private static function queryMissingSearchCache(int $offset = 0): Statement
	{
		
		$limit = 1000;


		$query = static::find();
		/* @var $query OrmQuery */
		$query
			->join("core_search", "search", "search.entityId = ".$query->getTableAlias() . ".id AND search.entityTypeId = " . static::entityType()->getId(), "LEFT")
			->andWhere('search.id IS NULL')
			->orWhere('search.rebuild = true')
			->limit($limit)
			->offset($offset);

		return $query->execute();
	}

	/**
	 * @throws Exception
	 */
	public static function rebuildSearchForEntity() {
		$cls = static::class;
		echo $cls."\n";

		if(ob_get_level() > 0) ob_flush();
		flush();


		echo "Deleting old values\n";

		$stmt = go()->getDbConnection()->delete('core_search', (new Query)
			->where('entityTypeId', '=', $cls::entityType()->getId())
			->andWhere('entityId', 'NOT IN', $cls::find()->selectSingleValue($cls::getMapping()->getPrimaryTable()->getAlias() . '.id'))
		);

		$stmt->execute();

		go()->getDbConnection()->exec("commit");

		echo "Deleted ". $stmt->rowCount() . " entries\n";

		//In small batches to keep memory low
		$stmt = static::queryMissingSearchCache();
		
		$offset = 0;
		
		//In small batches to keep memory low	
		while($stmt->rowCount()) {
			if(ob_get_level() > 0) ob_flush();
			flush();

			while ($m = $stmt->fetch()) {

				try {
					if(ob_get_level() > 0) ob_flush();
					flush();

					$m->saveSearch();
					echo ".";

				} catch (\Throwable $e) {
					echo "Error: " . $m->id() . ' '. $m->title() ." : " . $e->getMessage() ."\n";
					ErrorHandler::logException($e);

					$offset++;
				}
			}
			echo "\n";
			go()->getDbConnection()->exec("commit");

			$stmt = static::queryMissingSearchCache($offset);
		}


		go()->getDbConnection()->exec("commit");


	}

}
