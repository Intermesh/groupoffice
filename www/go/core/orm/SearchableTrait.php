<?php
namespace go\core\orm;

use go\core\db\Criteria;
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
	abstract public function getSearchDescription();
	
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
	 * @return string[]
	 */
	public static function splitTextKeywords($text) {

		if(empty($text)) {
			return [];
		}

		//Split on non word chars followed by whitespace or end of string. This wat initials like J.K. or french dates
		//01.01.2020 can be found too.
//		$keywords = mb_split('[^\w\-_\+\\\\\/:](\s|$)*', mb_strtolower($text), -1);
		$text= preg_replace('/[^\w\-_\+\\\\\/\s:@]/', '', mb_strtolower($text));
		$keywords = mb_split("\s+", $text);

		//filter small words
		$keywords = array_filter($keywords, function($word) {
			return strlen($word) > 1;
		});


		return $keywords;
	}

	public static function addCriteria(Criteria $criteria, Query $query, $searchPhrase) {
		$i = 0;
		$words = SearchableTrait::splitTextKeywords($searchPhrase);
		$words = array_unique($words);

		foreach($words as $word) {
			$query->join("core_search_word", 'w'.$i, 'w'.$i.'.searchId = search.id');
			//$query->join("core_search_word_reverse", 'wr'.$i, 'wr'.$i.'.searchId = s.id');

			$c = new Criteria();
			$c
				->where('w'.$i.'.word', 'LIKE', $word . '%')
				->orWhere('w'.$i.'.drow', 'LIKE', strrev($word) . '%');

			$criteria->where($c);

			$i++;
		}
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
		$search->modifiedAt = property_exists($this, 'modifiedAt') ? $this->modifiedAt : new \DateTime();
		
//		$search->createdAt = $this->createdAt;
		
		$keywords = $this->getSearchKeywords();
		if(!isset($keywords)) {
			$keywords = array_merge(self::splitTextKeywords($search->name), self::splitTextKeywords($search->description));
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

		if (method_exists($this, 'getCustomFields')) {
			$keywords = array_merge($keywords, $this->getCustomFieldsSearchKeywords());
		}

		$arr = [];
		foreach($keywords as $keyword) {
			$arr = array_merge($arr, self::splitTextKeywords($keyword));
		}

		$keywords = array_unique($arr);

		//$search->setKeywords(implode(' ', $keywords));
		$isNew = $search->isNew();
		if(!$search->internalSave()) {
			throw new \Exception("Could not save search cache: " . var_export($search->getValidationErrors(), true));
		}

		if(!$isNew) {
			go()->getDbConnection()->delete('core_search_word', ['searchId' => $search->id])->execute();
		}

		if(empty($keywords)) {
			return true;
		}

		//array values to make sure index is sequential
		$keywords = array_values(array_map(function ($word) use ($search) {
			return ['searchId' => $search->id, 'word'=> $word, 'drow' => strrev($word)];
		}, $keywords));

		return go()->getDbConnection()->insertIgnore(
			'core_search_word',$keywords
		)->execute();

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

		if(!Link::delete((new Query)
			->where(['fromEntityTypeId' => static::entityType()->getId()])
			->andWhere('fromId', 'IN', $query)
		)) {
			return false;
		}

		if(!Link::delete((new Query)
			->where(['toEntityTypeId' => static::entityType()->getId()])
			->andWhere('toId', 'IN', $query)
		)) {
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
					echo "Error: " . $m->id() . ' '. $m->title() ." : " . $e->getMessage() ."\n";
					\go\core\ErrorHandler::logException($e);

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
