<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @property int $user_id
 * @property string $text
 */


namespace GO\Summary\Model;


class Note extends \GO\Base\Db\ActiveRecord {
	
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function getLocalizedName(){
		return \GO::t("note", "summary");
	}
	
	public function tableName(){
		return 'su_notes';
	}
	
	public function primaryKey() {
    return 'user_id';
  }
	
}
