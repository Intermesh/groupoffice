<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *

 */

/**
 * @property int $contact_id
 * @property int $user_id
 * @property int $last_mail_time
 */


namespace GO\Email\Model;


class ContactMailTime extends \GO\Base\Db\ActiveRecord {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return \GO\Base\Model\UserGroup 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function tableName() {
		return 'em_contacts_last_mail_times';
	}
  
	public function relations() {
		return array(
				'contact' => array('type' => self::BELONGS_TO, 'model' => 'GO\Addressbook\Model\Contact', 'field' => 'contact_id'),
		);
	}
  public function primaryKey() {
    return array('contact_id','user_id');
  }
}