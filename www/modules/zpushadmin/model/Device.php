<?php

/**
 * Group-Office
 * 
 * Copyright Intermesh BV. 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @package GO.modules.zpushadmin.model
 * @version $Id: Device.php 21647 2016-09-22 13:34:46Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */

namespace GO\Zpushadmin\Model;

use go\core\model\Settings;

/**
 * The Device model
 *
 * @package GO.modules.zpushadmin.model
 * @property string $device_id
 * @property string $device_type
 * @property string $remote_addr
 * @property boolean $can_connect
 * @property int $ctime
 * @property int $mtime
 * @property boolean $new
 * @property string $username
 * @property string $comment
 * @property string $as_version
 */
class Device extends \GO\Base\Db\ActiveRecord {
	
	public $devicePhoneNumber;
	public $deviceModel;
	public $deviceImei;
	public $deviceName;
	public $deviceOS;
	public $deviceOSLanguage;
	public $deviceOperator;
	public $deviceOutboundSMS;
	public $deviceASVersion;
	public $deviceWiperequestOn;
	public $deviceWiperequestBy;
	public $deviceWiped;
	public $deviceErrors="";
		
	public function primaryKey() {
		return 'id';
	}

	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	protected function afterLoad() {
		$isLowerCaseNeeded = \GO\Zpushadmin\ZpushadminModule::checkZPushVersion('2.3');
		if($isLowerCaseNeeded)
			$this->device_id=strtolower($this->device_id);
		
		return parent::afterLoad();
	}

	static function findBy($id, $username) {
		return self::model()->findSingleByAttributes([
			'device_id' => $id,
			'username' => $username
		]);
	}

	static function requestAccess() {
		$device = Device::findBy(\Request::GetDeviceID(), \GO::user()->username);
		if(empty($device)){
			$device = new Device();
			$device->device_id = \Request::GetDeviceID();
			$device->device_type = \Request::GetDeviceType();
			$device->remote_addr = \Request::GetRemoteAddr();
			$device->username = \GO::user()->username;
			$device->can_connect = Settings::get()->activeSyncCanConnect;
		}
		$device->new = false;
		$device->forceSave(); // needed for updating the mtime field
		$device->save();

		return $device->can_connect;
	}
	
	/**
	 * Returns the table name
	 */
	 public function tableName() {
		 return 'zpa_devices';
	 }
		
	public function resync(){
		\GO\Zpushadmin\ZpushadminModule::includeZpushFiles();
		return \ZPushAdmin::ResyncDevice($this->username,$this->device_id);
	}
	
	public function remove(){
		\GO\Zpushadmin\ZpushadminModule::includeZpushFiles();
		return \ZPushAdmin::RemoveDevice($this->username,$this->device_id);
	}
	
	public function wipe(){
		\GO::debug('Zpushadmin::Device->wipe() called');
		\GO\Zpushadmin\ZpushadminModule::includeZpushFiles();
		\ZPushAdmin::WipeDevice(\GO::user()->username,$this->username,$this->device_id);
		return \ZPushAdmin::RemoveDevice($this->username,$this->device_id);
	}
	
	public function loadDetails(){
		\GO\Zpushadmin\ZpushadminModule::includeZpushFiles();
		\GO::debug('Zpushadmin::Device->loadDetails() called');
		\GO::debug('Zpushadmin::DeviceID = '.$this->device_id);
		\GO::debug('Zpushadmin::Username = '.$this->username);
		
		$data = \ZPushAdmin::GetDeviceDetails($this->device_id,$this->username);
		if($data){
			$this->devicePhoneNumber = $data->GetDevicePhoneNumber();
			$this->deviceModel = $data->GetDeviceModel();
			$this->deviceImei = $data->GetDeviceIMEI();
			$this->deviceName = $data->GetDeviceFriendlyName();
			$this->deviceOS = $data->GetDeviceOS();
			$this->deviceOSLanguage = $data->GetDeviceOSLanguage();
			$this->deviceOperator = $data->GetDeviceMobileOperator();
			$this->deviceOutboundSMS = $data->GetDeviceEnableOutboundSMS();
			$this->deviceASVersion = $data->GetASVersion();
			$this->deviceWiperequestOn = $data->GetWipeRequestedOn();
			$this->deviceWiperequestBy = $data->GetWipeRequestedBy();
			$this->deviceWiped = $data->GetWipeActionOn();
			$this->deviceErrors = $data->GetDeviceError();
			
			
			if ($data->GetDeviceError())
					$this->deviceErrors = $data->GetDeviceError()."\n";
			else if (!isset($data->ignoredmessages) || empty($data->ignoredmessages)) {
					$this->deviceErrors = "No errors known\n";
			}
			else {
					$this->deviceErrors .= sprintf("%d messages need attention because they could not be synchronized\n", count($data->ignoredmessages));
					foreach ($data->ignoredmessages as $im) {
							$info = "";
							if (isset($im->asobject->subject))
									$info .= sprintf("Subject: '%s'", $im->asobject->subject);
							if (isset($im->asobject->fileas))
									$info .= sprintf("FileAs: '%s'", $im->asobject->fileas);
							if (isset($im->asobject->from))
									$info .= sprintf(" - From: '%s'", $im->asobject->from);
							if (isset($im->asobject->starttime))
									$info .= sprintf(" - On: '%s'", strftime("%Y-%m-%d %H:%M", $im->asobject->starttime));
							$reason = $im->reasonstring;
							if ($im->reasoncode == 2)
									$reason = "Message was causing loop";
							$this->deviceErrors .= sprintf("\tBroken object:\t'%s' ignored on '%s'\n", $im->asclass,  strftime("%Y-%m-%d %H:%M", $im->timestamp));
							$this->deviceErrors .= sprintf("\tInformation:\t%s\n", $info);
							$this->deviceErrors .= sprintf("\tReason: \t%s (%s)\n", $reason, $im->reasoncode);
							$this->deviceErrors .= sprintf("\tItem/Parent id: %s/%s\n", $im->id, $im->folderid);
							$this->deviceErrors .= "\n";
					}
			}
			
			
			
			
			// Save the active sync version that is used to the database
			if($this->deviceASVersion != $this->as_version){
				$this->as_version = $this->deviceASVersion;
				$this->save();
			}
			
		}
		return true;
	}
	
	protected function afterDelete() {
		\GO\Zpushadmin\ZpushadminModule::includeZpushFiles();
		return \ZPushAdmin::RemoveDevice($this->username,$this->device_id);
	}
}
