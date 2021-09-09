<?php

namespace go\core\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\orm\Query;

/**
 * FieldSet entity
 * 
 * Find for entity type
 * ```
 * $fieldsets = \go\core\model\FieldSet::find()->filter(['entities' => ['Event']]);
 * ```
 * 
 * Create:
 * ````
 * 
 *		$fieldSet = new FieldSet();
 *		$fieldSet->name = "Forum";
 *		$fieldSet->setEntity('User');
 *		if(!$fieldSet->save()) {
 *			throw new \Exception("Could not save fieldset");
 *		}
 *	```
 */
class FieldSet extends AclOwnerEntity {
/**
	 * The ID
	 * 
	 * @var int
	 */
	public $id;

	public $name;
	
	public $description;
	
	protected $entityId;
	
	public $sortOrder;
	
	protected $entity;
	
	protected $filter;	
	
	/**
	 * Show this fieldset as a tab in clients
	 * 
	 * @var bool 
	 */
	public $isTab = false;

	/**
	 * Amount of columns the fieldset should render.
	 *
	 * @var int
	 */
	public $columns = 2;
	
	/**
	 * The filter is an object that can be used to show and hide field sets based
	 * on the entity values.
	 * 
	 * For example a contact fieldset may have:
	 * 
	 * filter = {
	 *    addressBookId: [1, 2],
	 *    isOrganization: true
	 * }
	 * 
	 * Will only show this fieldset for addressBookId 1 and 2 and for organizations.
	 * 
	 * @return array
	 */
	public function getFilter() {
		return empty($this->filter) || $this->filter == '[]'  ? new \stdClass() : json_decode($this->filter, true);
	}

	protected function canCreate()
	{
		return go()->getAuthState()->isAdmin();
	}
	
	public function setFilter($filter) {
		$this->filter = json_encode($filter);
	}
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('core_customfields_field_set', 'fs')
						->addQuery((new Query())->select("e.name AS entity")->join('core_entity', 'e', 'e.id = fs.entityId'));
	}
	
	public function getEntity() {
		return $this->entity;
	}
	
	public function setEntity($name) {
		$this->entity = $name;
		$e = \go\core\orm\EntityType::findByName($name);
		$this->entityId = $e->getId();
	}
	
	protected static function defineFilters() {
		return parent::defineFilters()
						->add('entities', function(\go\core\db\Criteria $criteria, $value) {
							//$ids = \go\core\orm\EntityType::namesToIds($value);			
							$criteria->andWhere('e.name', 'IN', $value);
						});
	}
	
	protected static function internalDelete(Query $query) {
		
		if(!Field::delete(['fieldSetId' => $query])) {
			throw new \Exception("Could not delete fields");
		}
		
		return parent::internalDelete($query);
	}
	
	// protected function internalSave() {
	// 	if(!parent::internalSave()) {
	// 		return false;
	// 	}
		
	// 	return !$this->isNew() || $this->findAcl()->addGroup(\go\core\model\Group::ID_EVERYONE, \go\core\model\Acl::LEVEL_WRITE)->save();
		
	// }

		/**
	 * Find all fields for an entity
	 * 
	 * @param string $name
	 * @return Query
	 */
	public static function findByEntity($name) {
		$e = \go\core\orm\EntityType::findByName($name);
		$entityTypeId = $e->getId();
		return static::find()->where(['entityId' => $entityTypeId]);
	}


}
