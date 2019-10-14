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
	 * @return string
	 */
	protected function getSearchKeywords() {
		return null;
	}
	
	public function saveSearch($checkExisting = true) {
		$search = $checkExisting ? \go\modules\core\search\model\Search::find()->where('entityTypeId','=', static::getType()->getId())->andWhere('entityId', '=', $this->id)->single() : false;
		if(!$search) {
			$search = new \go\modules\core\search\model\Search();
			$search->setEntity(static::getType());
		}
		$search->entityId = $this->id;
		$search->setAclId($this->findAclId());
		$search->name = $this->getSearchName();
		$search->description = $this->getSearchDescription();
		$search->modifiedAt = $this->modifiedAt;
//		$search->createdAt = $this->createdAt;
		
		$keywords = $this->getSearchKeywords();
		if(!isset($keywords)) {
			$keywords = $search->name.', '.$search->description;
		}
		$search->setKeywords($keywords);
		
		if(!$search->internalSave()) {
			throw new \Exception("Could not save search cache: " . var_export($search->getValidationErrors(), true));
		}
		
		return true;
	}
	
	public function deleteSearchAndLinks() {
		if(!\GO()->getDbConnection()
						->delete('core_search', 
										['entityTypeId' => static::getType()->getId(), 'entityId' => $this->id]
										)->execute()) {
			return false;
		}
		
		if(!\GO()->getDbConnection()
						->delete('core_link', 
										['fromEntityTypeId' => static::getType()->getId(), 'fromId' => $this->id]
										)->execute()) {
			return false;
		}
		
		if(!\GO()->getDbConnection()
						->delete('core_link', 
										['toEntityTypeId' => static::getType()->getId(), 'toId' => $this->id]
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
		$query->join("core_search", "search", "search.entityId = ".$query->getTableAlias() . ".id AND search.entityTypeId = " . $cls::getType()->getId(), "LEFT");
		$query->andWhere('search.id IS NULL')
							->limit($limit)
							->offset($offset);
		
		return $query->execute();
	}
	
	private static function rebuildSearchForEntity($cls) {
		echo $cls."\n";
		
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
