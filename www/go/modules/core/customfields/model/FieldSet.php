<?php

namespace go\modules\core\customfields\model;

use go\core\acl\model\AclEntity;
use go\core\db\Query;

class FieldSet extends AclEntity {

	public $name;
	
	protected $entityId;
	
	public $sortOrder;
	
	protected $entity;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('core_customfields_field_set', 'fs')
						->setQuery((new Query())->select("e.name AS entity")->join('core_entity', 'e', 'e.id = fs.entityId'));						
	}
	
	public function getEntity() {
		return $this->entity;
	}

}
