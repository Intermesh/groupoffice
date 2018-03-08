<?php

namespace GO\Users\Controller;


class GroupController extends \GO\Base\Controller\AbstractJsonController{
	
	protected function actionStore($params) {
		
		$columnModel = new \GO\Base\Data\ColumnModel('GO\Base\Model\Group');
		
		$store = new \GO\Base\Data\DbStore('GO\Base\Model\Group', $columnModel, $params);
		$store->defaultSort = array('name');
		$store->multiSelectable('users-groups-panel',null,'',array(),false);
		
		echo $this->renderStore($store);
		
	}
	
}