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
 * The Settings model
 *
 * @package GO.modules.addressbook
 * @version $Id: Settings.php 7607 2011-09-20 10:06:28Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 *
 * @property int $user_id
 * @property int $default_addressbook_id
 */


namespace GO\Addressbook\Model;


class Settings extends \GO\Base\Model\AbstractUserDefaultModel {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Settings
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}

	public function tableName() {
		return 'ab_settings';
	}

	public function relations() {
		return array(
			'addressbook' => array('type' => self::BELONGS_TO, 'model' => 'GO\Addressbook\Model\Addressbook', 'field' => 'default_addressbook_id', 'delete' => false)
			);
	}
		
	public function primaryKey() {
		return 'user_id';
	}
}
