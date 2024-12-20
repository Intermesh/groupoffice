<?php

namespace go\core\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\db\Criteria;
use go\core\orm\Entity;
use go\core\orm\EntityType;
use go\core\orm\exception\SaveException;
use go\core\orm\Filters;
use go\core\orm\Mapping;
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

	public $parentFieldSetId;
	/**
	 * Show this fieldset as a tab in clients
	 * 
	 * @var bool 
	 */
	public $isTab = false;

	public $collapseIfEmpty;

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
		return empty($this->filter) || $this->filter == '[]' || $this->filter == '{}' ? new \stdClass() : json_decode($this->filter, true);
	}

	protected function canCreate(): bool
	{
		return go()->getAuthState()->isAdmin();
	}
	
	public function setFilter($filter): void
	{
		$this->filter = empty($filter) ? null : json_encode($filter);
	}
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable('core_customfields_field_set', 'fs')
						->addQuery((new Query())->select("e.name AS entity")->join('core_entity', 'e', 'e.id = fs.entityId'));
	}
	
	public function getEntity() {
		return $this->entity;
	}

	/**
	 * Set the entity
	 * @param string $name Entity name. eg. "User"
	 * @return void
	 */
	public function setEntity(string $name) {
		$this->entity = $name;
		$e = \go\core\orm\EntityType::findByName($name);
		$this->entityId = $e->getId();
	}
	
	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
						->add('entities', function(Criteria $criteria, $value) {
							$criteria->andWhere('e.name', 'IN', $value);
						})
						->add('isTab', function(Criteria $criteria, $value) {
							$criteria->andWhere('isTab', '=', $value);
						});
	}
	
	protected static function internalDelete(Query $query): bool
	{
		
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
	 * @return Query|static[]
	 */
	public static function findByEntity(string $name) : Query {
		$e = \go\core\orm\EntityType::findByName($name);
		$entityTypeId = $e->getId();
		return static::find()->where(['entityId' => $entityTypeId]);
	}


	/**
	 * Copies field sets from one entity to another. Does not copy the data!
	 *
	 * @param class-string<Entity> $fromEntityCls
	 * @param class-string<Entity> $toEntityCls
	 * @return false|void
	 * @throws SaveException
	 */
	public static function migrateCustomFields(string $fromEntityCls, string $toEntityCls) {

		$fromEntityType = $fromEntityCls::entityType();
		$toEntityType = $toEntityCls::entityType();

		echo "Migrating entity " . $toEntityType->getName() ."\n";

		$fieldSets = \go\core\model\FieldSet::findByEntity($fromEntityType->getName());

		foreach($fieldSets as $fieldSet) {

			echo "Migrating fieldset " . $fieldSet->name . " (". $fieldSet->id .")\n";

			$newFieldSet = FieldSet::findByEntity($toEntityType->getName())->where(['name' => $fieldSet->name])->single();

			if(!$newFieldSet) {
				$newFieldSet = $fieldSet->copy();
				$newFieldSet->setEntity($toEntityType->getName());
				if (!$newFieldSet->save()) {
					throw new \go\core\orm\exception\SaveException($newFieldSet);
				}
			}

			echo $newFieldSet->id ."\n";

			$fields = \go\core\model\Field::find()->where('fieldSetId', '=', $fieldSet->id);
			foreach($fields as $field) {

//				echo "Migrating field " . $field->databaseName . "\n";
				$newField = \go\core\model\Field::find()->where('fieldSetId', '=', $newFieldSet->id)->where(['databaseName' => $field->databaseName])->single();

				if(!$newField) {
					$newField = $field->copy();
					$newField->fieldSetId = $newFieldSet->id;
					if (!$newField->save()) {
						throw new \go\core\orm\exception\SaveException($newField);
					}
				}
			}
		}

		go()->rebuildCache();

	}

}
