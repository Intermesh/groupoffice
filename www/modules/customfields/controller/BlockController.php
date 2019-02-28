<?php

namespace GO\Customfields\Controller;


class BlockController extends \GO\Base\Controller\AbstractJsonController{

	protected function actionManageStore($params) {
		
		$columnModel = new \GO\Base\Data\ColumnModel(\GO\Customfields\Model\Block::model());
		$columnModel->formatColumn('col_id', '"col_".$model->customField->databaseName', array(), 'field_id');
		$columnModel->formatColumn('customfield_name', '$model->customField->name', array(), 'field_id');
		$columnModel->formatColumn('customfield_datatype', '$model->customField->datatype', array(), 'field_id');
		$columnModel->formatColumn('extendsModel', '$model->customField->category->extendsModel', array(), 'field_id');

		$findParams = \GO\Base\Db\FindParams::newInstance()
			->joinModel(array(
				'model'=>'GO\Customfields\Model\Field',
				'localTableAlias'=>'t',
				'localField'=>'field_id',
				'foreignField'=>'id',
				'tableAlias'=>'cf',
				'type'=>'INNER'
			))
			->joinModel(array(
				'model'=>'GO\Customfields\Model\Category',
				'localTableAlias'=>'cf',
				'localField'=>'fieldSetId',
				'foreignField'=>'id',
				'tableAlias'=>'cfcat',
				'type'=>'INNER'
			))
			->join(
				'core_acl',
				\GO\Base\Db\FindCriteria::newInstance()->addRawCondition('a.id', 'cfcat.aclId'),
				'a'
				)
			->join(
				'core_user_group',
				\GO\Base\Db\FindCriteria::newInstance()->addRawCondition('a.ownedBy', 'ug.groupId'),
				'ug',
				'LEFT'
			)
			->criteria(
				\GO\Base\Db\FindCriteria::newInstance()
					->addCondition('ownedBy', \GO::user()->id, '=', 'a', false)
					->addCondition('userId', \GO::user()->id, '=', 'ug', false)
			);
		
		$store = new \GO\Base\Data\DbStore('GO\Customfields\Model\Block', $columnModel, $params, $findParams);

		echo $this->renderStore($store);
		
	}
	
	protected function actionSubmit($params) {
		
		if (!empty($params['id']))
			$blockModel = \GO\Customfields\Model\Block::model()->findByPk($params['id']);
		else
			$blockModel = new \GO\Customfields\Model\Block();
		
		$blockModel->setAttributes($params);
		$blockModel->save();
		
		echo $this->renderSubmit($blockModel);
		
	}
	
	protected function actionLoad($params) {
		
		if (!empty($params['id']))
			$blockModel = \GO\Customfields\Model\Block::model()->findByPk($params['id']);
		else
			$blockModel = new \GO\Customfields\Model\Block();
		
		$remoteComboFields = array('field_id' => '"[".\GO::t($model->customField->category->extendsModel,"customfields")."] ".$model->customField->category->name." : ".$model->customField->name." (".$model->databaseName.")"');
		
		echo $this->renderForm($blockModel,$remoteComboFields);
		
	}

	protected function actionEnableStore($params) {
				
		$columnModel = new \GO\Base\Data\ColumnModel(\GO\Customfields\Model\Block::model());
		$columnModel->formatColumn('col_id', '$model->customField->databaseName', array(), 'field_id');
		$columnModel->formatColumn('customfield_name', '$model->customField->name', array(), 'field_id');
		$columnModel->formatColumn('customfield_datatype', '$model->customField->datatype', array(), 'field_id');
		$columnModel->formatColumn('extendsModel', '$model->customField->category->extendsModel', array(), 'field_id');
		$columnModel->formatColumn('enabled', '!empty($model->enabled_block_id)', array(), 'enabled_block_id');

		$findParams = \GO\Base\Db\FindParams::newInstance()
			->select('t.*,eb.block_id AS enabled_block_id')
			->joinModel(array(
				'model'=>'GO\Customfields\Model\EnabledBlock',
				'localTableAlias'=>'t',
				'localField'=>'id',
				'foreignField'=>'block_id',
				'tableAlias'=>'eb',
				'type'=>'LEFT',
				'criteria'=>\GO\Base\Db\FindCriteria::newInstance()
					->addCondition('model_type_name',$params['model_name'],'=','eb')
					->addCondition('model_id',$params['model_id'],'=','eb')
			))
			->joinModel(array(
				'model'=>'GO\Customfields\Model\Field',
				'localTableAlias'=>'t',
				'localField'=>'field_id',
				'foreignField'=>'id',
				'tableAlias'=>'cf',
				'type'=>'INNER'
			))
			->joinModel(array(
				'model'=>'GO\Customfields\Model\Category',
				'localTableAlias'=>'cf',
				'localField'=>'fieldSetId',
				'foreignField'=>'id',
				'tableAlias'=>'cfcat',
				'type'=>'INNER'
			))
			->join(
				'core_acl',
				\GO\Base\Db\FindCriteria::newInstance()->addRawCondition('a.id', 'cfcat.aclId'),
				'a'
				)
			->join(
				'core_user_group',
				\GO\Base\Db\FindCriteria::newInstance()->addRawCondition('a.ownedBy', 'ug.groupId'),
				'ug',
				'LEFT'
			)
			->criteria(
				\GO\Base\Db\FindCriteria::newInstance()
					->addCondition('ownedBy', \GO::user()->id, '=', 'a', false)
					->addCondition('userId', \GO::user()->id, '=', 'ug', false)
			);
		
		$store = new \GO\Base\Data\DbStore('GO\Customfields\Model\Block', $columnModel, $params, $findParams);

		echo $this->renderStore($store);
		
	}
	
	protected function actionEnable($params) {
		
		$response['success'] = true;
		
		$enableBlockModel = \GO\Customfields\Model\EnabledBlock::model()
			->findSingleByAttributes(array(
				'block_id' => $params['block_id'],
				'model_id' => $params['model_id'],
				'model_type_name' => $params['model_name']
			));
		
		if (!empty($params['enable']) && $params['enable']!=='false') {
			
			if (!$enableBlockModel) {
				$enableBlockModel = new \GO\Customfields\Model\EnabledBlock();
				$enableBlockModel->block_id = $params['block_id'];
				$enableBlockModel->model_id = $params['model_id'];
				$enableBlockModel->model_type_name = $params['model_name'];
				$response['success'] = $enableBlockModel->save();
			}
			
		} else {
			
			if ($enableBlockModel)
				$response['success'] = $enableBlockModel->delete();
		}
		
		echo $this->renderJson($response);
		
	}
	
}
