<?php

namespace GO\Ipwhitelist\Controller;


class IpAddressController extends \GO\Base\Controller\AbstractJsonController {
	
	protected function actionStore($params) {
		
		$groupId = $params['group_id'];
		
		$columnModel = new \GO\Base\Data\ColumnModel(\GO\Ipwhitelist\Model\IpAddress::model());
		
		$storeFindParams = 
			\GO\Base\Db\FindParams::newInstance()
				->criteria(\GO\Base\Db\FindCriteria::newInstance()
					->addCondition('group_id',$groupId)
				)
				->order('ip_address');
		
		$store = new \GO\Base\Data\DbStore('GO\Ipwhitelist\Model\IpAddress', $columnModel, $params, $storeFindParams);
		echo $this->renderStore($store);
		
	}
	
	protected function actionLoad($params) {
		
		$model = \GO\Ipwhitelist\Model\IpAddress::model()->createOrFindByParams($params);
		
//		$remoteComboFields = array(
//			'group_id' => '$model->group->name',
//			'user_id' => '$model->user->name'
//		);

		echo $this->renderForm($model);//, $remoteComboFields);
		
	}
	
	protected function actionSubmit($params) {
		
		$model = \GO\Ipwhitelist\Model\IpAddress::model()->createOrFindByParams($params);

		$model->setAttributes($params);
		$model->save();

		echo $this->renderSubmit($model);
		
	}
	
}
?>
