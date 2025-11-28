<?php

namespace go\modules\community\jitsimeet;

use go\core;
use go\core\exception\NotFound;
use go\core\http\Exception;
use go\core\model;
use go\core\orm\Property;
use go\core\webclient\CSP;
use GO\Email\Controller\AccountController;
use GO\Email\Controller\MessageController;
use GO\Email\Model\Account as ActiveRecordAccount;
use GO\Email\Model\Alias;
use go\modules\community\email\model\Account;
use go\modules\community\jitsimeet\model\Settings;
use go\modules\community\oauth2client\model\Oauth2Client;
use go\modules\community\oauth2client\model\Authenticator;
use go\modules\community\oauth2client\model\DefaultClient;
use go\modules\community\oauth2client\model\Oauth2Account;

/**
 * @copyright (c) 2021, Intermesh BV http://www.intermesh.nl
 * @author Joachim van de Haterd <jvdhaterd@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Module extends core\Module
{
	public function getStatus() : string
	{
		return self::STATUS_STABLE;
	}

	public function getAuthor(): string
	{
		return "Intermesh BV <info@intermesh.nl>";
	}

	public function getDependencies(): array
	{
		return ["community/calendar"];
	}

	public function getSettings()
	{
		return Settings::get();
	}
}
