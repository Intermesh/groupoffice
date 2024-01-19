<?php

namespace go\core\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\db\Criteria;
use go\core\db\Expression;
use go\core\orm\EntityType;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;

/**
 * A Principal represents an individual, group, location (e.g. a room), resource (e.g. a projector) or other entity in a collaborative environment.
 */
class Principal extends AclOwnerEntity
{
	const Individual = 'individual'; // This represents a single person.
	const Group = 'group'; // This represents a group of people.
	const Resource = 'resource'; // This represents some resource, e.g. a projector.
	const Location = 'location'; // This represents a location (that needs scheduling).
	const Other = 'other'; // This represents some other undefined principal.

	protected $entityId;
	protected $entityTypeId;

	/** @var string generated use from clientName-entityId */
	public $id;

	/** @var string One of the type constants */
	public $type;

	/** @var string The name of the principal, e.g. “Jane Doe”, or “Room 4B”. */
	public $name;

	/** @var ?string 40char hex hash of file */
	public $avatarId;

	/** @var ?string A longer description of the principal, for example details about the facilities of a resource, or null if no description available. */
	public $description;

	/** @var ?string An email address for the principal, or null if no email is available */
	public $email;

	/** @var ?string The time zone for this principal, if known. If not null, the value MUST be a time zone id from the IANA Time Zone Database */
	public $timeZone;

	protected $clientName;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('core_principal', 'principal');
	}


//	public function getCapabilities() {
//		return [
//			'mayGetAvailability' => true, // met the user call Prinicpal/getAvailability
//			'mayShareWith' => true, // may the principal be added to the shareWith of a calendar.
//		];
//	}

	protected static function textFilterColumns(): array
	{
		return ['name', 'description', 'email'];
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add('type', function(Criteria $criteria, $value) {
				$criteria->andWhere('type', '=', $value);
			})->add('entity', function(Criteria $criteria, $value, $query) {
				$query->join('core_entity', 'ett', 'ett.id = principal.entityTypeId');
				$criteria->andWhere('ett.clientName', '=', $value);
			})->add('permissionLevel', function(Criteria $criteria, $value, Query $query) {
				if(!$query->isJoined('core_group', 'g')) {
					$query->join('core_group', 'g', 'principal.id = g.isUserGroupFor');
				}
				Acl::applyToQuery($query, 'g.aclId', $value);
			}, Acl::LEVEL_READ)
//			->add('showDisabled', function (Criteria $criteria, $value){
//				if($value === false) {
//					$criteria->andWhere('enabled', '=', 1);
//				}
//			}, false)
			->add('email', function (Criteria $criteria, $value, Query $query){
				$criteria->where('email', '=', $value);
			})
			->add('username', function (Criteria $criteria, $value, Query $query){
				$query->filter(['entity'=>'User']);
				$criteria->where('description', '=', $value);
			})
			->add('groupId', function (Criteria $criteria, $value, Query $query){
				$query->join('core_user_group', 'ug', 'ug.userId = principal.id')->andWhere(['ug.groupId' => $value]);
			})
			->add('groupMember',function (Criteria $criteria, $value, Query $query){
				//this filter doesn't actually filter but sorts the selected members on top
				$query->join('core_user_group', 'ug_sort', 'ug_sort.userId = principal.id AND ug_sort.groupId = ' . (int) $value, 'LEFT');
				$query->orderBy(array_merge([new Expression('ISNULL(ug_sort.userId) ASC')], $query->getOrderBy()));
				$query->groupBy(['principal.id']);
			})
			->add('aclId',  function (Criteria $criteria, $value, Query $query) {

				$query->join('core_user_group', 'aclIdUg', 'aclIdUg.userId = principal.id')
					->join('core_acl_group', 'aclIdAg', 'aclIdAg.groupId = aclIdUg.groupId')
					->groupBy(['principal.id'], true);

				$criteria->where('aclIdAg.aclId', '=', $value);
			})
			->add('aclPermissionLevel',  function (Criteria $criteria, $value, Query $query) {

				// can be used in conjunction with the aclId filter.

				$criteria->where('aclIdAg.level', '>=', $value);
			});
	}

	public static function loggable(): bool
	{
		return false;
	}

	public static function check()
	{
		//remove principal cache with invalid aclId's. Can happen in old framework.
		go()->getDbConnection()->exec("delete p from core_principal p left join core_acl a on a.id = p.aclId where a.id is null");
		return parent::check();
	}


	public function setAclId($aclId) {
		$this->aclId = $aclId;
	}

	public function setEntityType($entityType, $id) {

		if(!($entityType instanceof EntityType)) {
			$entityType = EntityType::findByName($entityType);
		}
		if($entityType->getName() === 'User')
			$this->id = $id;
		else {
			$this->id = $entityType->getName() . ':' . $id;
		}
		$this->entityId = $id;
		$this->entityTypeId = $entityType->getId();
	}

}