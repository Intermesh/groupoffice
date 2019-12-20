<?php

namespace go\core\model;

use go\core\model\Acl;
use go\core\acl\model\AclOwnerEntity;
use go\core\db\Criteria;
use go\core\orm\EntityType;
use go\core\orm\Query;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use go\core\db\Expression;

class Search extends AclOwnerEntity {

	public $id;
	public $entityId;
	protected $entityTypeId;
	/**
	 * @var EntityType
	 */
	protected $entity;
	protected $moduleId;

	public static function check()
	{
		//remove search cache with invalid aclId's. Can happen in old framework.
		go()->getDbConnection()->exec("delete s from core_search s left join core_acl a on a.id = s.aclId where a.id is null");
		return parent::check();
	}
		
	
	public function getEntity() {
		return $this->entity;
	}
	
	public function setAclId($aclId) {
		$this->aclId = $aclId;
	}
	
	//don't delete acl on search
	protected static function getAclsToDelete(Query $query) {
		return [];
	}

	public function findAclId() {
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
	protected $keywords;

	public function setKeywords($keywords) {
		$this->keywords = $keywords;
	}
	
	protected function internalValidate() {
		
		$this->name = StringUtil::cutString($this->name, $this->getMapping()->getColumn('name')->length, false);
		$this->description = StringUtil::cutString($this->description, $this->getMapping()->getColumn('description')->length);		
		$this->keywords = StringUtil::cutString($this->keywords, $this->getMapping()->getColumn('keywords')->length, true, "");
		
		return parent::internalValidate();
	}

	/**
	 *
	 * @var DateTime
	 */
	public $modifiedAt;

	protected static function defineMapping() {
		return parent::defineMapping()
										->addTable('core_search', 's')
										->setQuery(
														(new Query())
														->select("e.clientName AS entity")
														->join('core_entity', 'e', 'e.id = s.entityTypeId')
		);
	}

	/**
	 * Applies conditions to the query so that only entities with the given permission level are fetched.
	 * 
	 * @param Query $query
	 * @param int $level
	 */
	public static function applyAclToQuery(Query $query, $level = Acl::LEVEL_READ, $userId = null, $groups = null) {
		Acl::applyToQuery($query, 's.aclId', $level, $userId, $groups);
		
		return $query;
	}
	
	protected static function defineFilters() {
		return parent::defineFilters()
						->add("link", function(Criteria $criteria, $value, Query $query) {
							
							$on = 's.entityId = link.toId AND s.entityTypeId = link . toEntityTypeId';							
								
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
								$w = ['e.name' => $e['name']];
								if(isset($e['filter'])) {
									$w['filter'] = $e['filter'];
								}

								$sub->orWhere($w);
							}

							$criteria->where($sub);		
							
						})
						->add('text', function(Criteria $criteria, $value, Query $query) {

							$criteria->andWhere(
								(new Criteria())
								->orWhere('keywords','like', '%' . preg_replace('/\\s/', '%', $value) . '%')
							);

							// $value = static::convertQuery($value);

							// $criteria->where('MATCH (s.name, s.keywords) AGAINST (:keyword1 IN BOOLEAN MODE)')
							// ->bind(':keyword1', $value);
							// //->bind(':keyword2', $value);

							// // Order by relevance
							// //$query->orderBy([new Expression('MATCH (s.name, s.keywords) AGAINST (:keyword2 IN BOOLEAN MODE) DESC')]);
						});					
	}

	public static function sort(\go\core\orm\Query $query, array $sort)
	{
		if(empty($sort)) {
			$sort['s.modifiedAt'] = 'DESC';
		}

		return parent::sort($query, $sort);
	}

	// public static function convertQuery($value) {

	// 		//first occuring quote type will be used for tokenizing.
	// 		$doublepos = strpos($value, '"');
	// 		$singlepos = strpos($value, "'");							
	// 		$quote = '"';
	// 		if($singlepos !== false && $singlepos > $doublepos) {
	// 			$quote = "'";
	// 		}

	// 		//https://stackoverflow.com/questions/2202435/php-explode-the-string-but-treat-words-in-quotes-as-a-single-word
	// 		preg_match_all('/'.$quote.'(?:\\\\.|[^\\\\'.$quote.'])*'.$quote.'|\S+/', $value, $tokens);

	// 		$str = "";

	// 		foreach($tokens[0] as $token) {				
					
	// 				if(substr($token, -1,1) !== $quote) {
	// 					$token = $token .= '*';
	// 				}
	// 				$str .= '+' . $token . ' ';
	// 		}

	// 		return $str;
	// }
	
	
	// protected static function textFilterColumns() {
	// 	return ['keywords'];
	// }
	
}
