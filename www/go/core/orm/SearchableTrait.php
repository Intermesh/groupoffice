<?php
namespace go\core\orm;

use go\core\db\Criteria;
use go\core\model\Link;
use go\core\model\Search;

trait SearchableTrait {

	public static $updateSearch = true;
	
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
	 *
	 * @param ?string $text
	 * @param bool $forSave Split differently on save then when searching
	 *
	 * Case "Jansen-Pietersen"
	 *
	 * For save store:
	 * - Jansen-Pietersen
	 * - Jansen
	 * - Pietersen
	 *
	 * On search do:
	 * - Jansen-Pietersen"%
	 *
	 * @return string[]
	 */
	public static function splitTextKeywords(?string $text, bool $forSave = true): array
	{

		if(empty($text)) {
			return [];
		}

		//remove all characters we don't care about
		$text = preg_replace('/[^\w\-_+\\\\\/\s:@]/u', '', mb_strtolower($text));

		if($forSave) {
			//split on white space
			$keywords = mb_split("[\s]+", $text);

			// Add words separated too when they are joined with -, /, \ or _. eg. Jansen-Pietersen or test/foo
			// so they can be found with Jansen-Pietersen but also with Pietersen
			$secondPassKeywords = [];
			foreach ($keywords as $keyword) {
				$split = mb_split('[_\-\\\\\/]', $keyword);
				if(count($split) > 1) {
					$secondPassKeywords = array_merge($secondPassKeywords, $split);
				}
			}

			$keywords = array_merge($keywords, $secondPassKeywords);
		} else{
			// for search just split on white space
			// for search just split on white space
			$keywords = mb_split("[\s]+", $text);;
		}

		//remove empty stuff.
		return array_filter($keywords, function($keyword) {
			$word = preg_replace("/[^\w]/", "", $keyword);
			return !empty($word);
		});


	}

	/**
	 * Split numbers into multipe partials so we can match them using an index
	 * eg.
	 *
	 * ticket no
	 *
	 * 2002-12341234
	 *
	 * Will be found on:
	 *
	 * 002-12341234
	 * 02-12341234
	 * 2-12341234
	 * -12341234
	 * 12341234
	 * 2341234
	 * 341234
	 * 41234
	 * 1234
	 * 234
	 *
	 * this is faster then searchgin for
	 *
	 * %234 because it can't use an index
	 *
	 * @param $number
	 * @param int $minSearchLength
	 * @return array
	 */
	public static function numberToKeywords($number, $minSearchLength = 3) {
		$keywords = [$number];

		while(strlen($number) > $minSearchLength) {
			$number = substr($number, 1);
			$keywords[] = $number;
		}

		return $keywords;

	}

	/**
	 * Prepares the query for a search
	 *
	 * @param Criteria $criteria
	 * @param Query $query
	 * @param $searchPhrase
	 * @throws \Exception
	 */
	public static function addCriteria(Criteria $criteria, Query $query, $searchPhrase) {

		go()->setOptimizerSearchDepth();

		$i = 0;
		$words = SearchableTrait::splitTextKeywords($searchPhrase, false);
		$words = array_unique($words);


		//$query->noCache();

		foreach($words as $word) {
			$query->join(
				"core_search_word",
				'w'.$i, 'w'.$i.'.searchId = search.id',
				'INNER'
			);

			$criteria->where('w'.$i.'.word', 'LIKE', $word . '%');

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

		if(!static::$updateSearch) {
			return true;
		}

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
			$arr = array_merge($arr, self::splitTextKeywords($keyword));
		}

		$keywords = array_unique($arr);

		if(!empty($this->id) && !in_array($this->id, $keywords)) {
			$keywords[] = $this->id;
		}

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
			return ['searchId' => $search->id, 'word'=> $word];
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

		go()->debug("Deleted " . $delSearchStmt->rowCount() ." search results");

		if(!Link::delete((new Query)
			->where(['fromEntityTypeId' => static::entityType()->getId()])
			->andWhere('fromId', 'IN', $query)
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
		
		$limit = 1000;

		/** @var Entity $cls */
		$query = $cls::find();
		/* @var $query \go\core\db\Query */
		$query->join("core_search", "search", "search.entityId = ".$query->getTableAlias() . ".id AND search.entityTypeId = " . $cls::entityType()->getId(), "LEFT");
		$query->andWhere('search.id IS NULL')

//			$query->where('id', 'not in', Search::find()->selectSingleValue('entityId')->where('entityTypeId', '=', $cls::entityType()->getId()))
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

		go()->getDbConnection()->exec("commit");

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
			go()->getDbConnection()->exec("commit");

			$stmt = self::queryMissingSearchCache($cls, $offset);
		}


		go()->getDbConnection()->exec("commit");


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
