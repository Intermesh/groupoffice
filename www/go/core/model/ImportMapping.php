<?php
namespace go\core\model;

use go\core\db\Criteria;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\jmap\Entity;
use go\core\jmap\exception\InvalidArguments;
use go\core\orm\EntityType;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\util\JSON;

class ImportMapping extends Entity {

	public ?string $id;
	public int $entityTypeId;
	public ?string $checksum;
	public string $name;
	protected ?string $mapping;
	public ?string $updateBy;

	public ?string $thousandsSeparator = null;
	public ?string $decimalSeparator = null;
	public ?string $dateFormat = null;
	public ?string $timeFormat = null;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("core_import_mapping");
	}

	protected function canCreate(): bool
	{
		return true;
	}

	public function setMap(array $mapping) {
		$this->mapping = JSON::encode($mapping);
	}

	public function getColumnMapping() : array {
		return isset($this->mapping) ? JSON::decode($this->mapping, true) : [];
	}

	static function findByChecksum($entityTypeId, $checkSum) {
		return self::find()->where(['entityTypeId' => $entityTypeId , 'checksum'=> $checkSum])->orderBy(['id'=>'DESC'])->single();
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add('entity', function (Criteria $criteria, $value, $query){
				$query->join('core_entity', 'e', 'e.id = entityTypeId');
				$criteria->where(['e.clientName' => $value]);
			});

	}
}