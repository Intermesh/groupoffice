<?php


namespace GO\Email\Controller;

use GO;
use go\core\ErrorHandler;
use go\core\util\DateTime;


class AccountController extends \GO\Base\Controller\AbstractModelController
{
	protected $model = "GO\Email\Model\Account";
	
	protected function allowGuests() {
		return array('setsieve');
	}


	protected function actionDisplay($params) {
		$modelName = $this->model;
		$model = \GO::getModel($modelName)->findByPk($this->getPrimaryKeyFromParams($params));

		if(!$model)
			throw new \GO\Base\Exception\NotFound();

		return ['success' => true, 'data' => ['username' => $model->username]];
	}


	protected function getStoreParams($params) {

		$findParams = \GO\Base\Db\FindParams::newInstance()
						->select("t.id,t.host,t.user_id,t.username,t.smtp_host,a.email, a.name")
						->searchFields(array('a.email','a.name','t.host','t.username', 't.smtp_host'))
						->joinModel(array(
				'tableAlias' => 'a',
				'model' => 'GO\Email\Model\Alias',
				'foreignField' => 'account_id', //defaults to primary key of the remote model
				'type' => 'INNER',
				'criteria' => \GO\Base\Db\FindCriteria::newInstance()->addCondition('default', 1, '=', 'a')
						));


		if(isset($params['sort'] ) && $params['sort'] == 'user') {
			$findParams->ignoreAdminGroup();
			$findParams->joinModel(array(
				'model' => 'GO\Email\Model\AccountSort',
				'foreignField' => 'account_id', //defaults to primary key of the remote model
				'localField' => 'id', //defaults to primary key of the model
				'type' => 'LEFT',
				'tableAlias'=>'s',
				'criteria'=>  \GO\Base\Db\FindCriteria::newInstance()->addCondition('user_id', \GO::user()->id,'=','s')
			));
			$findParams->order('s.order', 'DESC');

			unset($params['sort']);
		}

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

	protected function afterLoad(&$response, &$model, &$params)
	{

		$response['data']['email_enable_labels'] = !empty(GO::config()->email_enable_labels); 

		$response['data']['smtp_auth']=!empty($model->smtp_username);

		//hide passwords
		$response['data']['password']='';
		$response['data']['smtp_password']='';


		$alias = $model->getDefaultAlias();


		$response['data']['email'] = $alias->email;
		$response['data']['name'] = $alias->name;
		$response['data']['signature'] = $alias->signature;

		$defaultTemplateModel = \GO\Email\Model\DefaultTemplateForAccount::model()->findByPk($model->id);
		if ($defaultTemplateModel) {
			$response['data']['default_account_template_id'] = $defaultTemplateModel->template_id;
		} else {
			$response['data']['default_account_template_id'] = '';
		}

		return parent::afterLoad($response, $model, $params);
	}

	protected function beforeSubmit(&$response, &$model, &$params) {
		if(empty($params['password'])) {
			if(isset($params['oauth2_client_id']) && !empty($params['oauth2_client_id'])) {
				$params['password'] = uniqid(); // We only need it to save the account record
				$model->checkImapConnectionOnSave = false;
			} else {
				unset($params['password']);
			}
		}

		if(isset($params['smtp_auth'])) {
			if (!empty($params['smtp_auth'])){
				if(empty($params['smtp_password'])) {
					unset($params['smtp_password']);
				}
			} else {
				$params['smtp_password']="";
				$params['smtp_username']="";
			}
		}

		return parent::beforeSubmit($response, $model, $params);
	}

	protected function afterSubmit(&$response, &$model, &$params, $modifiedAttributes)
	{
		if (empty($params['id'])) {
			$model->addAlias($params['email'], $params['name']);
		} else {
			$alias = $model->getDefaultAlias();
			$alias->name = $params['name'];

			if(isset($params['email'])) {
				$alias->email = $params['email'];
			}

			$alias->signature = $params['signature'];
			$alias->save();
		}

		if ( isset($params['default_account_template_id'])) {
			$defaultTemplateModel = \GO\Email\Model\DefaultTemplateForAccount::model()->findByPk($model->id);
			if (!$defaultTemplateModel) {
				$defaultTemplateModel = new \GO\Email\Model\DefaultTemplateForAccount();
				$defaultTemplateModel->account_id = $model->id;
			}
			$defaultTemplateModel->template_id = (int) $params['default_account_template_id'];
			$defaultTemplateModel->save();
		}

		// $model->isNew() does not work!
		if(isset($params['oauth2_client_id']) && !empty($params['oauth2_client_id']) && empty($params['id'])) {
			$response['needs_refresh_token'] = true;
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

		return $response;
	}

	public function actionSubscribtionsTree(array $params)
	{
		$account = \GO\Email\Model\Account::model()->findByPk($params['account_id']);

		$rootMailboxes = $account->getRootMailboxes(false, false);

		if ($params['node'] == 'root') {
			return $this->_getMailboxTreeNodes($rootMailboxes, true);
		} else{
			$parts = explode('_', base64_decode($params['node']));
			$type = array_shift($parts);
			$accountId = array_shift($parts);
			$mailboxName = implode('_', $parts);

			$account = \GO\Email\Model\Account::model()->findByPk($accountId);

			$mailbox = new \GO\Email\Model\ImapMailbox($account, array('name' => $mailboxName));
			return $this->_getMailboxTreeNodes($mailbox->getChildren(false, false), true);
		}
	}

	private function _getUsage(\GO\Email\Model\Account $account)
	{
		$usage="";

		$quota = $account->openImapConnection()->get_quota();
		
		if(isset($quota['usage'])) {
			if(!empty($quota['limit'])) {
				$percentage = ceil($quota['usage']*100/$quota['limit']);
				$usage = sprintf(\GO::t("%s of %s used", "email"), $percentage.'%', \GO\Base\Util\Number::formatSize($quota['limit']*1024));
				
				$round5 = floor($percentage / 5)*5;

				if($round5 > 95) {
					$round5 = 95;
				}
				
				$usage='<span class="em-usage-'.$round5.'">'.$usage.'</span>';

			}	else {
				$usage = sprintf(\GO::t("%s used", "email"), \GO\Base\Util\Number::formatSize($quota['usage']*1024));
			}
		}
		return $usage;
	}

	public function actionTree(array $params): array
	{
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
				try {
					$account->maybeRenewAccessToken();
				}catch(\Exception $e) {
					ErrorHandler::logException($e);
				}
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
						'iconCls' => 'ic-account-box fg-main',
						'expanded' => $this->_isExpanded($nodeId),
						'noselect' => false,
						'account_id' => $account->id,
						'mailbox' => rtrim($account->mbroot,"./"),
						'noinferiors' => false
					);
					if($node['permission_level']<= \GO\Base\Model\Acl::READ_PERMISSION) {
						$node['cls'] = 'em-readonly';
					}
					$response[] = $node;
				}
			}
		} else {
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

			$sortIndex = 5;
			$node['iconCls'] = 'c-primary ';
			switch ($mailbox->name) {
				case 'INBOX':
					$node['iconCls'] .= 'ic-inbox';
 					$sortIndex = 0;
					break;
				case $mailbox->getAccount()->sent:
					$node['iconCls'] .= 'ic-send';
					$sortIndex = 1;
					break;
				case $mailbox->getAccount()->trash:
					$node['iconCls'] .= 'ic-delete';
					$sortIndex = 3;
					break;
				case $mailbox->getAccount()->drafts:
					$node['iconCls'] .= 'ic-drafts';
					$sortIndex = 2;
					break;
				case $mailbox->getAccount()->spam:
					$node['iconCls'] .= 'ic-new-releases';
					$sortIndex = 4;
					break;
				default: $node['iconCls'] .= 'ic-folder';
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


	protected function actionUsernames(array $params)
	{
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

		$store->setStatement($stmt);

		return $store->getData();
	}

	protected function actionSavePassword(array $params)
	{
		$accountModel = \GO\Email\Model\Account::model()->findByPk($params['id']);
		$accountModel->password = $params['password'];
		$accountModel->save();

		return array('success'=>true);

	}

	protected function actionCopyMailTo(array $params)  :array
	{
		$srcMessages = json_decode($params['srcMessages']);

		$move = !empty($params['move']);

		// This function is very inefficient because it essentially does a copy on each individual message, because it checks if it's between different accounts
		// If we treat both cases differently, we can greatly speed things up

		// We create two arrays. One for UIDs belonging to the same account, one for UIDs from different accounts
		// This can probably be optimized further because now we lookup srcAccountModel / $targetAccountModel twice for the external case
		$internalMove=[];
    $externalMove=[];

    foreach ($srcMessages as $srcMessageInfo) {
      $srcAccountModel = \GO\Email\Model\Account::model()->findByPk($srcMessageInfo->accountId);

      $targetAccountModel = \GO\Email\Model\Account::model()->findByPk($params['targetAccountId']);

      if(!$targetAccountModel->checkPermissionLevel(\GO\Base\Model\Acl::CREATE_PERMISSION)) {
				throw new \GO\Base\Exception\AccessDenied();
      }

      if($move && $targetAccountModel->id == $srcAccountModel->id) {

        $srcImapMessage = \GO\Email\Model\ImapMessage::model()->findByUid($srcAccountModel, $srcMessageInfo->mailboxPath, $srcMessageInfo->mailUid);

        $internalMove[]=$srcImapMessage->uid;
      } else {
        $externalMove[]=$srcMessageInfo;
      }
    }

		// Move everything in one shot vs foreach move
    if (is_array($internalMove) && count($internalMove)>0) {
      $conn = $srcAccountModel->openImapConnection( $srcMessageInfo->mailboxPath);

      $conn->move($internalMove,$params["targetMailboxPath"]);
    }

		// Different accounts, do it the slow way
    if (is_array($externalMove) && count($externalMove)>0) {

      foreach ($externalMove as $srcMessageInfo) {

        $srcAccountModel = \GO\Email\Model\Account::model()->findByPk($srcMessageInfo->accountId);

        $srcImapMessage = \GO\Email\Model\ImapMessage::model()->findByUid($srcAccountModel, $srcMessageInfo->mailboxPath, $srcMessageInfo->mailUid);

        $targetAccountModel = \GO\Email\Model\Account::model()->findByPk($params['targetAccountId']);

        $targetImapConnection = $targetAccountModel->openImapConnection($params["targetMailboxPath"]);

        $flags = '';

        if ($srcMessageInfo->seen)
					$flags = '\SEEN';

        $targetImapConnection->append_message($params['targetMailboxPath'], $srcImapMessage->getSource(), $flags, new DateTime('@'.$srcImapMessage->udate));

        if ($move) {
					$srcImapMessage->delete();
        }
      }
    }

		return array('success'=>true);
	}

	protected function actionLoadAddress(array $params)
	{
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
