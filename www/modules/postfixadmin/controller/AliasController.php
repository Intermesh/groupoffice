<?php


namespace GO\Postfixadmin\Controller;


class AliasController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Postfixadmin\Model\Alias';
	
	
	protected function beforeStoreStatement(array &$response, array &$params, \GO\Base\Data\AbstractStore &$store, \GO\Base\Db\FindParams $storeParams) {
		$storeParams
			->select('t.*')
			->criteria(
				\GO\Base\Db\FindCriteria::newInstance()
					->addCondition('domain_id',$params['domain_id'])
			);
		return parent::beforeStoreStatement($response, $params, $store, $storeParams);
	}
	
	protected function afterLoad(&$response, &$model, &$params) {
		
		$response['data']['domain_name'] = $model->domain?$model->domain->domain:"";
		
		if(isset($response['data']['address']) && strpos($response['data']['address'], '@') !== false) {
			$response['data']['address'] = str_replace('@'.$response['data']['domain_name'], "", $response['data']['address']);
		}
		if($model->isNew) {
			$response['data']['active'] = true;
		}
			 
		
		return parent::afterLoad($response, $model, $params);
	}
	
	protected function beforeSubmit(&$response, &$model, &$params) {
		
		if(isset($params['address'])){
			// Check if the address has a @ in it.
			// If not, then attach the domain to it.
			if (strpos($params['address'], '@') === false) {
				$domain = $model->domain;
				if($domain){
					$params['address'].= '@'.$domain->domain;
				}
			}
		}

		return parent::beforeSubmit($response, $model, $params);
	}

}

