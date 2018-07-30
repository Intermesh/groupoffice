<?php

namespace GO\Files\Controller;


class BookmarkController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Files\Model\Bookmark';
	
	protected function beforeSubmit(&$response, &$model, &$params) {
		// See if folder with this ID can be accessed.
		$folderModel = \GO\Files\Model\Folder::model()->findByPk($params['folder_id']);
		
		if (empty($folderModel))
			return false;		
		
		$params['user_id'] = $model->user_id = \GO::user()->id;
		
		$response['user_id'] = \GO::user()->id;
		$response['folder_id'] = $folderModel->id;
		
		return parent::beforeSubmit($params, $folderModel, $params);
	}
	
	public function formatStoreRecord($record, $model, $store) {
		$record['folder_id'] = $model->folder_id;
		$record['name'] = $model->folder->name;
		return parent::formatStoreRecord($record, $model, $store);
	}
	
	protected function actionDelete($params) {
		
		$pk = array('user_id' => \GO::user()->id, 'folder_id' => $params['folder_id']);
		
		
		$model = \GO\Files\Model\Bookmark::model()->findByPk($pk);
		
//		$response = array();
//		$response = $this->beforeDelete($response, $model, $params);
		$response['success'] = $model->delete();
//		$response = $this->afterDelete($response, $model, $params);

		return $response;
	}
	
	
	protected function beforeStoreStatement(array &$response, array &$params, \GO\Base\Data\AbstractStore &$store, \GO\Base\Db\FindParams $storeParams) {
		$storeParams
            ->select('`t`.`folder_id`,`t`.`user_id`,`f`.`name`')
            ->joinModel(array(
              'model'=>'GO\Files\Model\Folder',
              'localTableAlias'=>'t',
              'localField'=>'folder_id',
              'foreignField'=>'id',
              'tableAlias'=>'f'
            ))
			->getCriteria()->addCondition('user_id',\GO::user()->id);
		return parent::beforeStoreStatement($response, $params, $store, $storeParams);
	}
	
}
?>
