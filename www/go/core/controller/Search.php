<?php

namespace go\core\controller;

use go\core\db\Criteria;
use go\core\ErrorHandler;
use go\core\event\EventEmitterTrait;
use go\core\model\Acl;
use go\core\orm\Query;
use go\core\jmap\EntityController;
use go\core\util\ArrayObject;
use go\modules\community\addressbook\model\Contact;
use go\core\model\Module;
use go\core\model;

class Search extends EntityController {

	const EVENT_SEARCH_EMAIL_CONTACTS = "searchemailcontacts";

	const EVENT_SEARCH_EMAIL = "searchemail";

	protected function entityClass(): string
	{
		return model\Search::class;
	}

	public function email(array $params)
	{
		$q = $params['filter']['text'] ?? null;

		$hasAddressbook = Module::isAvailableFor("community", "addressbook");
		$isEmailModuleAvailable = Module::isAvailableFor("legacy", "email");
		//TODO change when email module has been refactored.
		$sortByLastContactTime = \GO::config()->get_setting("email_sort_email_addresses_by_time", go()->getAuthState()->getUserId());

		if (!$hasAddressbook || !go()->getSettings()->userAddressBook()->getPermissionLevel()) {

			$query = new Query();

			$selectQueryContact = 'u.id as entityId, "User" as entity, u.email, "" as type, u.displayName AS name, u.avatarId AS photoBlobId ';

			if ($isEmailModuleAvailable && $sortByLastContactTime == "1") {
				$selectQueryContact .= ', NULL as priority';
			}
			$query->from('core_user', 'u')
				->join('core_group', 'g', 'u.id = g.isUserGroupFor');
			Acl::applyToQuery($query, 'g.aclId');

			$query->select($selectQueryContact);

			if (!empty($q)) {
				$query->where(
					(new Criteria)
						->where('email', 'LIKE', '%' . $q . '%')
						->orWhere('displayName', 'LIKE', '%' . $q . '%')
				);
			}

			$query->andWhere('enabled', '=', 1);
		}

		if ($hasAddressbook) {

			if(isset($query)) { // try to join contact for department property
				$query->join("addressbook_contact", "c", "c.goUserId=u.id", 'LEFT');
				$query->select($selectQueryContact . ', c.department as extra');
			}

			$selectQuery = 'c.id as entityId, "Contact" as entity, e.email, e.type, c.name, c.photoBlobId';

			if($isEmailModuleAvailable && $sortByLastContactTime == "1") {
				$selectQuery .= ', em.last_mail_time AS priority';
			}

			$selectQuery .= ", c.department as extra";

			$contactsQuery = (new Query)
							->select($selectQuery)
							->from("addressbook_contact", "c")
							->join("addressbook_email_address", "e", "e.contactId=c.id");

			if($isEmailModuleAvailable && $sortByLastContactTime == "1") {
				$contactsQuery->join("em_contacts_last_mail_times", "em", "em.contact_id = c.id AND em.user_id = " . go()->getAuthState()->getUserId(),"LEFT");
			}


			Contact::applyAclToQuery($contactsQuery);
			$contactsQuery->groupBy(['e.email']);

			if (!empty($q)) {
				$contactsQuery->where(
						(new Criteria)
								->where('e.email', 'LIKE', '%' . $q . '%')
								->orWhere('c.name', 'LIKE', '%' . $q . '%')
				);
			}

			self::fireEvent(self::EVENT_SEARCH_EMAIL_CONTACTS, $contactsQuery, $query ?? $contactsQuery);

			if(isset($query)) {
				$query->union($contactsQuery, true);
			} else{
				$query = $contactsQuery;
			}
		}

		$query->offset($params['position'] ?? 0)
			->limit(20);

		if($isEmailModuleAvailable && $sortByLastContactTime == "1") {
			$query->orderBy(["priority" => "DESC", "name" => "ASC"]);
		}

		self::fireEvent(self::EVENT_SEARCH_EMAIL, $query);

		$response = [
			'list' => $query->toArray()
		];

		if(go()->getDebugger()->enabled) {
			$response['query']  = (string) $query;
		}
		
		\go\core\jmap\Response::get()->addResponse($response);
	}
	
	/**
	 * Handles the Foo entity's Foo/query command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/query
	 */
	public function query($params) {
		return $this->defaultQuery($params);
	}

	protected function getQueryQuery(ArrayObject $params): Query
	{
		$query = parent::getQueryQuery($params)
				->groupBy([])
				->select("search.id")
				->removeJoin('core_entity', 'e');

		return $query;
	}

	/**
	 * Handles the Foo entity's Foo/get command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/get
	 */
	public function get($params) {
		return $this->defaultGet($params);
	}
	
	/**
	 * Handles the Foo entity's Foo/set command
	 * 
	 * @see https://jmap.io/spec-core.html#/set
	 * @param array $params
	 */
	public function set($params) {
		return $this->defaultSet($params);
	}
	
	
	/**
	 * Handles the Foo entity's Foo/changes command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/changes
	 */
	public function changes($params) {
		return $this->defaultChanges($params);
	}

}
