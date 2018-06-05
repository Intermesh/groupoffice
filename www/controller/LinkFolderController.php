<?php


namespace GO\Core\Controller;


class LinkFolderController extends \GO\Base\Controller\AbstractModelController {

	protected $model = "GO\Base\Model\LinkFolder";

	protected function actionTree($params) {

		$response = array();

		$findParams = \GO\Base\Db\FindParams::newInstance();

		$folder_id = isset($params['node']) && substr($params['node'], 0, 10) == 'lt-folder-' ? (substr($params['node'], 10)) : 0;

		if (!empty($folder_id))
			$findParams->getCriteria()->addCondition('parent_id', $folder_id);
		else
			$findParams->getCriteria()
							->addCondition('model_id', $params['model_id'])
							->addCondition('model_type_id', \GO\Base\Model\ModelType::model()->findByModelName($params['model_name']));


		$stmt = \GO\Base\Model\LinkFolder::model()->find($findParams);

		while ($model = $stmt->fetch()) {
			$node = array(
					'id' => 'lt-folder-' . $model->id,
					'text' => $model->name,
					'iconCls' => 'folder-default'
			);

			if (!$model->hasChildren()) {
				$node['expanded'] = true;
				$node['children'] = array();
			}

			$response[] = $node;
		}

		return $response;
	}

	protected function beforeSubmit(&$response, &$model, &$params) {
		if (empty($params['parent_id'])) {
			$model->model_type_id = \GO\Base\Model\ModelType::model()->findByModelName($params['model_name']);
		} else {
			unset($params['model_id']);
		}
		unset($params['model_name']);

		return parent::beforeSubmit($response, $model, $params);
	}

	protected function actionMoveLinks($params) {
		$moveLinks = json_decode($params['selections'], true);
		$target = json_decode($params['target']);

		$response['moved_links'] = array();

		foreach ($moveLinks as $modelNameAndId) {
			$link = explode(':', $modelNameAndId);
			$modelName = $link[0];
			$modelId = $link[1];

			if ($modelName == 'GO\Base\Model\LinkFolder') {
				
				$moveFolder = \GO\Base\Model\LinkFolder::model()->findByPk($modelId);
				$moveFolder->parent_id=intval($target->folder_id);
				$moveFolder->save();

			} else {
				
				$moveModel = \GO::getModel($modelName)->findByPk($modelId);
				
				$targetModel = \GO::getModel($target->model_name)->findByPk($target->model_id);
				$targetModel->updateLink($moveModel, array('folder_id'=>intval($target->folder_id)));
			}
		}
		$response['success'] = true;
		
		return $response;
	}

}
