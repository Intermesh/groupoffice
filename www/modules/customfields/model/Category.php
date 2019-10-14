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
 * The Category model
 *
 * @package GO.modules.customfields.model
 * @property int $sortOrder
 * @property string $name
 * @property int $aclId
 * @property string $extendsModel
 * @property int $id
 */


namespace GO\Customfields\Model;

use GO;
use GO\Base\Db\ActiveRecord;
use GO\Base\Db\FindCriteria;
use GO\Base\Db\FindParams;
use GO\Base\Model\Acl;
use GO\Base\Util\StringHelper;
use go\core\db\Query;
use go\core\orm\Entity;


class Category extends ActiveRecord{
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
	
	public function aclField() {	
		return 'aclId';
	}
	
	public function tableName() {
		return 'core_customfields_field_set';
	}
	
	public function relations() {
		return array(
		'fields' => array('type' => self::HAS_MANY, 'model' => 'GO\Customfields\Model\Field', 'field' => 'fieldSetId', 'delete' => true, 'findParams'=>  FindParams::newInstance()->order('sortOrder')),
		'_fieldsUnsorted' => array('type' => self::HAS_MANY, 'model' => 'GO\Customfields\Model\Field', 'field' => 'fieldSetId'	),
		'_fieldsSortedById' => array('type' => self::HAS_MANY, 'model' => 'GO\Customfields\Model\Field', 'field' => 'fieldSetId', 'delete' => true, 'findParams'=>  FindParams::newInstance()->order('id')),
				);
	}
	
	
	public function isForEntity() {
		return is_a($this->extendsModel, Entity::class, true);
	}
	
	public function customfieldsTableName() {		
		if($this->isForEntity()) {
			$cls = $this->extendsModel;
			return $cls::customFieldsTableName();
		} else
		{
			$model = GO::getModel($this->extendsModel);
		
			return 'cf_'.$model->tableName();
		}		
	}
	
	public function getExtendsModel() {
		return \go\core\orm\EntityType::findById($this->entityId)->getClassName();
	}
	
	public function setExtendsModel($className) {
		$this->entityId = \go\core\orm\EntityType::findByClassName($className)->getId();
	}
	
	
	public function findByModel($modelName, $permissionLevel=  Acl::READ_PERMISSION){
		
		$entityId = $modelName::getType()->getId();
		
		return Category::model()->find(
                    FindParams::newInstance()												
												->permissionLevel($permissionLevel)
												->criteria(FindCriteria::newInstance()->addCondition('entityId', $entityId))
                        ->order('sortOrder')
		);
	}
	
	/**
	 * Find or create a category
	 * 
	 * @param StringHelper $extendsModel
	 * @param StringHelper $categoryName
	 * @return \Category 
	 */
	public function createIfNotExists($extendsModel, $categoryName){
		$entityId = $extendsModel::getType()->getId();;		
		
		$category = Category::model()->findSingleByAttributes(array('entityId'=>$entityId, 'name'=>$categoryName));
		
		if(!$category){
			$category = new Category();
			$category->entityId=$entityId;
			$category->name=$categoryName;
			$category->save();
		}	
		
		return $category;
	}
	
	
	protected function beforeSave() {
		if($this->isNew)
		$this->sortOrder=$this->count();		
		
		return parent::beforeSave();
	}
	
	protected function afterDuplicate(&$duplicate) {
		
//		$this->duplicateRelation('_fieldsUnsorted', $duplicate);
//		$this->duplicateRelation('fields', $duplicate);
		$this->duplicateRelation('_fieldsSortedById', $duplicate);
		
		return parent::afterDuplicate($duplicate);
	}
}
