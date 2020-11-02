<?php
namespace go\core\orm;

use go\core\App;
use go\core\customfield\Html;
use go\core\db\Query;
use go\core\model\Link;

/**
 * Entities can use this trait to make it show up in the global search function
 * 
 * @property array $customFields 
 */
trait SearchableTrait {
	
	/**
	 * The description in the search results
	 * 
	 * @return string
	 */
	abstract protected function getSearchDescription();
	
	/**
	 * All the keywords that can be searched on.
	 *
	 * Note: for larger text fields it might be useful to use {@see self::splitTextKeywords()} on it.
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

	/**
	 * Split text by non word characters to get useful search keywords.
	 * @param $text
	 * @return array|false|string[]
	 */
	private static function splitTextKeywords($text) {
		mb_internal_encoding("UTF-8");
		mb_regex_encoding("UTF-8");
//		$split = preg_split('/[^\w\-_\+\\\\\/:]/', mb_strtolower($text), 0, PREG_SPLIT_NO_EMPTY);
		return mb_split('[^\w\-_\+\\\\\/:]', mb_strtolower($text), -1);
	}

	/**
	 * Save entity to search cache
	 *
	 * @param bool $checkExisting If certain there's no existing record then this can be set to false
	 * @return bool
	 * @throws \Exception
	 */
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
		$search->name = $this->title();
		$search->description = $this->getSearchDescription();
		$search->filter = $this->getSearchFilter();
		$search->modifiedAt = $this->modifiedAt;
		
//		$search->createdAt = $this->createdAt;
		
		$keywords = $this->getSearchKeywords();
		if(!isset($keywords)) {
			$keywords = [$search->name, $search->description];
		}

		$links = (new Query())
			->select('description')
			->from('core_link')
			->where('(toEntityTypeId = :e1 AND toId = :e2)')
			->orWhere('(fromEntityTypeId = :e3 AND fromId = :e4)')
			->bind([':e1' => static::entityType()->getId(), ':e2' => $this->id, ':e3' => static::entityType()->getId(), ':e4' => $this->id ]);
		foreach($links->all() as $link) {
			if(!empty($link['description']) && is_string($link['description'])) {
				$keywords[] = $link['description'];
			}

		}
		
		if(method_exists($this, 'getCustomFields')) {

			$cfData = $this->getCustomFields(true);

			foreach(static::getCustomFieldModels() as $field) {

				if($field->getDataType() instanceof Html) {
					continue;
				}

				$v = $cfData[$field->databaseName];

				if(is_array($v)) {
					foreach($v as $i) {
						if(!empty($v) && is_string($v)) {
							$keywords[] = $v;
						}
					}
				} else if(!empty($v) && is_string($v)) {
					$keywords[] = $v;
				}
			}
		}

		$sanitized = [];
		foreach($keywords as $keyword) {
			$sanitized = array_merge($sanitized, self::splitTextKeywords($keyword));
		}

		$sanitized = array_unique($sanitized);
		
		$search->setKeywords(implode(' ', $sanitized));
		
		if(!$search->internalSave()) {
			throw new \Exception("Could not save search cache: " . var_export($search->getValidationErrors(), true));
		}
		
		return true;
	}
	
	public static function deleteSearchAndLinks(Query $query) {
		$delSearchStmt = \go()->getDbConnection()
			->delete('core_search',
				(new Query)
					->where(['entityTypeId' => static::entityType()->getId()])
					->andWhere('entityId', 'IN', $query)
			);
//		$s = (string) $delSearchStmt;

		if(!$delSearchStmt->execute()) {
			return false;
		}
		
		if(!\go()->getDbConnection()
						->delete('core_link', 
										(new Query)
											->where(['fromEntityTypeId' => static::entityType()->getId()])
											->andWhere('fromId', 'IN', $query)
										)->execute()) {
			return false;
		}
		
		if(!\go()->getDbConnection()
						->delete('core_link', 										
										(new Query)
											->where(['toEntityTypeId' => static::entityType()->getId()])
											->andWhere('toId', 'IN', $query)
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
			->andWhere('entityId', 'NOT IN', $cls::find()->selectSingleValue($cls::getMapping()->getPrimaryTable()->getAlias() . '.id'))
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
