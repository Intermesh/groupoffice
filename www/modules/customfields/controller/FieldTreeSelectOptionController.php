<?php

namespace GO\Customfields\Controller;


class FieldTreeSelectOptionController extends \GO\Base\Controller\AbstractModelController{
	
	protected $model = 'GO\Customfields\Model\FieldTreeSelectOption';
	

	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		
		$columnModel->formatColumn('name_with_id', '$model->id.":".$model->name');

	}
	
	protected function getStoreParams($params) {
		
		if(isset($params['node']))
			$parent_id=$params['node'];
		else
			$parent_id=$params['parent_id'];
		
		$field_id = $params['field_id'];
		
		$fieldModel = \GO\Customfields\Model\Field::model()->findByPk($field_id, false, true);
		
		if ($params['parent_id']==0 && $fieldModel->datatype=='GO\Customfields\Customfieldtype\TreeselectSlave') {
			return \GO\Base\Db\FindParams::newInstance()
						->order(array("parent_id","sort"))
						->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('field_id', $fieldModel->getOption("treemaster_field_id")));
		} else {
			return \GO\Base\Db\FindParams::newInstance()
						->order("sort")
						->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('field_id', $field_id)->addCondition('parent_id', $parent_id));
		}
		
//		return array(
//				'where'=>'field_id=:field_id AND parent_id=:parent_id',
//				'bindParams'=>array(':field_id'=>$params['field_id'], ':parent_id'=>$parent_id),
//				'order'=>'sort'
//		);
	}
	
	
	protected function actionTree($params){
		
		$s = call_user_func(array($this->model,'model'));
		
		$stmt = $s->find(array(
				'where'=>'field_id=:field_id AND parent_id=:parent_id',
				'bindParams'=>array(
						'field_id'=>$params['field_id'], 
						'parent_id'=>$params['node']),
				'order'=>'sort',
			 'limit'=>0
		));
		
		$response=array();
		$models = $stmt->fetchAll();
		while($model = array_shift($models)){
			$node = array(
				'id'=>$model->id,
				'text'=>$model->name,
				'iconCls'=>'folder-default'
				);
			
			$record = $s->findSingle(array(
				'fields'=>'count(*) AS count',
				'where'=>'parent_id=:parent_id',
				'bindParams'=>array(
						'parent_id'=>$model->id),
				'order'=>'sort'
			));

			if(!$record->count){
				$node['children']=array();
				$node['expanded']=true;
			}
			$response[]=$node;
		}
		
		return $response;
	}
}


