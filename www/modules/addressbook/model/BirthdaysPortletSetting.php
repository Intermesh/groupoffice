<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */
 
/**
 * This model is for saving  the setting to the birthdays portlet
 *
 * @package GO.modules.addressbook
 * @version $Id: BirthdaysPortletSetting.php 16715 2014-01-27 10:14:00Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Michael de Hart mdhart@intermesh.nl
 *
 * @property int $user_id
 * @property int $addressbook_id
 */


namespace GO\Addressbook\Model;


class BirthdaysPortletSetting extends \GO\Base\Db\ActiveRecord {

	public function tableName() { return 'ab_portlet_birthdays'; }
	public function primaryKey() { return array('addressbook_id','user_id');}
	
	public function relations() {
		return array(
			'addressbook' => array('type' => self::BELONGS_TO, 'model' => 'GO\Addressbook\Model\Addressbook', 'field' => 'addressbook_id', 'delete' => false),
			);
	}
	
}
