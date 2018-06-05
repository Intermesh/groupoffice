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
 * The EnabledCategory model
 *
 * @package GO.modules.customfields.model
 * @property int $category_id
 * @property string $model_name
 * @property int $model_id
 */


namespace GO\Customfields\Model;


class EnabledCategory extends \GO\Base\Db\ActiveRecord{
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return EnabledCategory 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	
	public function tableName() {
		return 'cf_enabled_categories';
	}
	
	public function primaryKey() {
		return array('model_id','model_name','category_id');
	}
	
	/**
	 * Get enabled categories for a model.
	 * 
	 * @param StringHelper $modelName The name of the model that controls the disabled categories. eg. \GO\Addressbook\Model\Addressbook controls them for \GO\Addressbook\Model\Contact
	 * @param int $modelId
	 * @return array 
	 */
	public function getEnabledIds($modelName, $modelId){
		 $stmt = $this->find(
			\GO\Base\Db\FindParams::newInstance()
						->criteria(
								\GO\Base\Db\FindCriteria::newInstance()							
									->addCondition('model_name', $modelName)
										->addCondition('model_id', $modelId)
										)
						 );
		 		 	 
		 $ids = array();
		 
		 while($enabled = $stmt->fetch()){
			 $ids[]=$enabled->category_id;
		 }
		 
		 return $ids;		 
	}
	
}
