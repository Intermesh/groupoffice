<?php
/**
 * Group-Office
 *
 * Copyright Intermesh BV.
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @package GO.modules.smime.model
 * @version $Id: example.php 7607 20120101Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */
namespace GO\Smime\Model;

/**
 * Extend the email account settings with an always_sign boolean option
 *
 * @property int $account_id
 * @property boolean $always_sign
 */
class AccountSettings extends \GO\Base\Db\ActiveRecord
{

	public static $trimOnSave = false;

	public function primaryKey()
	{
		return 'account_id';
	}

	/**
	 * Returns the table name
	 */
	public function tableName()
	{
		return 'smi_account_settings';
	}
}
