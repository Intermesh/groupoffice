<?php
namespace go\modules\community\oidc\model;

use go\core\auth\PrimaryAuthenticator;

/**
 * OIDC Authenticator
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @author Merijn Schering <mschering@intermesh.nl> 
 */
class Authenticator extends PrimaryAuthenticator {
	
	public static function id() : string {
		return "oidc";
	}

	public static function isAvailableFor(string $username) : bool
	{
		return go()->getDbConnection()
			->select("username")
			->from("oidc_user", 'o')
			->join('core_user', 'u', 'u.id = o.userId')
				->where('username', '=', $username)
			->single() !== null;
	}

}
