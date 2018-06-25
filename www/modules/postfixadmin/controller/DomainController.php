<?php


namespace GO\Postfixadmin\Controller;


class DomainController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Postfixadmin\Model\Domain';
	
	
	protected function remoteComboFields() {
		return array('user_id'=>'$model->user->name');
	}
	
	
	protected function getStoreParams($params) {
		return \GO\Base\Db\FindParams::newInstance()->permissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION);
	}
	
	public function formatStoreRecord($record, $model, $store) {
		$record['user_name']=$model->user ? $model->user->name : 'unknown';
		
		$domainInfo = \GO\Postfixadmin\Model\Mailbox::model()->find(
			\GO\Base\Db\FindParams::newInstance()
				->single()
				->select('COUNT(*) AS mailbox_count, SUM(`usage`) AS `usage`, SUM(`quota`) AS `quota`')
				->criteria(
					\GO\Base\Db\FindCriteria::newInstance()
						->addCondition('domain_id', $model->id)
				)
		);
		$domainInfo2 = \GO\Postfixadmin\Model\Alias::model()->find(
			\GO\Base\Db\FindParams::newInstance()
				->single()
				->select('COUNT(*) AS alias_count')
				->criteria(
					\GO\Base\Db\FindCriteria::newInstance()
						->addCondition('domain_id', $model->id)
				)
		);
		$record['usage'] = \GO\Base\Util\Number::formatSize( $domainInfo->usage * 1024 );
		$record['quota'] = \GO\Base\Util\Number::formatSize( $model->total_quota * 1024 );
		$record['used_quota'] = \GO\Base\Util\Number::formatSize( $domainInfo->quota * 1024 );
		$record['mailbox_count'] = $domainInfo->mailbox_count.' / '.$model->max_mailboxes;
		$record['alias_count'] = $domainInfo2->alias_count.' / '.$model->max_aliases;
		return $record;
	}
	
	protected function beforeSubmit(&$response, &$model, &$params) {
		
		if(isset($params['total_quota'])){
			$model->total_quota=  \GO\Base\Util\Number::unlocalize($params['total_quota'])*1024;
			unset($params['total_quota']);
		}
		
		if(isset($params['default_quota'])){
			$model->default_quota=  \GO\Base\Util\Number::unlocalize($params['default_quota'])*1024;
			unset($params['default_quota']);
		}
		
		return parent::beforeSubmit($response, $model, $params);
	}
	
	protected function afterLoad(&$response, &$model, &$params) {
		
		$response['data']['default_quota'] = \GO\Base\Util\Number::localize($model->default_quota/1024);
		$response['data']['total_quota'] = \GO\Base\Util\Number::localize($model->total_quota/1024);
		return $response;
	}
	
	
	protected function actionGetUsage($params){
		
		if(!\GO::user()){
			if(empty($params['serverclient_token']) || $params['serverclient_token']!=\GO::config()->serverclient_token){
				throw new \GO\Base\Exception\AccessDenied();
			}else
			{
				\GO::session()->runAsRoot();
			}
		}
		
		$domains = json_decode($params['domains']);
						
		$response['success']=true;
		
		$record = \GO\Postfixadmin\Model\Mailbox::model()->find(
			\GO\Base\Db\FindParams::newInstance()
				->single()
				->select('SUM(`usage`) AS `usage`')
				->joinModel(array(
	 			'model'=>'GO\Postfixadmin\Model\Domain',
	 			'localField'=>'domain_id',
	 			'tableAlias'=>'d'	
				))
				->criteria(
					\GO\Base\Db\FindCriteria::newInstance()
						->addInCondition('domain', $domains,'d')
				)
		);
		
		$response['usage']=$record->usage;
		
		return $response;		
	}
	
	
	protected function allowGuests() {
		return array(
				'getusage', //handled by token
				'export',
				'import',
				'correctmaildirpaths'
		);
	}
	
	
	protected function actionExport($domain_name) {
		$this->requireCli();
		
		
		\GO::session()->runAsRoot();

		
		$domain = \GO\Postfixadmin\Model\Domain::model()->findSingleByAttribute('domain', $domain_name);
		$data = $domain->export();
		
		echo json_encode($data, JSON_PRETTY_PRINT);
	}
	
	
	protected function actionImport($file) {
		
		$this->requireCli();
		
		
		\GO::session()->runAsRoot();
		
		$data = file_get_contents($file);

		$data = json_decode($data, true);
		

		
		$domain = new \GO\Postfixadmin\Model\Domain();
		
		if($domain->import($data)) {
			echo "Success!";
		}
		
		
	}
	
	/**
	 * Get an export for the email domain accounts
	 * 
	 * @param array $params
	 * @throws \Exception
	 */
	protected function actionDomainExport($params){

		if(!isset($params['remoteModelId']) || !isset($params['domain']) || !isset($params['resetPasswords'])){
			throw new \Exception('Please provide all neccesary parameters');
		}
		
		$export = new \GO\Postfixadmin\Model\DomainExport();
		
		$export->remoteModelId = $params['remoteModelId'];
		$export->domain = $params['domain'];
		$export->resetPasswords = $params['resetPasswords'];
		
		echo $export->download();
	}
	
	
	protected function actionCorrectMaildirPaths($domain = null) {
		
		if(!$this->isCli()) {
			throw new \Exception("Only run this as root on CLI");
		}
		$findParams = new \GO\Base\Db\FindParams();
		$findParams->joinRelation('domain');
		
		if(isset($domain)) {
			$findParams->getCriteria()->addCondition('domain', $domain, '=', 'domain');			
		}
		
		$mailboxes = \GO\Postfixadmin\Model\Mailbox::model()->find($findParams);
		
		$rootDir = '/home/vmail/';
		$tmp = $rootDir . "tmp/" . uniqid() .'/';
		$this->exec("mkdir -p ".$tmp);
		
		foreach($mailboxes as $mailbox) {
			
			echo "Converting ".$mailbox->username . "\n";

			$parts = explode('@', $mailbox->username);
			
			$home = $rootDir . $mailbox->domain->domain . '/' . $parts[0] . '/';			
			$maildir = $home . 'Maildir/';
			if(!is_dir($home)) {
				echo "Maildir does not exist\n";
			} else {

				$this->exec("mv ". $home .' '. $tmp);
				$this->exec("mkdir " . $home);
				$this->exec("mv " . $tmp . basename($home) .' '.$maildir);
				exec("mv -f  ".$maildir.'sieve ' . $home);
			}
			$mailbox->maildir = substr($maildir, strlen($rootDir));
			if(!$mailbox->save()) {
				throw new \Exception("Could not save mailbox");
			}			
		}
		$this->exec("service dovecot restart");
	}
	
	private function exec($cmd) {
		echo "Running " . $cmd . "\n";
		system($cmd, $return);

		if ($return > 0) {
			throw new \Exception("Command failed with status " . $return);
		}

		return $output;
	}
}
