<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: LinkedEmail.php 7607 2011-09-01 15:38:01Z <<USERNAME>> $
 * @copyright Copyright Intermesh
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */

/**
 * The Alias model
 * 
 * @package GO.modules.email
 * 
 * @property int $user_id
 * @property int $account_id
 * @property int $order
 */

namespace GO\Email\Model;


class AccountSort extends \GO\Base\Db\ActiveRecord {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return AccountSort
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}


	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'em_accounts_sort';
	}

	public function primaryKey() {
		return array('user_id','account_id');
	}	

}
