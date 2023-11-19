<?php


namespace GO\Email\Controller;


class PortletController extends \GO\Base\Controller\AbstractModelController {
	
	/**
	 * The full name of the used model in this controller
	 * 
	 * @var StringHelper 
	 */
	protected $model = 'GO\email\Model\PortletFolder';
	
	/**
	 * The state of the portlet folders tree (This is the same state as the tree in the email tab)
	 * 
	 * @var StringHelper 
	 */
	private $_treeState;
	
	/**
	 * Load the folders that need to be displayed in the portlet
	 * 
	 * @param array $params
	 * @return array $response
	 */
	protected function actionPortletFoldersByUser($params){
		
		$findCriteria = \GO\Base\Db\FindCriteria::newInstance()
						->addCondition('user_id', \GO::user()->id);
						
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->joinRelation('account')
						->criteria($findCriteria);
		
		$portletFoldersStatement = \GO\email\Model\PortletFolder::model()->find($findParams);
		
		$portletFoldersStore = \GO\Base\Data\Store::newInstance(\GO::getModel($this->model));
				
		$cm = $portletFoldersStore->getColumnModel();
		$cm->formatColumn('email','$model->account->getDefaultAlias()->email');
		$cm->formatColumn('name', '$model->getName()');
		$portletFoldersStore->setStatement($portletFoldersStatement);
		
		return $portletFoldersStore->getData();
	}

protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel)
{
	$columnModel->formatColumn('name', '$model->getName()');
}

	/**
	 * Enable a folder to be displayed in the portlet
	 * 
	 * @param array $params
	 * @return array $response
	 */
	protected function actionEnablePortletFolder($params){
		$response = array();
		
		if(!isset($params['account_id']) || !isset($params['account_id'])){
			$response['success'] = false;
		} else {
			$accountId = $params['account_id'];
			$mailboxName = $params['mailbox'];

			$portletFolder =  $this->_loadPortletFolder($accountId,$mailboxName);
			
			if(!$portletFolder){
				$portletFolder = new \GO\email\Model\PortletFolder();
				$portletFolder->user_id = \GO::user()->id;
				$portletFolder->account_id = $accountId;
				$portletFolder->folder_name = $mailboxName;
				$portletFolder->save();
			}

			$response['success'] = true;
		}

		return $response;
	}
	
	
	private function _loadPortletFolder($accountId,$mailboxName){
		$portletFolder =  \GO\email\Model\PortletFolder::model()->findByPk(array('account_id'=>$accountId,'folder_name'=>$mailboxName,'user_id'=>\GO::user()->id));
		
		if(!$portletFolder)
			return false;
		else
			return $portletFolder;
	}
	
	/**
	 * Disable a folder to be disabled from the portlet
	 * 
	 * @param array $params
	 * @return array $response
	 */
	protected function actionDisablePortletFolder($params){
		$response = array();
		
		if(!isset($params['account_id']) || !isset($params['account_id'])){
			$response['success'] = false;
		} else {
			$accountId = $params['account_id'];
			$mailboxName = $params['mailbox'];

			$portletFolder =  $this->_loadPortletFolder($accountId,$mailboxName);
			
			if($portletFolder)
				$portletFolder->delete();
			
			$response['success'] = true;
		}
		
		$response['success'] = true;
		
		return $response;
	}
	
	/**
	 * Build the tree for the portlet folders
	 * 
	 * @param array $params
	 * @return array $response
	 */
	public function actionPortletTree($params) {
		\GO::session()->closeWriting();
		
		$response = array();

		if ($params['node'] == 'root') {
			
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
			
			$stmt = \GO\Email\Model\Account::model()->find($findParams);

			
			// Loop throught the found accounts and build the accounts root node.
			while ($account = $stmt->fetch()) {

				$alias = $account->getDefaultAlias();
				if($alias){
					$nodeId='account_' . $account->id;
					
					$node = array(
							'text' => $alias->email,
							'name' => $alias->email,
							'id' => $nodeId,
							'isAccount'=>true,
							'hasError'=>false,
							'iconCls' => 'folder-account',
							'expanded' => $this->_isExpanded($nodeId),
							'noselect' => false,
							'account_id' => $account->id,
							'mailbox' => '',							
							'noinferiors' => false
					);
					
					// Try to find the children
					try{
						$account->openImapConnection();
						if($node['expanded'])
							$node['children']=$this->_getMailboxTreeNodes($account->getRootMailboxes(true));
						
					}catch(\Exception $e){
//						$this->_checkImapConnectException($e,$node);
						if (strpos($e->getMessage(),'Authentication failed')==0) {
							$node['isAccount'] = false;
							$node['hasError'] = true;
							$node['text'] .= ' ('.\GO::t("Error").')';
							$node['children']=array();
							$node['expanded']=true;
							$node['qtipCfg'] = array('title'=>\GO::t("Error"), 'text' =>htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));	
						} else {
							throw $e;
						}
					}
					
					$response[] = $node;
				}
			}
		} else {
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
	 * Get the tree result from the given mailboxes
	 * 
	 * @param array $mailboxes
	 * @return StringHelper
	 */
	private function _getMailboxTreeNodes($mailboxes) {
		$nodes = array();
		foreach ($mailboxes as $mailbox) {

			$nodeId = 'f_' . $mailbox->getAccount()->id . '_' . $mailbox->name;
			
			$text = $mailbox->getDisplayName();
						
			$node = array(
					'text' => $text,
					'mailbox' => $mailbox->name,
					'account_id' => $mailbox->getAccount()->id,
					'iconCls' => 'folder-default',
					'id' => $nodeId,
					'noselect' => $mailbox->noselect,
					'disabled' =>$mailbox->noselect,
					'noinferiors' => $mailbox->noinferiors,
					'children' => !$mailbox->haschildren ? array() : null,
					'expanded' => !$mailbox->haschildren,
					'cls'=>$mailbox->noselect==1 ? 'em-tree-node-noselect' : null
			);

			if ($mailbox->haschildren && $this->_isExpanded($nodeId)) {
				$node['children'] = $this->_getMailboxTreeNodes($mailbox->getChildren(false, false));
				$node['expanded'] = true;
			}

			$node['checked']= $this->_showInPortlet($mailbox->getAccount()->id,$mailbox->name);
			
			$sortIndex = 5;

			switch ($mailbox->name) {
				case 'INBOX':
					$node['iconCls'] = 'email-folder-inbox';
					$sortIndex = 0;
					break;
				case $mailbox->getAccount()->sent:
					$node['iconCls'] = 'email-folder-sent';
					$sortIndex = 1;
					break;
				case $mailbox->getAccount()->drafts:
					$node['iconCls'] = 'email-folder-drafts';
					$sortIndex = 2;
					break;
				case $mailbox->getAccount()->trash:
					$node['iconCls'] = 'email-folder-trash';
					$sortIndex = 3;
					break;
				case $mailbox->getAccount()->spam:
					$node['iconCls'] = 'email-folder-spam';
					$sortIndex = 4;
					break;
			}

			$nodes[$sortIndex . $mailbox->name] = $node;
		}
		ksort($nodes);

		return array_values($nodes);
	}
	
	/**
	 * Check if the node is opened in the email module or not
	 * 
	 * @param int $nodeId
	 * @return boolean
	 */
	private function _isExpanded($nodeId) {
		if (!isset($this->_treeState)) {
			$state = \GO::config()->get_setting("email_accounts_tree", \GO::user()->id);
			
			if(empty($state)){
				//account and inbox nodes are expanded by default
				if((stristr($nodeId, 'account') || substr($nodeId,-6)=='_INBOX')){
					return true;
				}else
				{
					return false;
				}
			}
			
			$this->_treeState = json_decode($state);
		}

		return in_array($nodeId, $this->_treeState);
	}
	
	/**
	 * Check if the mailbox is enabled to show in the email portlet
	 * 
	 * @param StringHelper $mailboxName
	 * @return boolean
	 */
	private function _showInPortlet($accountId,$mailboxName){
	
		if(!$this->_loadPortletFolder($accountId, $mailboxName))
			return false;
		else
			return true;
	}	
		
}
