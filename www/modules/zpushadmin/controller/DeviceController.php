<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: DeviceController.php 18776 2014-04-07 11:39:03Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

/**
 * 
 * The Device controller
 * 
 */

namespace GO\Zpushadmin\Controller;


class DeviceController extends \GO\Base\Controller\AbstractModelController {
	
	protected $model = 'GO\Zpushadmin\Model\Device';
	
	
	protected function beforeDisplay(&$response, &$model, &$params) {
		$model->loadDetails();
		return parent::beforeDisplay($response, $model, $params);
	}
		
	protected function beforeStoreStatement(array &$response, array &$params, \GO\Base\Data\AbstractStore &$store, \GO\Base\Db\FindParams $storeParams) {
		
		$storeParams->select('*');
		
		return parent::beforeStoreStatement($response, $params, $store, $storeParams);
	}
	
	protected function afterDisplay(&$response, &$model, &$params) {
		
		$response['data']['deviceWiperequestOn'] = \GO\Base\Util\Date\DateTime::fromUnixtime($model->deviceWiperequestOn)->format();
		$response['data']['deviceWiped'] = \GO\Base\Util\Date\DateTime::fromUnixtime($model->deviceWiped)->format();
		
		$response['data']['deviceErrors'] = nl2br($model->deviceErrors);
		
		return parent::afterDisplay($response, $model, $params);
	}
	
	
}
