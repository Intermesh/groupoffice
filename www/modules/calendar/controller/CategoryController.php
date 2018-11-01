<?php

namespace GO\Calendar\Controller;


class CategoryController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Calendar\Model\Category';
	
	protected function beforeStoreStatement(array &$response, array &$params, \GO\Base\Data\AbstractStore &$store, \GO\Base\Db\FindParams $storeParams) {
		
		//SELECT * FROM cal_categories t 
		//LEFT JOIN go_acl ON (`t`.`acl_id` = go_acl.acl_id AND (go_acl.user_id=57 OR go_acl.group_id IN (2))) 
		//WHERE t.calendar_id = 0 AND go_acl.acl_id IS NOT NULL OR t.calendar_id=56 
		
		$groupIds = \GO\Base\Model\User::getGroupIds(\GO::user()->id);
		
		$storeCriteria = $storeParams->getCriteria();
		$storeParams->joinAclFieldTable();
		
		if(!empty($params['global_categories']) && !empty($params['calendar_id'])){
			$storeCriteria->addCondition('calendar_id', 0,'=','t',false);
			//$storeCriteria->addCondition('acl_id', NULL,'IS NOT','go_acl');
			
			$storeCriteria->addCondition('calendar_id', $params['calendar_id'],'=','t',false);
		} elseif(!empty($params['calendar_id'])) {
			$storeCriteria->addCondition('calendar_id', $params['calendar_id']);
		} elseif(!empty($params['fetch_all'])) {
			
		} else {
			$storeCriteria->addCondition('calendar_id', 0);
		}
		
		$storeParams->ignoreAcl();
		
		return parent::beforeStoreStatement($response, $params, $store, $storeParams);
	}
}
