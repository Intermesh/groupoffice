<?php

namespace GO\Core\Controller;

use GO;
use GO\Base\Data\Store;
use GO\Base\Db\FindParams;
use GO\Base\Model\ModelType;

class SearchController extends \GO\Base\Controller\AbstractModelController{
	protected $model = 'GO\Base\Model\SearchCacheRecord';
	
	protected function beforeStore(&$response, &$params, &$store) {
		//handle deletes for searching differently
		
		if(!empty($params['delete_keys'])){
			
			try{
				$keys = json_decode($params['delete_keys'], true);
				unset($params['delete_keys']);
				foreach($keys as $key){
					$key = explode(':',$key);

					$linkedModel = \GO::getModel($key[0])->findByPk($key[1]);				
					if($linkedModel)
						$linkedModel->delete();				
				}
				unset($params['delete_keys']);
				$response['deleteSuccess']=true;
			}
			catch(\Exception $e){
				$response['deleteSuccess']=false;
				$response['deleteFeedback']=$e->getMessage();
			}
		}
//		
//		//search query is required
//		if(empty($params["query"])){
//			return false;
//		}else
//		{
//			//we'll do a full text search in getStoreParams			
////			$params['match']=$params["query"];
////			unset($params["query"]);
//		}
//	
		
		return parent::beforeStore($response, $params, $store);
	}
	
	protected function getStoreParams($params) {
		$filesupport = false;
		
		if(isset($params['filesupport'])){
			$filesupport = $params['filesupport']==="true" || $params['filesupport']==="1"?true:false;
		}
		
		$forLinks = isset($params['for_links']) && ($params['for_links'] === "true" || $params['for_links'] === "1");
		$types = array();
		$storeParams = FindParams::newInstance();
		if(isset($params['model_names'])){
			$model_names = json_decode($params['model_names'], true);
			
			foreach($model_names as $model_name){
				if(class_exists($model_name))
					$types[]=\GO::getModel($model_name)->modelTypeId();
			}
		}
		
		if(!empty($params['type_filter'])) {
			if(isset($params['types'])) {
				$types= json_decode($params['types'], true);				
			}else {
				$types = \GO::config()->get_setting('link_type_filter', \GO::user()->id);
				$types = empty($types) ? array() : explode(',', $types);	
			}
			
			//only search for available types. eg. don't search for contacts if the user doesn't have access to the addressbook
			if(!count($types))
					$types=$this->_getAllModelTypes($filesupport, $forLinks);
			
			if(!isset($params['no_filter_save']) && isset($params['types']))
				\GO::config()->save_setting ('link_type_filter', implode(',',$types), \GO::user()->id);
		}else if(!count($types)) {
			$types=$this->_getAllModelTypes($filesupport, $forLinks);
		}

		$disableLinksFor = GO::config()->disable_links_for ? GO::config()->disable_links_for : array();
		foreach ($disableLinksFor as $disabledLinkFor) {
			$id = ModelType::model()->findByModelName($disabledLinkFor);
			$modelTypePosition = array_search($id, $types);
			unset($types[$modelTypePosition]);
		}

		$storeParams->getCriteria()->addInCondition('model_type_id', $types);
		
		if (!empty($params['minimumWritePermission']) && $params['minimumWritePermission']!='false')
			$storeParams->getCriteria()->addCondition('level',\GO\Base\Model\Acl::WRITE_PERMISSION,'>=','go_acl');
		
//		$subCriteria = \GO\Base\Db\FindCriteria::newInstance();
//		
//		if(strlen($params['match'])<4){
//			$subCriteria->addCondition('keywords', '%'.trim($params['match'],' *%').'%', 'LIKE','t',false);
//		}else
//		{
//			$str='+'.preg_replace('/[\s]+/',' +', $params['match']);	
//			$subCriteria->addMatchCondition(array('keywords'), $str);
//		}
//		
//		$storeParams->getCriteria()->mergeWith($subCriteria);
		
		return $storeParams;
	}
	
	private function _getAllModelTypes($filesupport=false, $forLinks=false){
		$types=array();
		$stmt = ModelType::model()->find();
		while($modelType = $stmt->fetch()){
			if(class_exists($modelType->model_name)){
				$model = \GO::getModel($modelType->model_name);
				$module = $modelType->model_name == "GO\Base\Model\User" ? "users" : $model->module;
				if(GO::modules()->{$module}){
					if((!$filesupport || $filesupport && $model->hasFiles()) && (!$forLinks || $modelType->model_name != 'GO\\Comments\\Model\\Comment')) {
						$types[]=$modelType->id;
					}
				}
			}
		}
		return $types;

	}
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		$columnModel->formatColumn('iconCls', '"go-model-".str_replace(\'\\\\\',\'_\',$model->model_name)');
		$columnModel->formatColumn('name_and_type', '"(".$model->type.") ".$model->name');
		$columnModel->getColumn('name_and_type')->setModelFormatType('raw');
		$columnModel->formatColumn('model_name_and_id', '$model->model_name.":".$model->model_id');
		return parent::formatColumns($columnModel);
	}
	
		protected function actionModelTypes($params){
		

		$filesupport = false;
		
		if(isset($params['filesupport']))
			$filesupport = $params['filesupport']==="true" || $params['filesupport']==="1"?true:false;
		
		$forLinks = isset($params['for_links']) && ($params['for_links'] === "true" || $params['for_links'] === "1");

		$theseTypesOnly = false;
		
		if(isset($params['filter_model_type_ids'])){
			$theseTypesOnly = json_decode($params['filter_model_type_ids']);
		}
	
		$findParams = FindParams::newInstance();
		
		if(!empty($theseTypesOnly)){
			$findParams->getCriteria()->addInCondition('id', $theseTypesOnly);
		}

		$stmt = ModelType::model()->find($findParams);
		
		$typesString = \GO::config()->get_setting('link_type_filter',\GO::user()->id);
		$typesArr = explode(',',$typesString);

		$disableLinksFor = GO::config()->disable_links_for ? GO::config()->disable_links_for : array();
		if (!is_array($disableLinksFor)) {
			$disableLinksFor = [$disableLinksFor];
		}

		$types=array();
		while($modelType = $stmt->fetch()){
			if (count($disableLinksFor) && in_array($modelType->model_name, $disableLinksFor, true)) {
				continue;
			}

			if(class_exists($modelType->model_name)){
				$model = \GO::getModel($modelType->model_name);

				$module = $modelType->model_name == "GO\Base\Model\User" ? "users" : $model->module;

				if(GO::modules()->{$module}){
					
					if((!$filesupport || $filesupport && $model->hasFiles()) && (!$forLinks || $modelType->model_name != 'GO\\Comments\\Model\\Comment')) {
						$types[$model->localizedName.$modelType->id]=array('id'=>$modelType->id, 'model_name'=>$modelType->model_name, 'name'=>$model->localizedName, 'checked'=>in_array($modelType->id,$typesArr));
					}
				}
			}else
			{
				\GO::debug("Missing class ".$modelType->model_name);
			}
		}
		
		ksort($types);
		
		$response['total']=count($types);
		$response['results']=array_values($types);
	
		
		return $response;		
	}
	
	
	
	protected function actionLinks($params){
		
		$model = \GO::getModel($params['model_name'])->findByPk($params['model_id']);
	
		
		$store = Store::newInstance(\GO\Base\Model\SearchCacheRecord::model());
		
		//$model->unlink($model);
		
		if(!empty($params['unlinks'])){
			$keys = json_decode($params['unlinks'], true);
			
			foreach($keys as $key){
				$key = explode(':',$key);
				
				$linkedModel = \GO::getModel($key[0])->findByPk($key[1]);				
				$model->unlink($linkedModel);				
			}
		}
		
//		if(!empty($params['delete_keys'])){
//			
//			$keys = json_decode($params['delete_keys'], true);
//			
//			foreach($keys as $key){
//				$key = explode(':',$key);
//				
//				$linkedModel = \GO::getModel($key[0])->findByPk($key[1]);				
//				$linkedModel->delete();				
//			}
//		}
		
		//we'll do a full text search in getStoreParams			
//		$params['match']=isset($params["query"]) ? $params["query"] : '';
//		unset($params["query"]);
		
		$storeParams = $store->getDefaultParams($params)->select("t.*,l.description AS link_description");
		
		$storeParams->mergeWith($this->getStoreParams($params));
		
		//if(!empty($params['folder_id']))
		$storeParams->getCriteria ()->addCondition ('folder_id', $params['folder_id'],'=','l');
		
		if(isset($params['types'])){
			$types = json_decode($params['types'], true);
			if(count($types))
				$storeParams->getCriteria ()->addInCondition ('model_type_id', $types);
		}
		
		
		$stmt = \GO\Base\Model\SearchCacheRecord::model()->findLinks($model, $storeParams);
		$store->setStatement($stmt);
		
		$cm = $store->getColumnModel();		
		$cm->formatColumn('iconCls', '"go-model-".str_replace(\'\\\\\',\'_\',$model->model_name)');
		$cm->formatColumn('name_and_type', '"(".$model->type.") ".$model->name');
		$cm->formatColumn('model_name_and_id', '$model->model_name.":".$model->model_id');
		$cm->formatColumn('link_count','\GO::getModel($model->model_name)->countLinks($model->model_id)');

		$data = $store->getData();
		
		$data['permissionLevel']=$model->getPermissionLevel();
		return $data;
	}
	
	
	
	protected function actionEmail($params) {

		$response['success'] = true;
		$response['results'] = array();

		if (empty($params['query']))
			return $response;

		$query = '%' . preg_replace('/[\s*]+/', '%', $params['query']) . '%';



		if (\GO::modules()->addressbook) {
			$response = array('total' => 0, 'results' => array());

			$userContactIds = array();
			$findParams = FindParams::newInstance()
							->searchQuery($query, array("CONCAT(t.first_name,' ',t.middle_name,' ',t.last_name)", 't.email', 't.email2', 't.email3'))
							->select('t.*, "' . addslashes(\GO::t('strUser')) . '" AS ab_name,c.name AS company_name')
							->limit(10)
							->joinModel(array(
					'model' => 'GO\Addressbook\Model\Company',
					'foreignField' => 'id', //defaults to primary key of the remote model
					'localField' => 'company_id', //defaults to "id"
					'tableAlias' => 'c', //Optional table alias
					'type' => 'LEFT' //defaults to INNER,
							));

//			if (!empty($params['requireEmail'])) {
				$criteria = \GO\Base\Db\FindCriteria::newInstance()
								->addCondition("email", "", "!=")
								->addCondition("email2", "", "!=", 't', false)
								->addCondition("email3", "", "!=", 't', false);

				$findParams->getCriteria()->mergeWith($criteria);
//			}

			if (\GO::user()->sort_email_addresses_by_time==1 && \GO::modules()->addressbook) {
				$findParams->joinModel(array(
					'model'=>'GO\Email\Model\ContactMailTime',
					'localTableAlias'=>'t',
					'localField'=>'id',
					'foreignField'=>'contact_id',
					'tableAlias'=>'cmt',
					'type'=>'LEFT',
					'criteria'=>  \GO\Base\Db\FindCriteria::newInstance()->addCondition('user_id', \GO::user()->id, '=', 'cmt')
				))->order('cmt.last_mail_time','DESC');
			}
				
			$stmt = \GO\Addressbook\Model\Contact::model()->findUsers(\GO::user()->id, $findParams);

			$userContactIds = array();

			foreach ($stmt as $contact) {

				$this->_formatContact($response,$contact);

				$userContactIds[] = $contact->id;
			}
			




			if (count($response['results']) < 10) {


				$findParams = FindParams::newInstance()
								->ignoreAcl()
								->select('t.*,c.name AS company_name, a.name AS ab_name')
								->searchQuery($query, array(
										"CONCAT(t.first_name,' ',t.middle_name,' ',t.last_name)",
										't.email',
										't.email2',
										't.email3'
								))
								->joinModel(array(
										'model' => 'GO\Addressbook\Model\Addressbook',
										'foreignField' => 'id', //defaults to primary key of the remote model
										'localField' => 'addressbook_id', //defaults to "id"
										'tableAlias' => 'a', //Optional table alias
										'type' => 'INNER' //defaults to INNER,
								))
								->limit(10-count($response['results']));


				//		if(!empty($params['joinCompany'])){
				$findParams->joinModel(array(
						'model' => 'GO\Addressbook\Model\Company',
						'foreignField' => 'id', //defaults to primary key of the remote model
						'localField' => 'company_id', //defaults to "id"
						'tableAlias' => 'c', //Optional table alias
						'type' => 'LEFT' //defaults to INNER,
				));
				//		}

				$findParams->getCriteria()->addInTemporaryTableCondition('usercontacts', 'id', $userContactIds, 't', true, true);


				if (!empty($params['addressbook_id'])) {
					$abs = array($params['addressbook_id']);
				} else {
					$abs = \GO\Addressbook\Model\Addressbook::model()->getAllReadableAddressbookIds();
				}

				if (!empty($abs)) {

					$findParams->getCriteria()->addInTemporaryTableCondition('readableaddressbooks', 'addressbook_id', $abs);

//					if (!empty($params['requireEmail'])) {
						$criteria = \GO\Base\Db\FindCriteria::newInstance()
										->addCondition("email", "", "!=")
										->addCondition("email2", "", "!=", 't', false)
										->addCondition("email3", "", "!=", 't', false);

						$findParams->getCriteria()->mergeWith($criteria);
//					}

					$stmt = \GO\Addressbook\Model\Contact::model()->find($findParams);
					if (\GO::user()->sort_email_addresses_by_time==1 && GO::modules()->addressbook) {
						$findParams->joinModel(array(
							'model'=>'GO\Email\Model\ContactMailTime',
							'localTableAlias'=>'t',
							'localField'=>'id',
							'foreignField'=>'contact_id',
							'tableAlias'=>'cmt',
							'type'=>'LEFT',
							'criteria'=>  \GO\Base\Db\FindCriteria::newInstance()->addCondition('user_id', GO::user()->id, '=', 'cmt')
						))->order('cmt.last_mail_time','DESC');
					}
						
					$stmt = \GO\Addressbook\Model\Contact::model()->find($findParams);

					$user_ids = array();
					foreach ($stmt as $contact) {
						$this->_formatContact($response,$contact);

						if ($contact->go_user_id)
							$user_ids[] = $contact->go_user_id;
					}
					
					
					if (count($response['results']) < 10) {
						$findParams = FindParams::newInstance()
							->ignoreAcl()
							->searchQuery($query)
							->limit(10-count($response['results']));
						
						$findParams->getCriteria()->addInTemporaryTableCondition('readableaddressbooks', 'addressbook_id', $abs);
						
//						if (!empty($params['requireEmail'])) {
							$criteria = $findParams->getCriteria()
										->addCondition("email", "", "!=");
//						}
						
						$stmt = \GO\Addressbook\Model\Company::model()->find($findParams);

						foreach ($stmt as $company) {
							$record=array();
							$record['name'] = $company->name;							
							
							$l = new \GO\Base\Mail\EmailRecipients();
							$l->addRecipient($company->email, $record['name']);

							$record['info'] = htmlspecialchars((string) $l . ' (' . sprintf(\GO::t('companyFromAddressbook', 'addressbook'), $company->addressbook->name) . ')', ENT_COMPAT, 'UTF-8');
							$record['full_email'] = htmlspecialchars((string) $l, ENT_COMPAT, 'UTF-8');

							$response['results'][] = $record;
							
						}
					}
					
				}
			}
		}else {

			//no addressbook module for this user. Fall back to user search.
			$findParams = FindParams::newInstance()
							->searchQuery($query)
							->select('t.*')
							->limit(10 - count($response['results']));
			
			$findParams->getCriteria()->addCondition('enabled', true);


			$stmt = \GO\Base\Model\User::model()->find($findParams);

			while ($user = $stmt->fetch()) {
				$record['name'] = $user->name;
				$record['user_id'] = $user->id;

				$l = new \GO\Base\Mail\EmailRecipients();
				$l->addRecipient($user->email, $record['name']);

				$record['info'] = htmlspecialchars((string) $l . ' (' . \GO::t('strUser') . ')', ENT_COMPAT, 'UTF-8');
				$record['full_email'] = htmlspecialchars((string) $l, ENT_COMPAT, 'UTF-8');

				$response['results'][] = $record;
			}
		}

		
		return $response;
	}

	private function _formatContact(&$response, $contact) {
		$record['name'] = $contact->name;
		$record['contact_id'] = $contact->id;
		$record['user_id'] = $contact->go_user_id;
		if ($contact->email != "") {
			$l = new \GO\Base\Mail\EmailRecipients();
			$l->addRecipient($contact->email, $record['name']);

			$record['info'] = htmlspecialchars((string) $l . ' (' . sprintf(\GO::t('contactFromAddressbook', 'addressbook'), $contact->addressbook->name) . ')', ENT_COMPAT, 'UTF-8');
			if (!empty($contact->department))
				$record['info'].=' (' . htmlspecialchars($contact->department, ENT_COMPAT, 'UTF-8') . ')';
			$record['full_email'] = htmlspecialchars((string) $l, ENT_COMPAT, 'UTF-8');

			$response['results'][] = $record;
		}

		if ($contact->email2 != "") {
			$l = new \GO\Base\Mail\EmailRecipients();
			$l->addRecipient($contact->email2, $record['name']);

			$record['info'] = htmlspecialchars((string) $l . ' (' . sprintf(\GO::t('contactFromAddressbook', 'addressbook'), $contact->addressbook->name) . ')', ENT_COMPAT, 'UTF-8');
			if (!empty($contact->department))
				$record['info'].=' (' . htmlspecialchars($contact->department, ENT_COMPAT, 'UTF-8') . ')';
			$record['full_email'] = htmlspecialchars((string) $l, ENT_COMPAT, 'UTF-8');

			$response['results'][] = $record;
		}

		if ($contact->email3 != "") {
			$l = new \GO\Base\Mail\EmailRecipients();
			$l->addRecipient($contact->email3, $record['name']);

			$record['info'] = htmlspecialchars((string) $l . ' (' . sprintf(\GO::t('contactFromAddressbook', 'addressbook'), $contact->addressbook->name) . ')', ENT_COMPAT, 'UTF-8');
			if (!empty($contact->department))
				$record['info'].=' (' . htmlspecialchars($contact->department, ENT_COMPAT, 'UTF-8') . ')';
			$record['full_email'] = htmlspecialchars((string) $l, ENT_COMPAT, 'UTF-8');

			$response['results'][] = $record;
		
		}

	}

}