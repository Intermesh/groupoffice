<?php

namespace GO\Core\Controller;


class AdvancedSearchController extends \GO\Base\Controller\AbstractModelController {
	
	protected $model = 'GO\Base\Model\AdvancedSearch';

//	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
//		
//		$columnModel->formatColumn('data', '$model->getData()');
//		
//		return parent::formatColumns($columnModel);
//	}
	
	public function formatStoreRecord($record, $model, $store) {
		
		$record['data'] = $model->getData();
		
		return parent::formatStoreRecord($record, $model, $store);
	}
	
	protected function getStoreParams($params) {	
		
		$storeParams = \GO\Base\Db\FindParams::newInstance();
		$storeParams->getCriteria()->addCondition('model_name', $params['model_name']);
		$storeParams->select('t.*');
		
		return $storeParams;
	}
}