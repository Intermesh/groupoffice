<?php
namespace go\core\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\db\Criteria;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\util\ArrayObject;

/**
 * EntityFilter model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class EntityFilter extends AclOwnerEntity {

	public ?string $id;
	
	protected int $entityTypeId;

	public ?string $createdBy;
	
	public string $name;
	
	protected ?string $filter = null;

	public string $type = "fixed";

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable("core_entity_filter", 'f');
	}

	protected function canCreate(): bool
	{
		return self::entityType()->getModule()->getUserRights()->mayRead;
	}

	public function getFilter() {
		return empty($this->filter) ? [] : json_decode($this->filter, true);
	}
	
	public function setFilter($filter) {
		$this->filter = json_encode($filter);
	}
	
	public function getEntity() {
		return \go\core\orm\EntityType::findById($this->entityTypeId)->getName();
	}
	
	public function setEntity($name) {
		$this->entityTypeId = \go\core\orm\EntityType::findByName($name)->getId();
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add('entity', function (Criteria $criteria, $value, \go\core\orm\Query $query){
				$query->join('core_entity', 'e', 'e.id = f.entityTypeId');

				$criteria->where(['e.clientName' => $value]);
			})
			->add('type', function (Criteria $criteria, $value) {
				$criteria->where('type','=', $value);
			});
	}

	public static function sort(\go\core\orm\Query $query, ArrayObject $sort): Query
	{
		if(!count($sort)) {
			$sort['name'] = 'ASC';
		}

		return parent::sort($query, $sort);
	}
}
