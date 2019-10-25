<?php
namespace go\core\orm;

use go\core\App;
use go\core\db\Query;

/**
 * Entities can use this trait to make it show up in the global search function
 * 
 * @property array $customFields 
 */
trait SearchableTrait {

	/**
	 * The name for the search results
	 * 
	 * @return string
	 */
	abstract protected function getSearchName() ;
	
	/**
	 * The description in the search results
	 * 
	 * @return string
	 */
	abstract protected function getSearchDescription();
	
	/**
	 * All the keywords that can be searched on
	 * 
	 * @return string[]
	 */
	protected function getSearchKeywords() {
		return null;
	}
	
	/**
	 * You can return an optional search filter here.
	 * 
	 * @return string
	 */
	protected function getSearchFilter() {
		return null;
	}
	
	public function saveSearch($checkExisting = true) {
		$search = $checkExisting ? \go\core\model\Search::find()->where('entityTypeId','=', static::entityType()->getId())->andWhere('entityId', '=', $this->id)->single() : false;
		if(!$search) {
			$search = new \go\core\model\Search();
			$search->setEntity(static::entityType());
		}
		
		if(empty($this->id)) {
			throw new \Exception("ID is not set");
		}
		
		$search->entityId = $this->id;
		$search->setAclId($this->findAclId());
		$search->name = $this->getSearchName();
		$search->description = $this->getSearchDescription();
		$search->filter = $this->getSearchFilter();
		$search->modifiedAt = $this->modifiedAt;
		
//		$search->createdAt = $this->createdAt;
		
		$keywords = $this->getSearchKeywords();
		if(!isset($keywords)) {
			$keywords = [$search->name, $search->description];
		}
		
		if(method_exists($this, 'getCustomFields')) {
			foreach($this->getCustomFields() as $col => $v) {
				if(!empty($v) && is_string($v)) {
					$keywords[] = $v;
				}
			}
		}
		
		$keywords = array_unique($keywords);
		
		$search->setKeywords(implode(',', $keywords));		
		
		if(!$search->internalSave()) {
			throw new \Exception("Could not save search cache: " . var_export($search->getValidationErrors(), true));
		}
		
		return true;
	}
	
	public static function deleteSearchAndLinks(Query $query) {

		
		if(!\go()->getDbConnection()
						->delete('core_search', 
										['entityTypeId' => static::entityType()->getId(), 'entityId' => $query]
										)->execute()) {
			return false;
		}
		
		if(!\go()->getDbConnection()
						->delete('core_link', 
										['fromEntityTypeId' => static::entityType()->getId(), 'fromId' => $query]
										)->execute()) {
			return false;
		}
		
		if(!\go()->getDbConnection()
						->delete('core_link', 
										['toEntityTypeId' => static::entityType()->getId(), 'toId' => $query]
										)->execute()) {
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * 
	 * @param string $cls
	 * @return \go\core\db\Statement
	 */
	private static function queryMissingSearchCache($cls, $offset = 0) {
		
		$limit = 100;
			
		$query = $cls::find();
		/* @var $query \go\core\db\Query */
		$query->join("core_search", "search", "search.entityId = ".$query->getTableAlias() . ".id AND search.entityTypeId = " . $cls::entityType()->getId(), "LEFT");
		$query->andWhere('search.id IS NULL')
							->limit($limit)
							->offset($offset);
		
		return $query->execute();
	}
	
	private static function rebuildSearchForEntity($cls) {
		echo $cls."\n";
		

		echo "Deleting old values\n";

		$stmt = go()->getDbConnection()->delete('core_search', (new Query)
			->where('entityTypeId', '=', $cls::entityType()->getId())
			->andWhere('entityId', 'NOT IN', $cls::find()->selectSingleValue('id'))
		);
		$stmt->execute();

		echo "Deleted ". $stmt->rowCount() . " entries\n";

		//In small batches to keep memory low
		$stmt = self::queryMissingSearchCache($cls);			
		
		$offset = 0;
		
		//In small batches to keep memory low	
		while($stmt->rowCount()) {	

			while ($m = $stmt->fetch()) {

				try {
					flush();

					$m->saveSearch(false);
					echo ".";

				} catch (\Exception $e) {
					echo $e->getMessage();
					\go\core\ErrorHandler::logException($e);
					echo "E";
					$offset++;
				}
			}
			echo "\n";

			$stmt = self::queryMissingSearchCache($cls, $offset);
		}
	
	}
	
	public static function rebuildSearch() {
		$classFinder = new \go\core\util\ClassFinder();
		$entities = $classFinder->findByTrait(SearchableTrait::class);
		
		foreach($entities as $cls) {
			self::rebuildSearchForEntity($cls);			
			echo "\nDone\n\n";
		}
	}
}
