<?php


namespace GO\Email\Controller;
use GO;
	
	
	
	
class AccountController extends \GO\Base\Controller\AbstractModelController {

	protected $model = "GO\Email\Model\Account";
	
	protected function allowGuests() {
		return array('setsieve');
	}


//	protected function actionTest($params){
//
//		\GO::$disableModelCache=true;
//
//		for($i=0;$i<1000;$i++){
//
//			echo $i."<br>";
//			${"account".$i} = \GO\Email\Model\Account::model()->findSingle();
//
//			${"account".$i}->openImapConnection("INBOX");
//		}
//
//
//
//	}

//	protected function headers() {
//		header('Content-Type: application/json; charset=UTF-8');
//	}
	protected function getStoreParams($params) {

		$findParams = \GO\Base\Db\FindParams::newInstance()
						->select("t.id,t.host,t.user_id,t.username,t.smtp_host,a.email, a.name")
						->searchFields(array('a.email','a.name','t.host'))
						->joinModel(array(
				'tableAlias' => 'a',
				'model' => 'GO\Email\Model\Alias',
				'foreignField' => 'account_id', //defaults to primary key of the remote model
				'type' => 'INNER',
				'criteria' => \GO\Base\Db\FindCriteria::newInstance()->addCondition('default', 1, '=', 'a')
						));

		return $findParams;
	}

	protected function actionSetSieve(){
		if($this->isCli()){
			GO::getDbConnection()->query("UPDATE em_accounts set sieve_port=4190 where host='localhost'");
		}
	}
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		$columnModel->formatColumn('user_name', '$model->user->name');
		return parent::formatColumns($columnModel);
	}

	protected function afterLoad(&$response, &$model, &$params) {

		$response['data']['email_enable_labels'] = !empty(GO::config()->email_enable_labels); 

		$response['data']['smtp_auth']=!empty($model->smtp_username);

		//hide passwords
		$response['data']['password']='';
		$response['data']['smtp_password']='';

//		$response['data']['password'] = $model->decryptPassword();
//		$response['data']['smtp_password'] = $model->decryptSmtpPassword();


		$alias = $model->getDefaultAlias();

		$response['data']['mbroot'] = trim($response['data']['mbroot'], './');

		$response['data']['email'] = $alias->email;
		$response['data']['name'] = $alias->name;
		$response['data']['signature'] = $alias->signature;

//		if (\GO::modules()->isInstalled('addressbook')) {
			$defaultTemplateModel = \GO\Email\Model\DefaultTemplateForAccount::model()->findByPk($model->id);
			if ($defaultTemplateModel) {
				$response['data']['default_account_template_id'] = $defaultTemplateModel->template_id;
			} else {
				$response['data']['default_account_template_id'] = '';
			}
//		}

		return parent::afterLoad($response, $model, $params);
	}

	protected function beforeSubmit(&$response, &$model, &$params) {
		if(empty($params['password']))
			unset($params['password']);

		if(isset($params['smtp_auth'])) {
			if (!empty($params['smtp_auth'])){
				if(empty($params['smtp_password']))
					unset($params['smtp_password']);
			}else
			{
				$params['smtp_password']="";
				$params['smtp_username']="";
			}
		}

		return parent::beforeSubmit($response, $model, $params);
	}

	protected function afterSubmit(&$response, &$model, &$params, $modifiedAttributes) {

		if (empty($params['id'])) {
			$model->addAlias($params['email'], $params['name']);
		} else {
			$alias = $model->getDefaultAlias();
			$alias->name = $params['name'];

			if(isset($params['email']))
				$alias->email = $params['email'];

			$alias->signature = $params['signature'];
			$alias->save();
		}

		if ( isset($params['default_account_template_id'])) {
			if ($params['default_account_template_id']==-1 || empty($params['default_account_template_id'])) {
				$defaultTemplateModel = \GO\Email\Model\DefaultTemplateForAccount::model()->findByPk($model->id);
				if ($defaultTemplateModel)
					$defaultTemplateModel->delete();
			} elseif ($params['default_account_template_id']>0) {
				$defaultTemplateModel = \GO\Email\Model\DefaultTemplateForAccount::model()->findByPk($model->id);
				if (!$defaultTemplateModel) {
					$defaultTemplateModel = new \GO\Email\Model\DefaultTemplateForAccount();
					$defaultTemplateModel->account_id = $model->id;
				}
				$defaultTemplateModel->template_id = $params['default_account_template_id'];
				$defaultTemplateModel->save();
			}
		}

		return parent::afterSubmit($response, $model, $params, $modifiedAttributes);
	}

	protected function remoteComboFields() {
		
			return array('user_id' => '$model->user->name',
					'default_template_id' => '$model->defaultTemplate->emailTemplate->name');
		
	}

	protected function actionCheckUnseen($params) {

		$response=array("success"=>true);
		$response['email_status']['total_unseen']=0;
		$response['email_status']['unseen']=array();

		\GO::session()->closeWriting();

		$findParams = \GO\Base\Db\FindParams::newInstance()
						->ignoreAdminGroup()
						->select('t.*');

		$stmt = \GO\Email\Model\Account::model()->find($findParams);

		while ($account = $stmt->fetch()) {
			try {
				if($account->getDefaultAlias()){

					$checkMailboxArray = $account->getAutoCheckMailboxes();

					//$imap = $account->openImapConnection();

					$existingCheckMailboxArray = array();

					foreach ($checkMailboxArray as $checkMailboxName) {
						if(!empty($checkMailboxName)){
							$mailbox = new \GO\Email\Model\ImapMailbox($account, array('name'=>$checkMailboxName));
							if($mailbox->exists()){
								if(!isset($response['email_status']['has_new']) && $mailbox->hasAlarm()){
									$response['email_status']['has_new']=true;
								}
								$mailbox->snoozeAlarm();

								$response['email_status']['unseen'][]=array('account_id'=>$account->id,'mailbox'=>$checkMailboxName, 'unseen'=>$mailbox->unseen);
								$response['email_status']['total_unseen'] += $mailbox->unseen;

								$existingCheckMailboxArray[] = $checkMailboxName;
							}
						}
					}

					$account->check_mailboxes = implode(',',$existingCheckMailboxArray);
					if($account->isModified("check_mailboxes"))
						$account->save();

					if(($imap = $account->getImapConnection())){
						$imap->disconnect();
					}

				}

			} catch (\Exception $e) {
				\GO::debug($e->getMessage());
			}
		}

		\GO::debug("Total unseen: ".$response['email_status']['total_unseen']);

//$response['email_status']['has_new']=true;
		return $response;
	}

	public function actionSubscribtionsTree($params){
		$account = \GO\Email\Model\Account::model()->findByPk($params['account_id']);

		$rootMailboxes = $account->getRootMailboxes(false, false);

		//\GO::debug($rootMailboxes);
		if ($params['node'] == 'root')
			return $this->_getMailboxTreeNodes($rootMailboxes, true);
		else{
			$parts = explode('_', base64_decode($params['node']));
			$type = array_shift($parts);
			$accountId = array_shift($parts);
			$mailboxName = implode('_', $parts);

			$account = \GO\Email\Model\Account::model()->findByPk($accountId);

			$mailbox = new \GO\Email\Model\ImapMailbox($account, array('name' => $mailboxName));
			return $this->_getMailboxTreeNodes($mailbox->getChildren(false, false), true);
		}
	}

	private function _getUsage(\GO\Email\Model\Account $account){
		$usage="";

		$quota = $account->openImapConnection()->get_quota();
		
		if(isset($quota['usage'])) {
			if(!empty($quota['limit'])) {
				$percentage = ceil($quota['usage']*100/$quota['limit']);
				$usage = sprintf(\GO::t("%s of %s used", "email"), $percentage.'%', \GO\Base\Util\Number::formatSize($quota['limit']*1024));
				
				$round5 = floor($quota['usage']/5)*5;
				
				$usage='<span class="em-usage-'.$round5.'">'.$usage.'</span>';

			}	else {
				$usage = sprintf(\GO::t("%s used", "email"), \GO\Base\Util\Number::formatSize($quota['usage']*1024));
			}
		}
		//var_dump($usage);
		return $usage;
	}

	public function actionTree($params) {
		\GO::session()->closeWriting();

		$response = array();

		if(!isset($params['node'])){
			return $response;
		} elseif ($params['node'] == 'root') {

			$findParams = \GO\Base\Db\FindParams::newInstance()
						->select('t.*')
						->joinModel(array(
								'model' => 'GO\Email\Model\AccountSort',
								'foreignField' => 'account_id', //defaults to primary key of the remote model
								'localField' => 'id', //defaults to primary key of the model
								'type' => 'LEFT',
								'tableAlias'=>'s',
								'criteria'=>  \GO\Base\Db\FindCriteria::newInstance()->addCondition('user_id', \GO::user()->id,'=','s')
						))
						->ignoreAdminGroup()
						->order('order', 'DESC');


			if(isset($params['permissionLevel'])){
				$findParams->permissionLevel($params['permissionLevel']);
			}
			
			$stmt =  \GO\Email\Model\Account::model()->find($findParams);

			while ($account = $stmt->fetch()) {

				$alias = $account->getDefaultAlias();
				if($alias){
					$nodeId=base64_encode('account_' . $account->id);

					$node = array(
							'text' => $alias->email,
							'name' => $alias->email,
							'id' => $nodeId,
							'isAccount'=>true,
							'permission_level'=>$account->getPermissionLevel(),
							'hasError'=>false,
							'iconCls' => 'ic-account-box',
							'expanded' => $this->_isExpanded($nodeId),
							'noselect' => false,
							'account_id' => $account->id,
							'mailbox' => rtrim($account->mbroot,"./"),
							'noinferiors' => false,
							//'inbox_new' => 0,
							//'usage' => "",
							//"acl_supported"=>false
					);
					if($node['permission_level']<= \GO\Base\Model\Acl::READ_PERMISSION)
					  $node['cls'] = 'em-readonly';

//					try{
//						if($node['expanded']){
//							$account->openImapConnection();
//							$rootMailboxes = $account->getRootMailboxes(true);
//							$node['children']=$this->_getMailboxTreeNodes($rootMailboxes);
//						}
//
//					}catch(\GO\Base\Mail\ImapAuthenticationFailedException $e){
//						//$this->_checkImapConnectException($e,$node);
//						$node['isAccount'] = false;
//						$node['hasError'] = true;
//						$node['text'] .= ' ('.\GO::t("Error").')';
//						$node['children']=array();
//						$node['expanded']=true;
//						$node['qtipCfg'] = array('title'=>\GO::t("Error"), 'text' =>htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));	
//					}

					$response[] = $node;
				}
			}
		} else {
//			$this->_setExpanded($params['node']);

			$params['node']=base64_decode($params['node']);

			$parts = explode('_', $params['node']);
			$type = array_shift($parts);
			$accountId = array_shift($parts);
			$mailboxName = implode('_', $parts);

			$account = \GO\Email\Model\Account::model()->findByPk($accountId);

			if($type=="account"){
				$response=$this->_getMailboxTreeNodes($account->getRootMailboxes(true));
			}else{
				$mailbox = new \GO\Email\Model\ImapMailbox($account, array('name' => $mailboxName));
				$response = $this->_getMailboxTreeNodes($mailbox->getChildren());
			}
		}

		return $response;
	}

//	private function _checkImapConnectException(\Exception $e, &$node) {
//		if (strpos($e->getMessage(),'Authentication failed')==0) {
//			$node['isAccount'] = false;
//			$node['hasError'] = true;
//			$node['text'] .= ' ('.\GO::t("Error").')';
//			$node['children']=array();
//			$node['expanded']=true;
//			$node['qtipCfg'] = array('title'=>\GO::t("Error"), 'text' =>htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));	
//		} else {
//			throw $e;
//		}
//	}

	/**
	 *
	 * @param type $mailboxes
	 * @param boolean $fetchAllWithSubscribedFlag Get all children with the "Subscribed" flag
	 * @return type
	 */
	private function _getMailboxTreeNodes($mailboxes, $fetchAllWithSubscribedFlag=false) {
		$nodes = array();
		foreach ($mailboxes as $mailbox) {

			//skip mailboxes with nonexistent flags if we're not listing subscribtions
			if(!$fetchAllWithSubscribedFlag && !$mailbox->isVisible())// && !$mailbox->haschildren)
				continue;

			/* @var $mailbox \GO\Email\Model\ImapMailbox */
//			if (!$mailbox->subscribed)
//				continue;

			$nodeId = base64_encode('f_' . $mailbox->getAccount()->id . '_' . $mailbox->name);

			
			$text = '';
			if(!$fetchAllWithSubscribedFlag){
				if ($mailbox->unseen > 0) {
					$width = strlen((string) $mailbox->unseen)*6+10;
					$text .= '<div  class="em-folder-status" id="status_' . $nodeId . '">' . $mailbox->unseen . '</div>';
				} else {
					$text .= '<div class="em-folder-status" id="status_' . $nodeId . '"></div>';
				}
			}
			$text .= '<div class="ellipsis">'.$mailbox->getDisplayName().'</div>';

//			\GO::debug($mailbox);

//			$children = $this->_getMailboxTreeNodes($mailbox->getChildren());

			$cls = $mailbox->noselect==1 ? 'em-tree-node-noselect' : "";
			
			if($mailbox->unseen > 0 ) {
				$cls .= ' ml-folder-unseen';
			}
			
			$node = array(
					'cls' => $cls,
					'text' => $text,
					'mailbox' => $mailbox->name,
					'name' => $mailbox->getDisplayName(), // default value when renaming folder
					'account_id' => $mailbox->getAccount()->id,
					'iconCls' => 'ic-folder-open',
					'id' => $nodeId,
					'draggable'=>$mailbox->getAccount()->getPermissionLevel() > \GO\Base\Model\Acl::READ_PERMISSION,
					'permission_level'=>$mailbox->getAccount()->getPermissionLevel(),
					'noselect' => $mailbox->noselect,
					'disabled' =>$fetchAllWithSubscribedFlag && $mailbox->noselect,
					'noinferiors' => $mailbox->noinferiors,
					'children' => !$mailbox->haschildren ? array() : null,
					'expanded' => !$mailbox->haschildren,
					'permittedFlags' => $mailbox->areFlagsPermitted()
			);
			
			if (!$fetchAllWithSubscribedFlag && $mailbox->unseen > 0) {
				$node['iconCls'] .= ' ic-folder';
			}

//			\GO::debug($node);

			if($mailbox->name=='INBOX'){
				$node['usage']=$this->_getUsage($mailbox->getAccount());
				$node['acl_supported']=$mailbox->getAccount()->openImapConnection()->has_capability('ACL');
			}

			if ($mailbox->haschildren && $this->_isExpanded($nodeId)) {
				$node['children'] = $this->_getMailboxTreeNodes($mailbox->getChildren(false, !$fetchAllWithSubscribedFlag),$fetchAllWithSubscribedFlag);
				$node['expanded'] = true;
			}

			if($fetchAllWithSubscribedFlag){
				$node['checked']=$mailbox->subscribed;
			}

			//if($mailbox->hasnochildren)

			$sortIndex = 5;

			switch ($mailbox->name) {
				case 'INBOX':
					$node['iconCls'] = 'ic-inbox';
					$sortIndex = 0;
					break;
				case $mailbox->getAccount()->sent:
					$node['iconCls'] = 'ic-send';
					$sortIndex = 1;
					break;
				case $mailbox->getAccount()->trash:
					$node['iconCls'] = 'ic-delete';
					$sortIndex = 3;
					break;
				case $mailbox->getAccount()->drafts:
					$node['iconCls'] = 'ic-drafts';
					$sortIndex = 2;
					break;
				case 'INBOX/Spam':
				case 'INBOX.Spam':
				case 'Spam':
					$node['iconCls'] = 'ic-new-releases';
					$sortIndex = 4;
					break;
			}

			//don't return empty namespaces
			if(!$node['noselect'] || empty($node['expanded']) || !empty($node['children'])){
				$nodes[$sortIndex .'-'. $mailbox->name] = $node;
			}

		}
		\GO\Base\Util\ArrayUtil::caseInsensitiveSort($nodes);

		return array_values($nodes);
	}

	private $_treeState;

	private function _isExpanded($nodeId) {
		if (!isset($this->_treeState)) {
			$state = \GO::config()->get_setting("email_accounts_tree", \GO::user()->id);

			if(empty($state)){
				$decoded = base64_decode($nodeId);
				//account and inbox nodes are expanded by default
				if((stristr($decoded, 'account') || substr($decoded,-6)=='_INBOX')){
					return true;
				}else
				{
					return false;
				}
			}

			$this->_treeState = json_decode($state);
			
			if(!is_array($this->_treeState)){				
				$error = json_last_error();
				\go\core\ErrorHandler::log ('JSON Error: '.var_export($error, true));
				$this->_treeState = array();
			}
		}

		return in_array($nodeId, $this->_treeState);
	}

//	private function _setExpanded($nodeId){
//
//		if(!$this->_isExpanded($nodeId)){
//			$this->_treeState[]=$nodeId;
//			\GO::config()->save_setting("email_accounts_tree", json_encode($this->_treeState), \GO::user()->id);
//		}
//	}

	protected function actionSaveTreeState($params) {
		$response['success'] = \GO::config()->save_setting("email_accounts_tree", $params['expandedNodes'], \GO::user()->id);
		return $response;
	}


	protected function actionSaveSort($params){
		$sort_order = json_decode($params['sort_order'], true);
		$count = count($sort_order);

		\GO\Email\Model\AccountSort::model()->deleteByAttribute("user_id", \GO::user()->id);

		for($i=0;$i<$count;$i++) {

			$as = new \GO\Email\Model\AccountSort();
			$as->order=$count-$i;
			$as->account_id=$sort_order[$i];
			$as->save();
		}

		return array("success"=>true);
	}


	protected function actionUsernames($params){

//		$store = \GO\Base\Data\Store::newInstance(\GO\Email\Model\Account::model());
//		$findParams= $store->getDefaultParams($params)->group('username');



		$store = \GO\Base\Data\Store::newInstance(\GO\Base\Model\User::model());
		$findParams= $store->getDefaultParams($params);
		$findParams->joinModel(array(
					'model'=>'GO\Email\Model\Account',
					'localTableAlias'=>'t', //defaults to "t"
					'localField'=>'id', //defaults to "id"
					'foreignField'=>'user_id', //defaults to primary key of the remote model
					'tableAlias'=>'acc', //Optional table alias
					'type'=>'INNER', //defaults to INNER,
				//	'criteria'=>'' //\GO\Base\Db\FindCriteria Optional extra join parameters
			));

		$findParams->select('acc.username');
		$findParams->joinCustomFields(false);
		$findParams->group(array('acc.username'));

		$stmt = \GO\Base\Model\User::model()->find($findParams);

		//$stmt = \GO\Email\Model\Account::model()->find($findParams);
		$store->setStatement($stmt);

		return $store->getData();
	}

	protected function actionSavePassword($params) {

		$accountModel = \GO\Email\Model\Account::model()->findByPk($params['id']);
		$accountModel->password = $params['password'];
		$accountModel->save();

		return array('success'=>true);

	}

	protected function actionCopyMailTo($params) {

		$srcMessages = json_decode($params['srcMessages']);

		foreach ($srcMessages as $srcMessageInfo) {
			$srcAccountModel = \GO\Email\Model\Account::model()->findByPk($srcMessageInfo->accountId);
			$srcImapMessage = \GO\Email\Model\ImapMessage::model()->findByUid($srcAccountModel, $srcMessageInfo->mailboxPath, $srcMessageInfo->mailUid);

			$targetAccountModel = \GO\Email\Model\Account::model()->findByPk($params['targetAccountId']);

			if(!$targetAccountModel->checkPermissionLevel(\GO\Base\Model\Acl::CREATE_PERMISSION))
			  throw new \GO\Base\Exception\AccessDenied();

			$targetImapConnection = $targetAccountModel->openImapConnection($params["targetMailboxPath"]);
			
			$flags = '';
			
			if($srcMessageInfo->seen)
				$flags = '\SEEN';

			$targetImapConnection->append_message($params['targetMailboxPath'], $srcImapMessage->getSource(), $flags);
		}

		return array('success'=>true);
	}

	protected function actionLoadAddress($params) {

		$accountModel = GO\Email\Model\Account::model()->find(
			GO\Base\Db\FindParams::newInstance()
				->single()
				->select('t.*,al.name,al.email')
				->ignoreAcl()
				->joinModel(array(
					'model'=>'GO\Email\Model\Alias',
					'localTableAlias'=>'t',
					'localField'=>'id',
					'foreignField'=>'account_id',
					'tableAlias'=>'al'
				))
				->criteria(GO\Base\Db\FindCriteria::newInstance()
					->addCondition('id',$params['id'])
					->addCondition('default','1','=','al')
				)
		);

		$response = array(
			'success' => true,
			'data' => array(
				'name' => $accountModel->name,
				'email' => $accountModel->email
			)
		);

		echo json_encode($response);

	}

}
