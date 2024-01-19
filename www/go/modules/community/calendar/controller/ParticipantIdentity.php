<?php

namespace go\modules\community\calendar\controller;

use go\core\Controller;


class ParticipantIdentity extends Controller {

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


