<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author Wilmar van Beusekom <wilmar@intermesh.nl>
 * @property string $sql
 * @property string $name
 * @property boolean $companies
 * @property int $user_id
 * @property int $id
 */


namespace GO\Addressbook\Model;


 class SearchQuery extends \GO\Base\Db\ActiveRecord{
		 
	 /**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Addressbook 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function tableName(){
		return 'ab_search_queries';
	}
	
	public function relations(){
		return array();
	}

}