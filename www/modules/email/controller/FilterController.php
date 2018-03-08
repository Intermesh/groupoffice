<?php


namespace GO\Email\Controller;


class FilterController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Email\Model\Filter';

	protected function beforeStore(&$response, &$params, &$store) {

		$store->setDefaultSortOrder('priority');

		return parent::beforeStore($response, $params, $store);
	}

	protected function getStoreParams($params) {
		
	
		$findParams = \GO\Base\Db\FindParams::newInstance();
		$findParams->getCriteria()
						->addCondition("account_id", $params['account_id']);
	
		return $findParams;
	}
	protected function actionSaveSort($params){		
		$fields = json_decode($params['filters'], true);

		foreach ($fields as $id=>$sort) {
			$model = \GO\Email\Model\Filter::model()->findByPk($id);
			$model->priority=$sort;
			$model->save();
		}		
		
		return array('success'=>true);
	}	
	

}
