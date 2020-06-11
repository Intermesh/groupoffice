<?php


namespace GO\Postfixadmin\Controller;


class MailboxController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Postfixadmin\Model\Mailbox';
	
	
	protected function allowGuests() {
		return array("cacheusage","setpassword","submit"); //handled by serverclient_token
	}
		
	protected function getStoreParams($params) {
		return \GO\Base\Db\FindParams::newInstance()
						->criteria(\GO\Base\Db\FindCriteria::newInstance()
				->addCondition('domain_id',$params['domain_id']));		
	}
	
	protected function afterLoad(&$response, &$model, &$params) {
		if($model->isNew)
			$model->quota=$model->domain->default_quota;
		
	
		$response['data']['password'] = '';
		$response['data']['quota'] = \GO\Base\Util\Number::localize($model->quota/1024);
		$response['data']['domain']='@'.$model->domain->domain;
		
		if(isset($response['data']['username']))
			$response['data']['username']=str_replace($response['data']['domain'],"", $response['data']['username']);
		return $response;
	}
	
	
	protected function actionSetPassword($params){
		
		if(!\GO::user()){
			if(empty($params['serverclient_token']) || $params['serverclient_token']!=\GO::config()->serverclient_token){
				throw new \GO\Base\Exception\AccessDenied();
			}else
			{
				\GO::session()->runAsRoot();
			}
		}
		
		$mailbox = \GO\Postfixadmin\Model\Mailbox::model()->findSingleByAttributes(array(
				"username"=>$params["username"]				
		));

		$response['success']=true;
		
		if($mailbox){
			$mailbox->password=$params["password"];
			$response['success'] = $mailbox->save()===true;
			if (!$response['success']) {
				$validateErrors = $mailbox->getValidationErrors();
				$response['feedback'] = implode('<br />',$validateErrors);
			}
		}
		
		return $response;
	}
	
	protected function beforeSubmit(&$response, &$model, &$params) {
		

		if(!\GO::user()){
			if(empty($params['serverclient_token']) || $params['serverclient_token']!=\GO::config()->serverclient_token){
				throw new \GO\Base\Exception\AccessDenied();
			}else
			{
				\GO::session()->runAsRoot();
			}
		}

		
		if(isset($params['domain_id']))
			$domainModel = \GO\Postfixadmin\Model\Domain::model()->findByPk($params['domain_id']);
		else {
			$domainModel = \GO\Postfixadmin\Model\Domain::model()->findSingleByAttribute("domain", $params['domain']); //serverclient module doesn't know the domain_id. It sends the domain name as string.
			if(!$domainModel){
				//todo create new domain
				$domainModel = new	\GO\Postfixadmin\Model\Domain();
				$domainModel->domain = $params['domain'];
				$domainModel->user_id = \GO::user()->id;
				$domainModel->save();
			}
			$params['domain_id']=$domainModel->id;
			
			$model->quota = $domainModel->default_quota;
		}
		
		if(isset($params['quota'])){
			$model->quota=  \GO\Base\Util\Number::unlocalize($params['quota'])*1024;
			unset($params['quota']);
		}
		
		if ($params['password']!=$params['password2'])
			throw new \Exception(\GO::t("The passwords didn't match"));
		
		if(empty($params['password']))
			unset($params['password']);
		
		if(isset($params['username']))
			$params['username'] .= '@'.$domainModel->domain;
		
		if ($model->isNew) {
			
			//Create alias for each username so it's possible to add a catch all alias because aliases have priority over usernames.
			$aliasModel = \GO\Postfixadmin\Model\Alias::model()->findSingleByAttribute('address', $params['username']);
			if (empty($aliasModel)) {
				$aliasModel = new \GO\Postfixadmin\Model\Alias();
			}
			$aliasModel->domain_id = $params['domain_id'];
			$aliasModel->address = $params['username'];
			$aliasModel->goto = $params['username'];
			$aliasModel->save();
			
			
			if(!empty($params['alias']) && $params['alias']!=$params['username']){
				$aliasModel = \GO\Postfixadmin\Model\Alias::model()->findSingleByAttribute('address', $params['alias']);
				if (empty($aliasModel)) {
					$aliasModel = new \GO\Postfixadmin\Model\Alias();
				}
				$aliasModel->domain_id = $params['domain_id'];
				$aliasModel->address = $params['alias'];
				$aliasModel->goto = $params['username'];
				$aliasModel->save();
			}
		}
	}
	
	public function formatStoreRecord($record, $model, $store) {
		$record['usage'] = \GO\Base\Util\Number::formatSize($model->usage*1024);
		$record['quota'] = \GO\Base\Util\Number::formatSize($model->quota*1024);
		return $record;
	}
	
	
	protected function actionCacheUsage($params){
		$this->requireCli();

		if(!\GO::modules()->isInstalled('postfixadmin'))
			trigger_error('Postfixadmin module must be installed',E_USER_ERROR);

		$activeStmt = \GO\Postfixadmin\Model\Mailbox::model()->find();
		
		while ($mailboxModel = $activeStmt->fetch()) {
			if(empty($params['q'])) {
				echo 'Calculating size of ' . $mailboxModel->getMaildirFolder()->path() . "\n";
			}
			$mailboxModel->cacheUsage();

			if(empty($params['q'])) {
				echo \GO\Base\Util\Number::formatSize($mailboxModel->usage * 1024) . "\n";
			}
		}

	}
	
	
//	protected function actionImport($params){
//		
//		$source=array(
//				'host'=>'imap.imfoss.nl',
//				'port'=>993,
//				'username'=>'test@intermesh.nl',
//				'password'=>'test',
//				'ssl'=>true
//		);
//		
//		$localUsername="import@intermesh.dev";
//		
//		$imap = new \GO\Base\Mail\Imap();
//		if(!$imap->connect($source['host'], $source['port'], $source['username'], $source['password'], $source['ssl']))
//				throw new \Exception("Could not connect to source host");
//		
//		$folders = $imap->list_folders(true, false, "", "*");
//		if(!is_array($folders))
//			throw new \Exception("Failed to fetch folder list");
//		
//		$folderNames = array_keys($folders);
//		if(!in_array('INBOX', $folderNames))
//			$folderNames[]='INBOX';
//		
//		$list = implode(',', $folderNames);
//				
//    $fetchmailRc = 
//			"poll ".$source['host']."\n".
//			"proto imap\n".
//			"user ".$source['username']."\n".
//			"pass ".$source['password']."\n".
//			"is ".$localUsername."\n".
//			"limit 20480000\n".
//			"folder $list\n".
//			"keep\n".
//			"dropdelivered";
//		 
//		
//		file_put_contents('/tmp/importFetchmailRc', $fetchmailRc);
//		chmod('/tmp/importFetchmailRc',0700);
//		
//		system('fetchmail -r /tmp/importFetchmailRc -v');
//	}
	
}

