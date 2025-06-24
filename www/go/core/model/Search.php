<?php

namespace go\core\model;

use Exception;
use GO\Base\Db\ActiveRecord;
use go\core\exception\NotFound;
use go\core\jmap\Entity;
use go\core\model\Acl;
use go\core\acl\model\AclOwnerEntity;
use go\core\db\Criteria;
use go\core\orm\EntityType;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\orm\SearchableTrait;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use go\core\db\Expression;

class Search extends AclOwnerEntity {

	public ?string $id;
	public string $entityId;
	protected int $entityTypeId;
	protected string $entity;
	protected int $moduleId;

	public bool $dontChangeModifiedAt = true;

	public static function loggable(): bool
	{
		return false;
	}


	public static function check()
	{
		//remove search cache with invalid aclId's. Can happen in old framework.
		go()->getDbConnection()->exec("delete s from core_search s left join core_acl a on a.id = s.aclId where a.id is null");


		return parent::check();
	}
	public static function checkAcls() {
		// do nothing for search
	}

	/**
	 * Prepares the query for a search
	 *
	 * @param Criteria $criteria
	 * @param Query $query
	 * @param string $searchPhrase
	 * @throws Exception
	 */
	public static function addCriteria(Criteria $criteria, Query $query, string $searchPhrase)
	{

		go()->setOptimizerSearchDepth();

		$i = 0;
		$words = StringUtil::splitTextKeywords($searchPhrase, false);
		$words = array_unique($words);

		foreach ($words as $word) {
			$query->join(
				"core_search_word",
				'w' . $i, 'w' . $i . '.searchId = search.id',
				'INNER'
			);

			$criteria->where('w' . $i . '.word', 'LIKE', $word . '%');

			$i++;
		}
	}

	protected function createAcl() : void
	{
		// don't for search
	}

	protected static function checkAcl()
	{
		//don't call parent as it's messes up core_acl references!
	}

	protected static function getDefaultFetchProperties() : array
	{
		//Acl prop is not needed for search results
		return array_diff(parent::getDefaultFetchProperties(), ['acl']);
	}


	public function getEntity() {
		return $this->entity;
	}
	
	public function setAclId(?int $aclId) {
		$this->aclId = $aclId;
	}
	
	//don't delete acl on search
	protected static function getAclsToDelete(Query $query) : array
	{
		return [];
	}

	public function findAclId() : int {
		return $this->aclId;
	}
	
	
	/**
	 * Set the entity type
	 * 
	 * @param string|EntityType $entity "note" or entitytype instance
	 */
	public function setEntity($entity) {
		
		if(!($entity instanceof EntityType)) {
			$entity = EntityType::findByName($entity);
		}	
		$this->entity = $entity->getName();
		$this->entityTypeId = $entity->getId();
		$this->moduleId = $entity->getModuleId();
	}

	public $name;
	public $description;
	public $filter;

	protected function internalValidate() {
		
		$this->name = StringUtil::cutString($this->name, $this->getMapping()->getColumn('name')->length, false);
		$this->description = StringUtil::cutString($this->description, $this->getMapping()->getColumn('description')->length);		
		//$this->keywords = StringUtil::cutString($this->keywords, $this->getMapping()->getColumn('keywords')->length, true, "");
		
		return parent::internalValidate();
	}

	/**
	 *
	 * @var DateTime
	 */
	public $modifiedAt;

	/**
	 * Rebuild this entry when running build search cache.
	 *
	 * @var bool
	 */
	public $rebuild = false;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
										->addTable('core_search', 'search')
										->addQuery(
														(new Query())
														->select("e.clientName AS entity")
														->join('core_entity', 'e', 'e.id = search.entityTypeId')
		);
	}

	/**
	 * Applies conditions to the query so that only entities with the given permission level are fetched.
	 * 
	 * @param Query $query
	 * @param int $level
	 */
	public static function applyAclToQuery(Query $query, int $level = Acl::LEVEL_READ, int|null $userId = null, array|null $groups = null): Query
	{
		Acl::applyToQuery($query, 'search.aclId', $level, $userId, $groups);
		
		return $query;
	}
	
	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
						->add("link", function(Criteria $criteria, $value, Query $query) {
							
							$on = 'search.entityId = link.toId AND search.entityTypeId = link . toEntityTypeId';
								
							$query->join('core_link', 'link', $on); 

							$criteria->where('fromId', '=', $value['id'])
											->andWhere('fromEntityTypeId', '=', EntityType::findByName($value['entity'])->getId());							
						})
						->add('entityId', function (Criteria $criteria, $value){
							$criteria->where(['entityId' => $value]);		
						})
						->add('entities', function (Criteria $criteria, $value){
							// Entity filter consist out of name => "Contact" and an optional "filter" => "isOrganization"
							if(empty($value)) {
								return;
							}
							
							$sub = (new Criteria);

							foreach($value as $e) {
								if(is_string($e)) {
									$e = ['name' => $e];
								}
								$w = ['entityTypeId' =>  EntityType::findByName($e['name'])->getId()];
								if(isset($e['filter'])) {
									$w['filter'] = $e['filter'];
								}

								$sub->orWhere($w);
							}

							$criteria->where($sub);		
							
						})
						->add('text', function(Criteria $criteria, $value, Query $query) {
							Search::addCriteria( $criteria, $query, $value);
						});					
	}

	/**
	 * Find the entity this comment belongs to.
	 *
	 * @return Entity|ActiveRecord
	 */
	public function findEntity() {
		$e = EntityType::findById($this->entityTypeId);
		if(!$e) {
			throw new NotFound("Can't find entity type ID: " . $this->entityTypeId);
		}
		$cls = $e->getClassName();
		if(is_a($cls, ActiveRecord::class, true)) {
			return $cls::model()->findByPk($this->entityId);
		} else {
			return $cls::findById($this->entityId);
		}
	}
}
