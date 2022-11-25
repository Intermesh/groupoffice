<?php
namespace go\core\model;

use go\core\orm\Entity;
use go\core\orm\Mapping;
use go\core\util\JSON;

class ImportMapping extends Entity {
	public $entityTypeId;
	public $checksum;
	protected $mapping;
	public $updateBy;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("core_import_mapping");
	}

	public function setMap(array $mapping) {
		$this->mapping = JSON::encode($mapping);
	}

	public function getMap() : array {
		return isset($this->mapping) ? JSON::decode($this->mapping, true) : [];
	}
}