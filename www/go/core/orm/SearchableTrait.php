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
	
	
	public static function rebuildSearch() {
		$classFinder = new \go\core\util\ClassFinder();
		$entities = $classFinder->findByTrait(SearchableTrait::class);
		
		foreach($entities as $cls) {
			echo $cls."\n";
			$query = $cls::find();
			$query->join("core_search", "search", "search.entityId = ".$query->getTableAlias() . ".id AND search.entityTypeId = " . $cls::getType()->getId(), "LEFT");
			$query->andWhere('search.id IS NULL');
			
			foreach($query as $e) {				
				try {
					$e->saveSearch(false);
					echo ".";
				} catch(\Exception $e) {
					\go\core\ErrorHandler::logException($e);
					echo "E";
				}
			}
			
			echo "\nDone\n\n";
		}
	}
}
