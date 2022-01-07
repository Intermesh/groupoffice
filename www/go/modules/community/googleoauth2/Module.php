<?php
namespace go\modules\community\googleoauth2;
							
use go\core;
use go\core\orm\Property;
use go\core\webclient\CSP;
use go\modules\community\email\model\Account;
use go\modules\community\googleoauth2\model\Oauth2Account;

/**						
 * @copyright (c) 2021, Intermesh BV http://www.intermesh.nl
 * @author Joachim van de Haterd <jvdhaterd@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Module extends core\Module
{
							
	public function getAuthor() :string
	{
		return "Intermesh BV <info@intermesh.nl>";
	}


	public function defineListeners()
	{
		Account::on(Property::EVENT_MAPPING, static::class, 'onMap');
		CSP::on(Csp::EVENT_CREATE,  static::class, 'onCspCreate');
	}


	public static function onMap(core\orm\Mapping $mapping)
	{
		$mapping->addHasOne('googleOauth2', Oauth2Account::class, ['id' => 'accountId'], false);
	}


	public static function onCspCreate(CSP $csp)
	{
		$csp->add('default-src',  trim('https://accounts.google.com', '/'))
			->add("connect-src", "'self'")
			->add("connect-src", trim('https://accounts.google.com', '/'));

	}
}