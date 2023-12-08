<?php
namespace go\modules\community\oauth2client\model;

use Exception;
use GO\Base\Mail\ImapAuthenticationFailedException;
use go\core\auth\PrimaryAuthenticator;
use go\core\ErrorHandler;
use go\core\ldap\Record;
use go\core\model\User;
use GO\Email\Model\Account;
use go\modules\community\ldapauthenticator\model\Server;
use go\modules\community\ldapauthenticator\Module;

/**
 * LDAP Authenticator
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @author Merijn Schering <mschering@intermesh.nl> 
 */
class Authenticator extends PrimaryAuthenticator {
	
	public static function id() : string {
		return "openid";
	}

	public static function isAvailableFor(string $username) : bool
	{
		return go()->getDbConnection()
			->select("username")
			->from("oauth2client_openid_user", 'o')
			->join('core_user', 'u', 'u.id=o.userId')
				->where('username', '=', $username)
			->single() !== null;
	}

}
