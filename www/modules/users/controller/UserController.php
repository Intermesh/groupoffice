<?php


namespace GO\Users\Controller;

use GO\Base\Model\User;
use GO\Users\Model\Transporter;


class UserController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Base\Model\User';

	protected function ignoreAclPermissions() {
		//ignore acl on submit so normal users can use the users module. 
		//otherwise they are not allowed to save users.
		return array('store','load','submit');
	}
	
	/**
	 * Transfer data from 1 user account to antoher
	 * @param $tranfer has two item: 'id_from', 'id_to'
	 */
	protected function actionTransfer($transfer) {
		
		$transporter = new Transporter($transfer['id_from'], $transfer['id_to']);
		
		return array('success' => $transporter->sync());
	}
	
	protected function afterDisplay(&$response, &$model, &$params) {
		
		$contact = $model->createContact();
		
		$response['data']['contact_id']=$contact->id;
		
		return parent::afterDisplay($response, $model, $params);
	}

	protected function remoteComboFields() {
		if(\GO::modules()->isInstalled('addressbook')){
			return array(
					'addressbook_id' => '$model->contact->addressbook->name',
					'company_id' => '$model->contact->company->name',
					'holidayset'=> '\GO::t($model->holidayset)'
					);
		}
	}

	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		$columnModel->formatColumn('name', '$model->getName()', array(), \GO::user()->sort_name);
		$columnModel->formatColumn('enabled', "!empty(\$model->enabled) ? \GO::t(\"Yes\") : \GO::t(\"No\")");
		return parent::formatColumns($columnModel);
	}

	protected function afterLoad(&$response, &$model, &$params) {

		//Join the contact that belongs to the user in the form response.
		if(\GO::modules()->isInstalled('addressbook')){
			$contact=false;
			if(!empty($model->id)){
				$contact = $model->contact;
			}elseif(!empty($params['contact_id'])){
				$contact = \GO\Addressbook\Model\Contact::model()->findByPk($params['contact_id']);
				$response['data']['contact_id']=$contact->id;
			}
			if(!$contact)
			{
				$contact = new \GO\Addressbook\Model\Contact();
			}
			if ($contact) {
				$attr = $contact->getAttributes();

				// Set the default addressbook ID to the "Users" addressbook when it is a new User
				if($model->isNew){
					if(!empty($params['addressbook_id'])) {
						$addressbook = \GO\Addressbook\Model\Addressbook::model()->findByPk($params['addressbook_id']);
					} else {
						$addressbook = \GO\Addressbook\Model\Addressbook::model()->getUsersAddressbook();
					}
					
					if($addressbook){
						$attr['addressbook_id'] = $addressbook->id;
						if(empty($response['remoteComboTexts']))
							$response['remoteComboTexts'] = array();
						$response['remoteComboTexts']['addressbook_id'] = $addressbook->name; // Add remote combo text
					}
				}
				
				$response['data'] = array_merge($attr, $response['data']);
				
				if(empty($response['data']['company_id'])){
					$response['data']['company_id']="";
				} else {
					// Set the correct remote combo text for the company
					$response['remoteComboTexts']['company_id'] = $contact->company->name;
				}
			}
			
			if(!empty($response['data']['date_separator'])&& !empty($response['data']['date_format'])){
				$response['data']['dateformat'] = $response['data']['date_separator'].':'.$response['data']['date_format'];
			}

			unset($response['data']['password']);
		}
		

		return parent::afterLoad($response, $model, $params);
	}

	protected function beforeSubmit(&$response, &$model, &$params) {

		if(empty($params['password'])){
			unset($params['password']);
		}
		
		if(!empty($params["dateformat"])){
			$dateparts = explode(':',$params["dateformat"]);
			$params['date_separator'] = $dateparts[0];
			$params['date_format'] = $dateparts[1];
		}
		
		return parent::beforeSubmit($response, $model, $params);
	}

	protected function afterSubmit(&$response, &$model, &$params, $modifiedAttributes) {

		//Save the contact fields to the contact.
		
		if (isset($_POST['modules'])) {
			$modules = !empty($_POST['modules']) ? json_decode($_POST['modules']) : array();
			$groupsMember = json_decode($_POST['group_member'], true);
			
			
			$userGroup = \go\modules\core\groups\model\Group::find()->where(['isUserGroupFor' => $model->id])->single();
			/**
			 * Process selected module permissions
			 */
			foreach ($modules as $modPermissions) {
				$modModel = \GO\Base\Model\Module::model()->findByName(
					$modPermissions->id
				);	
				if(!$modModel->acl->addGroup(
					$userGroup->id,
					$modPermissions->permissionLevel
				)) {
					throw new \Exception("Could not add group");
				}
			}

			/**
			 * User will be member of the selected groups
			 */
			foreach ($groupsMember as $group) {
				if ($group['id'] != \GO::config()->group_everyone) {
					if ($group['selected']) {
						\GO\Base\Model\Group::model()->findByPk($group['id'])->addUser($model->id);
					} else {
						\GO\Base\Model\Group::model()->findByPk($group['id'])->removeUser($model->id);
					}
				}
			}
		}

		$model->checkDefaultModels();

		if (!empty($params['send_invitation'])) {
			$model->sendRegistrationMail();
		}
	}

	protected function actionSyncContacts($params) {
		
		\GO::$ignoreAclPermissions=true; //allow this script access to all
		\GO::$disableModelCache=true; //for less memory usage
		ini_set('max_execution_time', '300');

		$ab = \GO\Addressbook\Model\Addressbook::model()->findSingleByAttribute('users', '1'); //\GO::t("Users"));
		if (!$ab) {
			$ab = new \GO\Addressbook\Model\Addressbook();
			$ab->name = \GO::t("Users");
			$ab->users = true;
			$ab->save();
		}
		$stmt = User::model()->find();
		while ($user = $stmt->fetch()) {

			$contact = $user->contact();
			if (!$contact) {
				
				\GO::output("Creating contact for ".$user->username);
				
				$contact = new \GO\Addressbook\Model\Contact();
				$contact->go_user_id = $user->id;
				$contact->addressbook_id = $ab->id;
			}else
			{
				\GO::output("Updating contact for ".$user->username);
			}
			$attr = $user->getAttributes();
			unset($attr['id']);

			$contact->setAttributes($attr);
			$contact->save();
		}
		
		\GO::output("Done!");

		//return array('success' => true);
	}
	

	
	protected function beforeStoreStatement(array &$response, array &$params, \GO\Base\Data\AbstractStore &$store, \GO\Base\Db\FindParams $storeParams) {
		
		$storeParams->joinModel(
			array(
				'model'=>'GO\Base\Model\UserGroup',
				'localTableAlias'=>'t',
				'localField'=>'id',
				'foreignField'=>'userId',
				'tableAlias'=>'ug'
			));
		
		$storeParams->group('t.id');
		$storeParams->export('users');
		
		$groupsMultiSel = new \GO\Base\Component\MultiSelectGrid(
			'users-groups-panel', 
			"GO\Base\Model\Group",$store, $params, true);
			$groupsMultiSel->addSelectedToFindCriteria($storeParams, 'groupId','ug');
			
		return parent::beforeStoreStatement($response, $params, $store, $storeParams);
	}
	
	/**
	 * Get an example file for importing users
	 * 
	 * @param array $params 
	 */
	protected function actionGetImportExample($params){
	
		$data = array();
		
		for($i=0;$i<5;$i++){
			$data[$i] = array(
				'username'=>'user'.$i,
				'password'=>'password'.$i,
				'enabled'=>$i%3?1:0,
				'first_name'=>'firstname'.$i,
				'middle_name'=>'middlename'.$i,
				'last_name'=>'lastname'.$i,
				'initials'=>'AE',
				'title'=>$i%2?'Mr.':'Mevr.',
				'sex'=>$i%2?'M':'F',
				'birthday'=>'0'.($i+1).'-0'.($i+3).'-8'.$i,
				'email'=>'email'.$i.'@domain.com',
				'company'=>'company',
				'department'=>'department',
				'function'=>'function',
				'home_phone'=>'123456789'.$i,
				'work_phone'=>'623451789'.$i,
				'fax'=>'423156789'.$i,
				'cellular'=>'061234567'.$i,
				'country'=>'NL',
				'state'=>'state',
				'city'=>'city',
				'zip'=>'zip',
				'address'=>'Address',
				'address_no'=>$i,
				'groups'=>'Everyone,Internal,Some_group',
				'modules_read'=>'summary,email,addressbook,files',
				'modules_write'=>'tasks'
			);
		}
		
		\GO\Base\Util\Http::outputDownloadHeaders(new \GO\Base\Fs\File("users.csv"));
		
		$csvFile  = new \GO\Base\Csv\Writer('php://output');
	
		$header = true;
		
		foreach($data as $row){
			
			if($header){
				$csvFile->putRecord(array_keys($row));
				$header=false;
			}
			
			$csvFile->putRecord(array_values($row));
		}		
	}
	
	/**
	 * The function that will be called when importing users 
	 * 
	 * @param array $params
	 * @return array
	 * @throws Exception When the controller cannot be found, an exeption will be thrown
	 */
	protected function actionImport($params){
		
		$response = array();
		
//		$params['updateExisting'] = true;
		
		$params['updateFindAttributes'] = 'username';
		$params['file'] = $_FILES['files']['tmp_name'][0];
		
		\GO::setMaxExecutionTime(0);
		
		if($params['controller']=='GO\Users\Controller\UserController')
			$controller = new UserController();
		else
			throw new \Exception("No or wrong controller given");

		$response = array_merge($response,$controller->run("importCsv",$params,false));
		
		$response['success'] = true;
		return $response;
	}
	
	/**
	 * The actual call to the import CSV function
	 * 
	 * @param array $params
	 * @return array $response 
	 */
	protected function actionImportCsv($params){
		
		//allow weak passwords
		\GO::config()->password_validate=false;
		
		$summarylog = parent::actionImport($params);
		return $summarylog->getErrorsJson();
	}
	
	
	/**
	 * The afterimport for every imported user.
	 * 
	 * @param User $model
	 * @param array $attributes
	 * @param array $record
	 * @return boolean success
	 */
	protected function afterImport(&$model, &$attributes, $record){
		
		// Create the new groups
		if(!empty($attributes["groups"]))
			$model->addToGroups(explode(',',$attributes["groups"]),true);
		
		// Create the 
		$c=$model->createContact();
		$c->setAttributes($attributes);
		$c->save();
		
		$model->checkDefaultModels();
		
		return parent::afterImport($model, $attributes, $record);
	}
	
	
	protected function actionGroupStore($user_id=0){
		
		$selectedGroupIds=array();
		if(empty($user_id))
		{
			$selectedGroupIds=User::getDefaultGroupIds();
		}else
		{
//			$user = User::model()->findByPk($user_id);
			$selectedGroupIds = User::getGroupIds($user_id);
		}
		
		
		$columnModel = new \GO\Base\Data\ColumnModel('GO\Base\Model\Group');
		
		$columnModel->formatColumn('selected', 'in_array($model->id, $selectedGroupIds)', array('selectedGroupIds'=>$selectedGroupIds));
		$columnModel->formatColumn('disabled', 
						'($user_id==1 && $model->id==GO::config()->group_root) || $model->id==GO::config()->group_everyone', array('user_id'=>$user_id));
		
		$store = new \GO\Base\Data\DbStore('GO\Base\Model\Group', $columnModel);
		$store->defaultSort = array('name');
		
		$store->getFindParams()->getCriteria()->addCondition('isUserGroupFor', null);
		
		return $store->getData();
		
	}
	
	
	protected function actionVisibleGroupStore($user_id=0){
		
		$selectedGroupIds=array();
		if(empty($user_id))
		{
			$selectedGroupIds=User::getDefaultVisibleGroupIds();
		}else
		{
			$user = User::model()->findByPk($user_id);
			$groups = $user->getAcl()->getGroups();
			
			foreach($groups as $group){
				$selectedGroupIds[] = $group->id;
			}
		}
		
		
		$columnModel = new \GO\Base\Data\ColumnModel('GO\Base\Model\Group');
		
		$columnModel->formatColumn('selected', 'in_array($model->id, $selectedGroupIds)', array('selectedGroupIds'=>$selectedGroupIds));
		$columnModel->formatColumn('disabled', '$model->id==GO::config()->group_root');
		
		$store = new \GO\Base\Data\DbStore('GO\Base\Model\Group', $columnModel);
		$store->defaultSort = array('name');
		
		return $store->getData();
		
	}
}
