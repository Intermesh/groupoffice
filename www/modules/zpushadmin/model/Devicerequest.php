<?php


namespace GO\Zpushadmin\Model;


class Devicerequest extends \GO\Base\Model {
	
	private $_device = false;
	
	public function __construct() {
		$settings = Settings::load();
		
		$this->_device = Device::model()->findSingleByAttributes(array('device_id'=>\Request::GetDeviceID(),'username'=>\GO::user()->username));
		
		if(empty($this->_device)){
			$this->_device = new Device();

			$this->_device->device_id = \Request::GetDeviceID();
			$this->_device->device_type = \Request::GetDeviceType();
			$this->_device->remote_addr = \Request::GetRemoteAddr();
			$this->_device->username = \GO::user()->username;

			if($settings->zpushadmin_can_connect)
				$this->_device->can_connect = true;
			
			$this->_device->save();
		} else {			
			$this->_device->touch(); // needed for updating the mtime field
		}

	}
	
	public function setNotNew(){
		if($this->_device->new){
			$this->_device->new = false;
			return $this->_device->save();
		} else {
			return true;
		}
	}
	
	public function hasAccess(){
		
		// Return the canConnect value of the savedRequest AR
		return $this->_device->can_connect?true:false;
	}
	
}
