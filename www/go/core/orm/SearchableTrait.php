<?php
namespace go\core\orm;

use DateTime;
use Exception;
use go\core\db\Criteria;
use go\core\db\Query as OrmQuery;
use go\core\db\Statement;
use go\core\ErrorHandler;
use go\core\model\Link;
use go\core\model\Search;
use go\core\util\ClassFinder;
use go\core\util\StringUtil;
use function go;

trait SearchableTrait {

	public static $updateSearch = true;
	
	/**
	 * The description in the search results
	 * 
	 * @return string
	 */
	abstract public function getSearchDescription(): string;
	
	/**
	 * All the keywords that can be searched on.
	 *
	 * Note: for larger text fields it might be useful to use {@see self::splitTextKeywords()} on it.
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
	 * @param string|int $number
	 * @param int $minSearchLength
	 * @return array
	 */
	public static function numberToKeywords($number, int $minSearchLength = 3): array
	{
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
	 * @param string $searchPhrase
	 * @throws Exception
	 */
	public static function addCriteria(Criteria $criteria, Query $query, string $searchPhrase) {
		$i = 0;
		$words = StringUtil::splitTextKeywords($searchPhrase);
		$words = array_unique($words);

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
	 * @throws Exception
	 */
	public function saveSearch(bool $checkExisting = true): bool
	{

		if(!static::$updateSearch) {
			return true;
		}

		$search = $checkExisting ?
			Search::find()
				->where('entityTypeId','=', static::entityType()->getId())
				->andWhere('entityId', '=', $this->id)->single()
			: false;

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
		
//		$search->createdAt = $this->createdAt;
		
		$keywords = $this->getSearchKeywords();
		if(!isset($keywords)) {
			$keywords = array_merge(StringUtil::splitTextKeywords($search->name), StringUtil::splitTextKeywords($search->description));
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
			$arr = array_merge($arr, StringUtil::splitTextKeywords($keyword));
		}

		$keywords = array_unique($arr);

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


	/**
	 * @throws Exception
	 */
	public static function deleteSearchAndLinks(Query $query): bool
	{
		$delSearchStmt = go()->getDbConnection()
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
	 * @param class-string<Entity> $cls
	 * @param int $offset
	 * @return Statement
	 * @throws Exception
	 */
	private static function queryMissingSearchCache(string $cls, int $offset = 0): Statement
	{
		
		$limit = 1000;

		/** @var Entity $cls */
		$query = $cls::find();
		/* @var $query OrmQuery */
		$query->join("core_search", "search", "search.entityId = ".$query->getTableAlias() . ".id AND search.entityTypeId = " . $cls::entityType()->getId(), "LEFT");
		$query->andWhere('search.id IS NULL')

//			$query->where('id', 'not in', Search::find()->selectSingleValue('entityId')->where('entityTypeId', '=', $cls::entityType()->getId()))
							->limit($limit)
							->offset($offset);


		return $query->execute();
	}

	/**
	 * @throws Exception
	 */
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

				} catch (Exception $e) {
					echo "Error: " . $m->id() . ' '. $m->title() ." : " . $e->getMessage() ."\n";
					ErrorHandler::logException($e);

					$offset++;
				}
			}
			echo "\n";
			go()->getDbConnection()->exec("commit");

			$stmt = self::queryMissingSearchCache($cls, $offset);
		}


		go()->getDbConnection()->exec("commit");


	}

	/**
	 * @throws Exception
	 */
	public static function rebuildSearch() {
		$classFinder = new ClassFinder();
		$entities = $classFinder->findByTrait(SearchableTrait::class);
		
		foreach($entities as $cls) {
			self::rebuildSearchForEntity($cls);			
			echo "\nDone\n\n";
		}
	}
}
