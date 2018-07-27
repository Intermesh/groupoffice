<?php

namespace GO\Comments\Controller;


class CommentController extends \GO\Base\Controller\AbstractModelController{

	protected $model = 'GO\Comments\Model\Comment';
	


	protected function getStoreParams($params){

		$sort = 'ctime';
		$dir = 'DESC';
		if(!empty($params['sort'])) {
			$sort = $params['sort'];
			$dir = $params['dir'];
		}
		
		return \GO\Base\Db\FindParams::newInstance()
						->ignoreAcl()	
						->select('t.*, category.name as category_name')
						->order($sort,$dir)
						->joinRelation('category', 'LEFT')
						->criteria(
										\GO\Base\Db\FindCriteria::newInstance()
											->addCondition('model_id', $params['model_id'])
											->addCondition('model_type_id', \GO\Base\Model\ModelType::model()->findByModelName($params['model_name']))										
										);
	}
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		$columnModel->formatColumn('user_name','$model->user->name');
		$columnModel->formatColumn('category_name','$model->category->name', array(), 'category_name');
		return parent::formatColumns($columnModel);
	}
	
	protected function beforeStore(&$response, &$params, &$store) {
		$model = \GO::getModel($params['model_name']);
		
						
		if(is_a($model, \go\core\orm\Entity::class)) {
			$params['model_name'] = get_class($model);
			$model = $model::findById($params['model_id']);
			$response['permisson_level']=$model->getPermissionLevel();
			$response['write_permission']=$model->hasPermissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION);
		} else
		{
		
			$model = $model->findByPk($params['model_id']);
			////\GO\Base\Model\SearchCacheRecord::model()->findByPk(array('model_id'=>$params['model_id'], 'model_type_id'=>\GO\Base\Model\ModelType::model()->findByModelName($params['model_name'])));

			$response['permisson_level']=$model->permissionLevel;
			$response['write_permission']=$model->checkPermissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION);
		}
		if(!$response['permisson_level'])
		{
			throw new AccessDeniedException();
		}
		return $response;
	}
	
	protected function beforeSubmit(&$response, &$model, &$params) {
		
		$entityType = strpos($params['model_name'], "\\") === false ? \go\core\orm\EntityType::findByName($params['model_name']) : \go\core\orm\EntityType::findByClassName($params['model_name']);
		
		$params['model_type_id']=$entityType->getId();
		
		return parent::beforeSubmit($response, $model, $params);
	}
	
	protected function afterSubmit(&$response, &$model, &$params, $modifiedAttributes) {
		$modelTypeModel = \GO\Base\Model\ModelType::model()->findSingleByAttribute('id',$model->model_type_id);
		if ($modelTypeModel->model_name == 'GO\Addressbook\Model\Contact') {
			$modelWithComment = \GO::getModel($modelTypeModel->model_name)->findByPk($model->model_id);
			$modelWithComment->setAttribute('action_date',\GO\Base\Util\Date::to_unixtime($params['action_date']));
			$modelWithComment->save();
		}
		return parent::afterSubmit($response, $model, $params, $modifiedAttributes);
	}
	
	protected function afterLoad(&$response, &$model, &$params) {
		$modelTypeModel = \GO\Base\Model\ModelType::model()->findSingleByAttribute('id',$model->model_type_id);
		if ($modelTypeModel->model_name == 'GO\Addressbook\Model\Contact') {
			$modelWithComment = \GO::getModel($modelTypeModel->model_name)->findByPk($model->model_id);
			$actionDate = $modelWithComment->getAttribute('action_date');
			$response['data']['action_date'] = \GO\Base\Util\Date::get_timestamp($actionDate,false);
		}
		return parent::afterLoad($response, $model, $params);
	}
	
	protected function actionCombinedStore($params) {
		$response = array(
			'success' => true,
			'total' => 0,
			'results' => array()
		);

		$cm = new \GO\Base\Data\ColumnModel();
		$cm->setColumnsFromModel(\GO::getModel('GO\Comments\Model\Comment'));
		
		$store = \GO\Base\Data\Store::newInstance($cm);
		
		$storeParams = $store->getDefaultParams($params)->mergeWith($this->getStoreParams($params));
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
			->select('t.*,type.model_name')
			->joinModel(array(
				'model' => 'GO\Base\Model\ModelType',
				'localTableAlias' => 't',
				'localField' => 'model_type_id',
				'foreignField' => 'id',
				'tableAlias' => 'type'
			));

		$findParams->mergeWith($storeParams);
		
		$store->setStatement(\GO\Comments\Model\Comment::model()->find($findParams));
		return $store->getData();
//						
//		return $response;
	}
	
	protected function afterDisplay(&$response, &$model, &$params){
		
		$modelType = \GO\Base\Model\ModelType::model()->findByPk($model->model_type_id);
		
		$scModel = \GO\Base\Model\SearchCacheRecord::model()->findByPk(array(
			'model_id'=>$model->model_id,
			'model_type_id'=>$model->model_type_id
		));
		
		if(!isset($response['data']['parent'])){
			$response['data']['parent'] = array();
		}
		
		if($scModel){
			$response['data']['parent']['name'] = $scModel->name;
		} 
		
		$response['data']['parent']['model_type']= $modelType?$modelType->model_name:false;
		$response['data']['parent']['model_id'] = $model->model_id;
		
		$response['data']['comments']= $model->comments;
		$response['data']['category_name']= $model->category?$model->category->name:'';

		$response['data']['short'] = (strlen($model->comments) > 13) ? substr($model->comments,0,10).'...' : $model->comments;
		
		return $response;
	}	
}
