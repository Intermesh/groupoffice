<?php

namespace go\modules\core\search\controller;

use go\core\acl\model\Acl;
use go\core\db\Query;
use go\core\jmap\EntityController;
use go\modules\community\addressbook\model\Contact;
use go\modules\core\modules\model\Module;
use go\modules\core\search\model;

class Search extends EntityController {

	protected function entityClass() {
		return model\Search::class;
	}

	public function email($params) {

		$query = new Query();
		$query->select('u.id, "User" as entity, u.email, "" as type, u.displayName AS name')
						->from('core_user', 'u')
						->join('core_group', 'g', 'u.id = g.isUserGroupFor');

		if (isset($params['q'])) {
			$query
							->where('email', 'LIKE', $params['q'] . '%')
							->orWhere('displayName', 'LIKE', $params['q'] . '%');
		}

		Acl::applyToQuery($query, 'g.aclId');

		if (Module::isAvailableFor("community", "addressbook")) {

			$contactsQuery = (new Query)
							->select('c.id, "Contact" as entity, e.email, e.type, c.name')
							->from("addressbook_contact", "c")
							->join("addressbook_email_address", "e", "e.contactId=c.id");

			Contact::applyAclToQuery($contactsQuery);

			if (isset($params['q'])) {
				$contactsQuery
								->where('e.email', 'LIKE', $params['q'] . '%')
								->orWhere('c.name', 'LIKE', $params['q'] . '%');
			}

			$query->union($contactsQuery)
							->offset($params['position'] ?? 0)
							->limit(20);
		}
		
		\go\core\jmap\Response::get()->addResponse([
				'list' => array_map(function($r) {$r['id'] = (int) $r['id']; return $r;}, $query->toArray())
				]);
	}

}
