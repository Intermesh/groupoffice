<?php
namespace GO\Dropbox\Controller;

use Exception;
use GO;
use GO\Base\Controller\AbstractJsonController;
use GO\Dropbox\DropboxModule;
use GO\Dropbox\Model\DropboxClient;
use GO\Dropbox\Model\DropboxUser;
use GO\Base\Db\FindParams;

class AuthController extends AbstractJsonController {

	protected function ignoreAclPermissions(){
		return array('*');
	}
	
	protected function allowGuests(){
		return array('*');
	}
	
	protected function actionStart(){
		
		$app = DropboxClient::getDropboxApp();
		$service = DropboxClient::getDropboxService($app);
		$authHelper = DropboxClient::getAuthHelper($service);
		
		$authUrl = $authHelper->getAuthUrl(DropboxModule::getCallbackUri());
		
		header("Location: ".$authUrl);
	}
	
	protected function actionDisconnect(){
		$app = DropboxClient::getDropboxApp();
		$service = DropboxClient::getDropboxService($app);
		$authHelper = DropboxClient::getAuthHelper($service);
		
		$authHelper->revokeAccessToken();
	}
	
	protected function actionCallback(){
		
		$this->render("externalHeader");
		
		if (isset($_GET['code']) && isset($_GET['state'])) {
			//Bad practice! No input sanitization!
			$code = $_GET['code'];
			$state = $_GET['state'];
		
		
			$app = DropboxClient::getDropboxApp();
			$service = DropboxClient::getDropboxService($app);
			$authHelper = DropboxClient::getAuthHelper($service);

			$userAccessToken  = $authHelper->getAccessToken($code, $state, DropboxModule::getCallbackUri());
			
			if($userAccessToken){
				$dbxUser = DropboxClient::getDropboxUser();
	
				$dbxUser->access_token = $userAccessToken->getToken();
				if($dbxUser->save()){
					echo "<h1>".GO::t('connected','dropbox')."</h1>";
					echo "<p>".GO::t('done','dropbox')."</p>";
					echo '<button onclick="window.close();">'.GO::t('cmdClose').'</button>';
				} else {
					echo "<h1>".GO::t('errorConnect','dropbox')."</h1>";
					echo '<button onclick="window.close();">'.GO::t('cmdClose').'</button>';
				}
			}
		}
		
		$this->render("externalFooter");
	}
	
	protected function actionWebHook(){
		
	}
	
	protected function actionTest(){
		
		$stmt = DropboxUser::model()->find(FindParams::newInstance()->select());

		foreach($stmt as $dropboxUser){
						
			try {
				DropboxClient::setDropboxUser($dropboxUser);
				DropboxClient::syncDropboxToGO();
				DropboxClient::syncGOToDropbox();
			} catch(Exception $e){
				
				var_dump($e);
				
			}
			
		}
	}
	
}