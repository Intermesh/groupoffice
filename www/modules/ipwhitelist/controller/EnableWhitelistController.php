<?php

namespace GO\Ipwhitelist\Controller;


//class EnableWhitelistController extends \GO\Base\Controller\AbstractJsonController {
//	
//	protected function actionSetWhitelist($params) {
//		
//		$enable = $params['enable_whitelist'];
//		$groupId = $params['group_id'];
//		
//		if ($enable) {
//			
//			$enableWhitelistModel = \GO\Ipwhitelist\Model\EnableWhitelist::model()->findByPk($groupId);
//			if (!$enableWhitelistModel) {
//				$enableWhitelistModel = new \GO\Ipwhitelist\Model\EnableWhitelist();
//				$enableWhitelistModel->group_id = $groupId;
//				$enableWhitelistModel->save();
//			}
//			
//		} else {
//			$enableWhitelistModel = \GO\Ipwhitelist\Model\EnableWhitelist::model()->findByPk($groupId);
//			if ($enableWhitelistModel)
//				$enableWhitelistModel->delete();
//		}
//		
//		echo json_encode(array('success'=>true));
//		
//	}
//	
//}
?>
