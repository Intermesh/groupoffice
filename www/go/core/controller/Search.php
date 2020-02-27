<?php

namespace go\core\controller;

use go\core\db\Criteria;
use go\core\model\Acl;
use go\core\orm\Query;
use go\core\jmap\EntityController;
use go\modules\community\addressbook\model\Contact;
use go\core\model\Module;
use go\core\model;

class Search extends EntityController {

	protected function entityClass() {
		return model\Search::class;
	}

	public function email($params) {
		
		$q = $params['filter']['text'] ?? null;

		$query = new Query();

		$selectQueryContact ='u.id as entityId, "User" as entity, u.email, "" as type, u.displayName AS name, u.avatarId AS photoBlobId';
		$isEmailModuleAvailable = Module::isAvailableFor("legacy","email");
		$optionEnabled = \GO::config()->get_setting("sort_email_addresses_by_time", \GO::user()->id);

		if($isEmailModuleAvailable && $optionEnabled == "1") {
			$selectQueryContact .= ', NULL as priority';
		}
		$query->select($selectQueryContact)
						->from('core_user', 'u')
						->join('core_group', 'g', 'u.id = g.isUserGroupFor');


		if (!empty($q)) {
			$query->where(
				(new Criteria)
							->where('email', 'LIKE', '%' . $q . '%')
							->orWhere('displayName', 'LIKE', '%' . $q . '%')
						);
		}

		Acl::applyToQuery($query, 'g.aclId');

		if (Module::isAvailableFor("community", "addressbook")) {

			$selectQuery = 'c.id as entityId, "Contact" as entity, e.email, e.type, c.name, c.photoBlobId';

			if($isEmailModuleAvailable && $optionEnabled == "1") {
				$selectQuery .= ', em.last_mail_time AS priority';
			}
			$contactsQuery = (new Query)
							->select($selectQuery)
							->from("addressbook_contact", "c")
							->join("addressbook_email_address", "e", "e.contactId=c.id");

			if($isEmailModuleAvailable && $optionEnabled == "1") {
				$contactsQuery->join("em_contacts_last_mail_times", "em", "em.contact_id=c.id","LEFT");
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


			$query->union($contactsQuery);
		}

		$query->offset($params['position'] ?? 0)
			->limit(20);

		if($isEmailModuleAvailable && $optionEnabled == "1") {
			$query->orderBy(["priority" => "DESC", "name" => "ASC"]);
		}
		go()->debug($query);
		
		\go\core\jmap\Response::get()->addResponse([
				'list' => $query->toArray()
				]);
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
