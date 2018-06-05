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
 * The DisableCategories model
 *
 * @package GO.modules.customfields.model
 * @property string $model_name
 * @property int $model_id
 */


namespace GO\Customfields\Model;


class DisableCategories extends \GO\Base\Db\ActiveRecord{
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Category 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	
	public function tableName() {
		return 'cf_disable_categories';
	}
	
	public function primaryKey() {
		return array('model_id','model_name');
	}

	/**
	 * Check if disabling of certain categories is enabled.
	 * 
	 * @param StringHelper $model_name 
	 * @param int $model_id
	 * @return boolean 
	 */
	public static function isEnabled($model_name, $model_id){
		$model = DisableCategories::model()->findByPk(array('model_id'=>$model_id,'model_name'=>$model_name));
		
		return $model!=false;
	}
	
}
