<?php
/**
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @package GO.base.model
 * @version $Id: example.php 7607 20120101Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */
 
/**
 * @package GO.base.model
 * @property string $value
 * @property string $name
 * @property int $user_id
 */


namespace GO\Base\Model;


class State extends \GO\Base\Db\ActiveRecord {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return State 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function tableName() {
		return 'go_state';
	}
	
	public function primaryKey() {
		return array('name','user_id');
	}
	
	/**
	 * Get's the user's client state in a key value array.
	 * 
	 * @param int $user_id
	 * @return array 
	 */
	public function getFullClientState($user_id){
		$state = array();
		$stmt = $this->findByAttribute('user_id', $user_id, \GO\Base\Db\FindParams::newInstance()->select('t.*'));
		while($model= $stmt->fetch()){
			$state[$model->name]=$model->value;
		}
		
		return $state;		
	}
}
