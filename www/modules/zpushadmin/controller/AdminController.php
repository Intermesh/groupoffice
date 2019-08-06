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

/**
 * 
 * The Admin controller
 * 
 */

namespace GO\Zpushadmin\Controller;

class AdminController extends \GO\Base\Controller\AbstractModelController {
	
	public function __construct() {
		parent::__construct();
		\GO\Zpushadmin\ZpushadminModule::includeZpushFiles();
	}
	
	
	
	public function actionRemove($params){
		$device = $this->_loadDevice($params);
		
		$success = $device->remove();
	
		$response = array();
		$response['success']=$success;
		
		if(!$success)
			$response['feedback'] = 'Remove device: '.$device->device_id.' is not possible through a server error.';
		
		return $response;
	}
	
//	public function actionWipe($params){
//		$device = $this->_loadDevice($params);
//		
//		\GO::debug('Zpushadmin::Device->wipe() called from controller');
//		
//		$success = $device->wipe();
//	
//		$response = array();
//		$response['success']=$success;
//		
//		if(!$success)
//			$response['feedback'] = 'Wipe of device: '.$device->device_id.' is not possible through a server error.';
//		
//		return $response;
//	}
	
	public function actionResyncDevice($params){
		$device = $this->_loadDevice($params);
		
		$success = $device->resync();
	
		$response = array();
		$response['success']=$success;
		
		if(!$success)
			$response['feedback'] = 'Resyncing of device: '.$device->device_id.' has failed.';
		
		return $response;
	}
	
	public function actionDeviceDetails($params){
		$response = array();
		$response['success']=false;
		
		$device = $this->_loadDevice($params);
	
		$details = $device->getDetails();
		var_dump($details);
		
		if($details){
			$response['success'] = true;
			$response['details'] = $details->GetData();
		}
		
		return $response;
	}
	
	
	
	
	private function _loadDevice($params){
		
		if(empty($params['deviceId']) || empty($params['username']))
			throw new \GO\Base\Exception\NotFound();
		
		$device = \GO\Zpushadmin\Model\Device::model()->findSingleByAttributes(array('device_id'=>$params['deviceId'],'username'=>$params['username']));
		
		if(empty($device))
			throw new \GO\Base\Exception\NotFound();
		
		return $device;
	}
	
	
}
