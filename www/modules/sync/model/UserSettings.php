<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */
namespace GO\Sync\Model;

use go\core\orm\Property;
/**
 * The Settings model
 *
 * @package GO.modules.Tasks
 * @version $Id$
 * @copyright Copyright Intermesh BV.
 * @author Michael de Hart mdhart@intermesh.nl
 *
 * @property int $user_id
 */
class UserSettings extends Property {

	public $user_id;
	/**
	 * Email account
	 * @var int
	 */
	public $account_id;

	protected static function defineMapping() {
		return parent::defineMapping()->addTable("sync_settings", "syncs");
	}

}
