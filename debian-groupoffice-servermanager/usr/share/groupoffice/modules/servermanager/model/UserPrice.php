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
 * @copyright Copyright Intermesh BV
 * @version $Id UserPrice.php 2012-09-11 17:12:23 mdhart $
 * @author Michael de Hart <mdehart@intermesh.nl> 
 * @package GO.servermanager.model
 */
/**
 * Active record for user staffel price table
 *
 * @package GO.servermanager.model
 * @copyright Copyright Intermesh
 * @version $Id UserPrice.php 2012-09-11 17:12:23 mdhart $ 
 * @author Michael de Hart <mdehart@intermesh.nl> 
 * 
 * @property int $max_users the user limit for
 * @property double $price_per_month
 */

namespace GO\ServerManager\Model;


class UserPrice extends \GO\Base\Db\ActiveRecord
{
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'sm_user_prices';
	}
	
	public function primaryKey()
	{
		return 'max_users';
	}
}
?>
