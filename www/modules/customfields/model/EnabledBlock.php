<?php
/**
 * @property int $block_id
 * @property string $model_type_name
 * @property int $model_id
 */

namespace GO\Customfields\Model;


class EnabledBlock extends \GO\Base\Db\ActiveRecord{
		
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Field 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function tableName() {
		return 'cf_enabled_blocks';
	}
	
	public function primaryKey() {
		return array('block_id','model_id','model_type_name');
	}
	
	public function relations() {
		return array(
				'block' => array('type' => self::BELONGS_TO, 'model' => 'GO\Customfields\Model\Block', 'field' => 'block_id')
			);
	}
		
//	protected function init() {
//		
//		$this->columns['model_type_name']['required']=true;
//		$this->columns['field_id']['required']=true;
//		
//		parent::init();
//	}

	public static function getEnabledBlocks($modelId,$listedModelTypeName,$listingModelName) {
		
		if ($listingModelName=='GO\Addressbook\Model\Contact')
			$dataType = 'GO\Addressbook\Customfieldtype\Contact';
		else
			$dataType = 'GO\Addressbook\Customfieldtype\Company';
		
		return self::model()->find(
				\GO\Base\Db\FindParams::newInstance()
					->joinModel(array(
						'model'=>'GO\Customfields\Model\Block',
						'localTableAlias'=>'t',
						'localField'=>'block_id',
						'foreignField'=>'id',
						'tableAlias'=>'b',
						'type'=>'INNER'
					))
					->joinModel(array(
						'model'=>'GO\Customfields\Model\Field',
						'localTableAlias'=>'b',
						'localField'=>'field_id',
						'foreignField'=>'id',
						'tableAlias'=>'cf',
						'type'=>'INNER'
					))
					->criteria(
						\GO\Base\Db\FindCriteria::newInstance()
							->addCondition('model_id', $modelId, '=', 't')
							->addCondition('model_type_name', $listedModelTypeName, '=', 't')
							->addCondition('datatype', $dataType, '=', 'cf')
					)->debugSql()
			);
//		->findByAttributes(array(
//			'model_id' => $modelId,
//			'model_type_name' => $listedModelTypeName
//		));
		
	}
	
}