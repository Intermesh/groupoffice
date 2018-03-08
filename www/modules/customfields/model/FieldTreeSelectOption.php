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
 * @package GO.modules.customfields.model
 * @version $Id: example.php 7607 20120101Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */
 
/**
 * The FieldTreeSelectOption model
 *
 * @package GO.modules.customfields.model
 * @property int $sort
 * @property string $name
 * @property int $field_id
 * @property int $parent_id
 * @property int $id
 */


namespace GO\Customfields\Model;


class FieldTreeSelectOption extends \GO\Base\Db\ActiveRecord{
		
	
	public $checkSlaves=true;
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return FieldSelectOption 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function tableName() {
		return 'cf_tree_select_options';
	}	
	
	public function relations() {
		return array(
				'field' => array('type' => self::BELONGS_TO, 'model' => 'GO\Customfields\Model\Field', 'field' => 'field_id')		);
	}	
	
	protected function beforeSave() {
		
		if($this->isNew && empty($this->sort)){
			$record = $this->findSingle(array(
					'fields'=>'MAX(`sort`) AS sort',
					'where'=>'field_id=:field_id',
					'bindParams'=>array('field_id'=>$this->field_id)
			));
			if($record)
				$this->sort=intval($record->sort);
		}
		
		return parent::beforeSave();
	}
	
	protected function afterSave($wasNew) {
		
		if($wasNew && $this->checkSlaves){
			$this->field->checkTreeSelectSlaves();			
		}
		
		return parent::afterSave($wasNew);
	}
	

	
//	public function getChildren(){
//		$stmt = self::model()->find(array(
//			'where'=>'parent_id=:parent_id AND field_id=:field_id',
//			'bindParams'=>array('parent_id'=>$this->id,'field_id'=>$this->field_id),
//			'order'=>'sort'
//		));
//		
//		return $stmt->fetchAll();
//	}

}