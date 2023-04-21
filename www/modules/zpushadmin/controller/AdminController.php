<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: AdminController.php 18776 2014-04-07 11:39:03Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */


namespace GO\Zpushadmin\Controller;

use GO\Base\Controller\AbstractModelController;
use GO\Zpushadmin\Model\Device;
use GO\Zpushadmin\ZpushadminModule;

ZpushadminModule::includeZpushFiles();

class AdminController extends AbstractModelController {

	public function actionRemove($params){
		$device = $this->_loadDevice($params);
		return $device->remove() ?
			['success' => true] :
			['success' => false, 'feedback' =>  'Remove device: '.$device->device_id.' is not possible through a server error.'];
	}
	
//	public function actionWipe($params){
//		$device = $this->_loadDevice($params);
//		return $device->wipe() ?
//			['success' => true] :
//			['success' => false, 'feedback' =>  'Wipe of device: '.$device->device_id.' is not possible through a server error.'];
//	}
	
	public function actionResyncDevice($params){
		$device = $this->_loadDevice($params);
		return $device->resync() ?
			['success' => true] :
			['success' => false, 'feedback' => 'Resyncing of device: '.$device->device_id.' has failed.'];

	}
	
	private function _loadDevice($params){
		
		if(empty($params['deviceId']) || empty($params['username']))
			throw new \GO\Base\Exception\NotFound();
		
		$device = Device::findBy($params['deviceId'], $params['username']);
		
		if(empty($device))
			throw new \GO\Base\Exception\NotFound();
		
		return $device;
	}
}