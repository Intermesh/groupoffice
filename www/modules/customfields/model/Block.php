<?php
/**
 * @property int $id
 * @property string $name
 * @property int $field_id
 */

namespace GO\Customfields\Model;


class Block extends \GO\Base\Db\ActiveRecord{
		
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
		return 'cf_blocks';
	}
	
	public function relations() {
		return array(
				'customField' => array('type' => self::BELONGS_TO, 'model' => 'GO\Customfields\Model\Field', 'field' => 'field_id'),
				'enabledModels'=>array('type' => self::HAS_MANY, 'model' => 'GO\Customfields\Model\EnabledBlock', 'field' => 'block_id','delete'=>true)
			);
	}
		
	protected function init() {
		
		$this->columns['name']['required']=true;
		$this->columns['field_id']['required']=true;
		
		parent::init();
	}

	public function getItemNames($forModelId,$forModelName) {
		
		$modelUnderBlock = \GO::getModel($this->customField->category->extends_model);
		
		$cfTableName = 'cf_'.$modelUnderBlock->tableName();
		
		$stmt = $modelUnderBlock->find(
			\GO\Base\Db\FindParams::newInstance()
				->ignoreAcl()
				->join(
					$cfTableName,
					\GO\Base\Db\FindCriteria::newInstance()->addRawCondition('cf.model_id', 't.id'),
					'cf',
					'INNER'
				)
				->criteria(
					\GO\Base\Db\FindCriteria::newInstance()
						->addCondition('col_'.$this->field_id, $forModelId.':%', 'LIKE', 'cf')
				)
		);
		
		$itemNamesArr = array();
		
		foreach ($stmt as $item) {
			$name = $item->className()=='GO\Addressbook\Model\Company' || $item->className()=='GO\Addressbook\Model\Contact'
				? $item->name.' ('.$item->addressbook->name.')'
				: $item->name;
			$itemNamesArr[] = array('model_id'=>$item->id,'model_name'=>$item->className(),'item_name'=>$name);
		}
		
		usort($itemNamesArr,function($a,$b) {
			return $a['item_name']>=$b['item_name'] ? 1 : -1;
		});
		
		return $itemNamesArr;
	}
	
}