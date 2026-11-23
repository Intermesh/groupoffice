<?php

namespace go\modules\community\calendar\controller;

use go\core\Controller;


class ParticipantIdentity extends Controller {

	/**
	 * Get the participant identity of the current user
	 *
	 * @apiPermission Module access: returns only the name and e-mail address of the user.
	 */
	public function get($params) {
		$u = go()->getAuthState()->getUser(['displayName', 'email']);
		return [
    		"list"=> [[
				"id"=> go()->getUserId(),
				"name"=> $u->displayName,
				"scheduleId"=> "mailto:"+$u->email,
				"sendTo"=> [
					"imip"=> "mailto:"+$u->email,
         		//"other"=>"https://example.com/uri/for/internal/scheduling"
       		],
       		"isDefault"=> true
     		]]
		];
	}

}


