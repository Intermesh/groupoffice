<?php


namespace GO\Ipwhitelist;


class IpwhitelistModule extends \GO\Base\Module{
	
	public function depends() {
		return array("groups");
	}

	public static function initListeners() {
		$invoiceController = new \GO\Core\Controller\AuthController();
		$invoiceController->addListener('beforelogin', "GO\Ipwhitelist\IpwhitelistModule", "checkIpAddress");
		$groupController = new \GO\Groups\Controller\GroupController();
		$groupController->addListener('load', "GO\Ipwhitelist\IpwhitelistModule", "getWhitelistEnabled");
		$groupController->addListener('submit', 'GO\Ipwhitelist\IpwhitelistModule', 'setWhitelist');
	}
	
	public function autoInstall() {
		return false;
	}
	
	public function adminModule() {
		return true;
	}
	
	public static function checkIpAddress( array &$params, array &$response ) {
		
		$oldIgnoreAcl = \GO::setIgnoreAclPermissions();
		$userModel = \GO\Base\Model\User::model()->findSingleByAttribute('username',$params['username']);
		if (!$userModel)
			return true;
				
		$allowedIpAddresses = array();//"127.0.0.1");
		$whitelistIpAddressesStmt = Model\IpAddress::model()->find(
			\GO\Base\Db\FindParams::newInstance()
				->select('t.ip_address')
				->joinModel(array(
					'model'=>'GO\Ipwhitelist\Model\EnableWhitelist',
					'localTableAlias'=>'t',
					'localField'=>'group_id',
					'foreignField'=>'group_id',
					'tableAlias'=>'ew',
					'type'=>'INNER'
				))
				->joinModel(array(
					'model'=>'GO\Base\Model\UserGroup',
					'localTableAlias'=>'ew',
					'localField'=>'group_id',
					'foreignField'=>'group_id',
					'tableAlias'=>'usergroup',
					'type'=>'INNER'
				))
				->criteria(\GO\Base\Db\FindCriteria::newInstance()
					->addCondition('user_id',$userModel->id,'=','usergroup')
				)
		);
		if (!empty($whitelistIpAddressesStmt) && $whitelistIpAddressesStmt->rowCount() > 0) {
			
			foreach ($whitelistIpAddressesStmt as $ipAddressModel) {
//				$enabledWhitelistModel = Model\EnableWhitelist::model()->findByPk($groupModel->id);
//				if (!empty($enabledWhitelistModel)) {
//					$ipAddressesStmt = Model\IpAddress::model()->findByAttribute('group_id',$groupModel->id);
//					foreach ($ipAddressesStmt as $ipAddressModel) {
						if (!in_array($ipAddressModel->ip_address,$allowedIpAddresses))
							$allowedIpAddresses[] = $ipAddressModel->ip_address;
//					}
//				}
			}
			
		}
		
		\GO::setIgnoreAclPermissions($oldIgnoreAcl);
		
		if (count($allowedIpAddresses)>0 && !in_array($_SERVER['REMOTE_ADDR'],$allowedIpAddresses)) {
			$response['feedback'] = sprintf(\GO::t('wrongLocation','ipwhitelist'),$_SERVER['REMOTE_ADDR']);
			$response['success'] = false;
			return false;
		}
		
		return true;
	}
	
	public static function setWhitelist(\GO\Groups\Controller\GroupController $groupController, array &$response, \GO\Base\Model\Group $groupModel, array &$params, array $modifiedAttributes) {
				
		$enable = $params['enable_whitelist'];
		$groupId = $groupModel->id;
		
		if ($enable) {
			
			$enableWhitelistModel = Model\EnableWhitelist::model()->findByPk($groupId);
			if (!$enableWhitelistModel) {
				$enableWhitelistModel = new Model\EnableWhitelist();
				$enableWhitelistModel->group_id = $groupId;
				$enableWhitelistModel->save();
			}
			
		} else {
			$enableWhitelistModel = Model\EnableWhitelist::model()->findByPk($groupId);
			if ($enableWhitelistModel)
				$enableWhitelistModel->delete();
		}
	}

	public static function getWhitelistEnabled(\GO\Groups\Controller\GroupController $groupController, array &$response, \GO\Base\Model\Group $groupModel, array &$params) {
		$enabledWhitelistModel = Model\EnableWhitelist::model()->findByPk($groupModel->id);
		$response['data']['enable_whitelist'] = !empty($enabledWhitelistModel);
	}
	
}