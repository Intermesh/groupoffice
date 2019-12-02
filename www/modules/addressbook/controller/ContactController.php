<?php
/*
 * Copyright Intermesh BV
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl>
 * @author Wilmar van Beusekom <wilmar@intermesh.nl>
 *
 */


namespace GO\Addressbook\Controller;
use GO;

//use function GuzzleHttp\json_decode;

class ContactController extends \GO\Base\Controller\AbstractModelController{
	
	protected $model = 'GO\Addressbook\Model\Contact';
	
	protected function allowGuests() {
		return array('photo');
	}
	
	protected function actionFindForUser($params) {
		$contact = GO\Addressbook\Model\Contact::model()->findSingleByAttribute('go_user_id', $params['user_id']);
		
		return ['success' => true, 'contact_id' => $contact ? $contact->id : 0];
	}
		
	protected function beforeSubmit(&$response, &$model, &$params) {	
		
		//workaroud extjs iframe hack for file upload
//		$_SERVER["HTTP_X_REQUESTED_WITH"] = "XMLHttpRequest";
		
		$this->checkMaxPostSizeExceeded();
		
		//if user typed in a new company name manually we set this attribute so a new company will be autocreated.
		if(isset($params['company_id']) && !is_numeric($params['company_id'])){
			$model->company_name = $params['company_id'];
		}
		
		
		return parent::beforeSubmit($response, $model, $params);
	}
	
	protected function afterSubmit(&$response, &$model, &$params, $modifiedAttributes) {
		
		$stmt = \GO\Addressbook\Model\Addresslist::model()->find(\GO\Base\Db\FindParams::newInstance()->permissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION));
		while($addresslist = $stmt->fetch()){
			$linkModel = $addresslist->hasManyMany('contacts', $model->id);
			$mustHaveLinkModel = isset($params['addresslist_' . $addresslist->id]);
			if ($linkModel && !$mustHaveLinkModel) {
				$linkModel->delete();
			}
			if (!$linkModel && $mustHaveLinkModel) {
				$addresslist->addManyMany('contacts',$model->id);
			}
		}		
		
		
		if(!empty($params['delete_photo'])){
			$model->removePhoto();
			$model->save();
		}
		if (isset($_FILES['image']['tmp_name'][0]) && is_uploaded_file($_FILES['image']['tmp_name'][0])) {
		
			
			$destinationFile = new \GO\Base\Fs\File(\GO::config()->getTempFolder()->path().'/'.$_FILES['image']['name'][0]);
			
			move_uploaded_file($_FILES['image']['tmp_name'][0], $destinationFile->path());
			
			$model->setPhoto($destinationFile);
			$model->save();
			$response['photo_url'] = $model->photoThumbURL;
			$response['original_photo_url'] = $model->photoURL;
		}elseif(!empty($params['download_photo_url'])){
			
			$file = \GO\Base\Fs\File::tempFile();	
			$c = new \GO\Base\Util\HttpClient();
			
			if(!$c->downloadFile($params['download_photo_url'], $file))
				throw new \Exception("Could not download photo from: '".$params['download_photo_url']."'");
						
			$model->setPhoto($file);
			$model->save();					
			$response['photo_url'] = $model->photoThumbURL;
			$response['original_photo_url'] = $model->photoURL;
		}
		
		
		return parent::afterSubmit($response, $model, $params, $modifiedAttributes);
	}
	
	
	protected function afterLoad(&$response, &$model, &$params) {
		
		if (\GO::modules()->customfields)
			$response['customfields'] = \GO\Customfields\Controller\CategoryController::getEnabledCategoryData("GO\Addressbook\Model\Contact", $model->addressbook_id);
		
		$response['data']['photo_url']=$model->photoThumbURL;		
		$response['data']['original_photo_url']=$model->photoURL;
		
		if ($model->action_date > 0)
			$response['data']['action_date'] = \GO\Base\Util\Date::get_timestamp($model->action_date,false);
		
		$stmt = $model->addresslists();
		while($addresslist = $stmt->fetch()){
			$response['data']['addresslist_'.$addresslist->id]=1;
		}
		
		return parent::afterLoad($response, $model, $params);
	}	
	
	protected function remoteComboFields() {
		return array(
				'addressbook_id'=>'$model->addressbook->name',
				'company_id'=>'$model->company->name'
				);
	}
	
	
	protected function actionPhoto($params){
		$contact = \GO\Addressbook\Model\Contact::model()->findByPk($params['id'], false, true);
		
		//all user photos visible
		if(!GO::user() || (!$contact->go_user_id && !$contact->getPermissionLevel())) {
			throw new \GO\Base\Exception\AccessDenied();
		}
		
		\GO\Base\Util\Http::outputDownloadHeaders($contact->getPhotoFile(), true, true);
		$contact->getPhotoFile()->output();
	}
	
	
	protected function afterDisplay(&$response, &$model, &$params) {
			
		$response['data']['name']=$model->name;
		$response['data']['photo_url']=$model->photoThumbURL;
		$response['data']['original_photo_url']=$model->photoURL;
		$response['data']['addressbook_name']=$model->addressbook->name;
		
		$company = $model->company();
		if($company){					
			$response['data']['company_name'] = $company->name;
			$response['data']['company_name2'] = $company->name2;
			$response['data']['company_formatted_address'] = nl2br($company->getFormattedAddress());
			$response['data']['company_google_maps_link']=\GO\Base\Util\Common::googleMapsLink(
						$company->address, $company->address_no,$company->city, $company->country);
			
			$response['data']['company_formatted_post_address'] = nl2br($company->getFormattedPostAddress());
			$response['data']['company_google_maps_post_link']=\GO\Base\Util\Common::googleMapsLink(
						$company->post_address, $company->post_address_no,$company->post_city, $company->post_country);
			
			
			$response['data']['company_email'] = $company->email;
			$response['data']['company_phone'] = $company->phone;
		} else {
			$response['data']['company_name'] = '';
			$response['data']['company_name2'] = '';
			$response['data']['company_formatted_address'] = '';
			$response['data']['company_google_maps_link']='';
			$response['data']['company_formatted_post_address'] = '';
			$response['data']['company_google_maps_post_link']='';
			$response['data']['company_email'] = '';
			$response['data']['company_phone'] = '';
		}
		
		$response['data']['google_maps_link']=\GO\Base\Util\Common::googleMapsLink(
						$model->address, $model->address_no,$model->city, $model->country);
		
		$response['data']['formatted_address']=nl2br($model->getFormattedAddress());
		
		$response['data']['action_date']=\GO\Base\Util\Date::get_timestamp($model->action_date,false);
		
		if(\GO::modules()->customfields && isset($response['data']['customfields']) && \GO\Customfields\Model\DisableCategories::isEnabled("GO\Addressbook\Model\Contact", $model->addressbook_id)){

			$ids = \GO\Customfields\Model\EnabledCategory::model()->getEnabledIds("GO\Addressbook\Model\Contact", $model->addressbook_id);
			
			$enabled = array();
			foreach($response['data']['customfields'] as $cat){
				if(in_array($cat['id'], $ids)){
					$enabled[]=$cat;
				}
			}
			$response['data']['customfields']=$enabled;
		}
		
		
		if (\GO::modules()->isInstalled('customfields')) {
			
			$response['data']['items_under_blocks'] = array();
			
			$enabledBlocksStmt = \GO\Customfields\Model\EnabledBlock::getEnabledBlocks($model->addressbook_id, 'GO\Addressbook\Model\Addressbook', $model->className());
			foreach ($enabledBlocksStmt as $i => $enabledBlockModel) {
				
				$items = $enabledBlockModel->block->getItemNames($model->id,$model->name);
				
				if (!empty($items)) {
					$blockedItemsEl = array(
						'id' => $i,
						'block_name' => $enabledBlockModel->block->name,
						'items' => $items
					);

					$blockedItemsEl['model_name'] = !empty($items[0]) ? $items[0]['model_name'] : '';
					$modelNameArr = explode('_', $blockedItemsEl['model_name']);
					$blockedItemsEl['type'] = !empty($modelNameArr[3]) ? $modelNameArr[3] : '';

					$response['data']['items_under_blocks'][] = $blockedItemsEl;
				}
			}
			
		}
		$response['data']['panelId'] = 'ab-contact-detail'; // backward compat for CF
		
		return parent::afterDisplay($response, $model, $params);
	}
	
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		
		$sortAlias = \GO::user()->sort_name=="first_name" ? array('first_name','last_name') : array('last_name','first_name');

		$columnModel->formatColumn('name','$model->getName(\GO::user()->sort_name)', array(),$sortAlias, \GO::t("Name"));
		$columnModel->formatColumn('company_name','$model->company_name', array(),'', \GO::t("Company", "addressbook"));
		$columnModel->formatColumn('ab_name','$model->ab_name', array(),'', \GO::t("Address book", "addressbook"));
		$columnModel->formatColumn('age', '$model->age', array(), 'birthday');
		$columnModel->formatColumn('action_date', '$model->getActionDate()', array(), 'action_date');
		$columnModel->formatColumn('username', '$model->user->displayName', array(), 'user_id');
		$columnModel->formatColumn('musername', '$model->mUser->displayName', array(), 'muser_id');
		
		$columnModel->formatColumn('cf', '$model->id.":".$model->name');//special field used by custom fields. They need an id an value in one.)
		return parent::formatColumns($columnModel);
	}
	


	protected function beforeStoreStatement(array &$response, array &$params, \GO\Base\Data\AbstractStore &$store, \GO\Base\Db\FindParams $storeParams) {
		if(!empty($params['filters'])){
			$abMultiSel = new \GO\Base\Component\MultiSelectGrid(
							'books', 
							"GO\Addressbook\Model\Addressbook",$store, $params, true);
			
			$abMultiSel->addSelectedToFindCriteria($storeParams, 'addressbook_id');
	//		$abMultiSel->setButtonParams($response);
	//		$abMultiSel->setStoreTitle();

			$addresslistMultiSel = new \GO\Base\Component\MultiSelectGrid(
							'addresslist_filter', 
							"GO\Addressbook\Model\Addresslist",$store, $params, false);

			if(!empty($params['addresslist_filters']))
			{
				$addresslistMultiSel->addSelectedToFindCriteria($storeParams, 'addresslist_id','ac');

				if(count($addresslistMultiSel->selectedIds)){
					//we need to join the addresslist link model if a filter for the addresslist is enabled.
					$storeParams->join(\GO\Addressbook\Model\AddresslistContact::model()->tableName(),
									\GO\Base\Db\FindCriteria::newInstance()->addCondition('id', 'ac.contact_id', '=', 't', true, true),
									'ac'
						);
					
					$storeParams->group('t.id');
				}
			}
			
			if (!empty($params['onlyCurrentActions'])) {
				$storeParams->getCriteria()
					->addCondition('action_date', 0, '>', 't')
					->addCondition('action_date', time(), '<=', 't');
			}
		}
		
		return parent::beforeStoreStatement($response, $params, $store, $storeParams);
	}
	
	/*
	 * This function initiates the contact filters by:
	 * - search query (happens automatically in GO base class)
	 * - by clicked letter
	 * - checked addresslists
	 */
	protected function getStoreParams($params) {	
	
		$criteria = \GO\Base\Db\FindCriteria::newInstance()
			->addModel(\GO\Addressbook\Model\Contact::model(),'t');
				
		// Filter by clicked letter
		if (!empty($params['clicked_letter'])) {
			if ($params['clicked_letter'] == '[0-9]') {
				$query = '^[0-9].*$';
				$query_type = 'REGEXP';
			} else {
				$query = $params['clicked_letter'] . '%';
				$query_type = 'LIKE';
			}
			//$criteria->addRawCondition('CONCAT_WS(`t`.`first_name`,`t`.`middle_name`,`t`.`last_name`)', ':query', $query_type);
			$queryCrit = \GO\Base\Db\FindCriteria::newInstance()			
				->addRawCondition(\GO::user()->sort_name, ':query', $query_type)
				->addBindParameter(':query', $query);
				
			$criteria->mergeWith($queryCrit);
		}
		
		$searchFields = \GO\Addressbook\Model\Contact::model()->getFindSearchQueryParamFields();
		$searchFields[]="c.name";

		$selectFields = \GO\Addressbook\Model\Contact::model()->getDefaultFindSelectFields().
						',c.name AS company_name, addressbook.name AS ab_name, CONCAT_WS(\' \',`t`.`first_name`,`t`.`middle_name`,`t`.`last_name`) AS name';
		
		$storeParams = \GO\Base\Db\FindParams::newInstance()
			->export("contact")
			->joinAclFieldTable()
			->criteria($criteria)		
			->searchFields($searchFields)
			->joinModel(array(
				'model'=>'GO\Addressbook\Model\Company',
	 			'foreignField'=>'id', //defaults to primary key of the remote model
	 			'localField'=>'company_id', //defaults to "id"
	 			'tableAlias'=>'c', //Optional table alias
	 			'type'=> 'LEFT' //defaults to INNER,
	 			
			))			
			->select($selectFields);
	
		return $storeParams;
		
	}

	public function actionMergeEmailWithContact($params) {
		$email = (isset($params['email']) && $params['email']) ? $params['email'] : '';
		$replaceEmail = (isset($params['replace_email']) && $params['replace_email']) ? $params['replace_email'] : '';
		$contactId = (isset($params['contact_id']) && $params['contact_id']) ? $params['contact_id'] : 0;

		$response['success'] = false;
		if($email && $contactId)
		{
			$contactModel = \GO\Addressbook\Model\Contact::model()->findByPk($contactId);
			$emailAddresses = array($contactModel->email, $contactModel->email2, $contactModel->email3);

			if(!$replaceEmail)
			{		    		    		    
				if(!in_array($email, $emailAddresses))
				{
					$index = array_search('', $emailAddresses);
					if($index === false) {
						$response['addresses'] = array(array('name' => $contactModel->email), array('name' => $contactModel->email2), array('name' => $contactModel->email3));
						$response['contact_name'] = $contactModel->name;
					} else{
						$field = ($index == 0) ? 'email' : 'email'.($index+1);
						$contactModel->$field = $email;
						$contactModel->save();
					}	
					$response['success'] = true;
				} else {
					$response['feedback'] = \GO::t("E-mail address is already added to this contact", "addressbook");
				}
			} else {
				$index = array_search($replaceEmail, $emailAddresses);
				if($index === false)
				{
					$response['feedback'] = \GO::t("E-mail address wasn't found", "addressbook");
				}else
				{
					$field = ($index == 0) ? 'email' : 'email'.($index+1);
					$contactModel->$field = $email;
					$contactModel->save();
					$response['success']=true;
				}		        
			}	
	  }
		return $response;
	}
	
	function actionEmployees($params) {
		$result['success'] = false;
		$company = \GO\Addressbook\Model\Company::model()->findByPk($params['company_id']);
		
		if(!$company->checkPermissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION))
			throw new \GO\Base\Exception\AccessDenied();
		
		if(isset($params['delete_keys']))
		{
			$response['deleteSuccess'] = true;
			try{
				$delete_contacts = json_decode(($params['delete_keys']));

				foreach($delete_contacts as $id)
				{
					$contact = \GO\Addressbook\Model\Contact::model()->findByPk($id);
					$contact->setAttributes(array('id'=>$id,'company_id'=>0));
					$contact->save();
				}
			}
			catch (\Exception $e)
			{
				$response['deleteFeedback'] = $strDeleteError;
				$response['deleteSuccess'] = false;
			}
		}

		if(isset($params['add_contacts']))
		{
			$add_contacts = json_decode(($params['add_contacts']));

			foreach($add_contacts as $id)
			{
				$contact = \GO\Addressbook\Model\Contact::model()->findByPk($id);
				$contact->setAttributes(array('id'=>$id,'company_id'=>$params['company_id']));
				$contact->save();
			}			
		}

		$params['field'] = isset($params['field']) ? ($params['field']) : 'addressbook_name';

		$store = new \GO\Base\Data\Store($this->getStoreColumnModel());	
		$this->formatColumns($store->getColumnModel());

		$response['success']=true;
		
		$storeParams = $store->getDefaultParams($params)->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('company_id',$params['company_id']))
						->mergeWith($this->getStoreParams($params));
		$store->setStatement(call_user_func(array('GO\Addressbook\Model\Contact','model'))->find($storeParams));
		return array_merge($response, $store->getData());
	}
	
	protected function actionChangeAddressbook($params) {
		$ids = json_decode($params['items']);
		
		$response['success'] = true;
		$response['failedToMove'] = array();
		
		foreach ($ids as $id) {
			$model = \GO\Addressbook\Model\Contact::model()->findByPk($id);
			try{
				
				if ($model->company) {
					//the company will move it's contact along too.
					$model->company->addressbook_id=$params['book_id'];
					$model->company->save();
				} else {

					$model->addressbook_id=$params['book_id'];
					$model->save();				
				}
			}catch(\GO\Base\Exception\AccessDenied $e){
				$response['failedToMove'][]=$model->id;
			}
		}
		$response['success']=empty($response['failedToMove']);
		
		if(!$response['success']){
			$count = count($response['failedToMove']);
			$response['feedback'] = sprintf(\GO::t("%s item(s) cannot be moved, you do not have the right permissions."),$count);
		}
		
		return $response;
	}
	

	protected function beforeHandleAdvancedQuery ($advQueryRecord, \GO\Base\Db\FindCriteria &$criteriaGroup, \GO\Base\Db\FindParams &$storeParams) {
		if($advQueryRecord['comparator'] == 'RADIUS') {
			list($lat, $long) = json_decode($advQueryRecord['field'], true);
			$defaultSelect = 't.*, "'.addslashes(\GO::t('strUser')).'" AS ab_name,c.name AS company_name';
			$storeParams->debugSql()->select($defaultSelect.',( 6371 * acos( cos( radians('.$lat.') ) * cos( radians( t.latitude ) ) * cos( radians( t.longitude ) - radians('.$long.') ) + sin( radians('.$lat.') ) * sin( radians( t.latitude ) ) ) ) AS distance');
			$storeParams->having('distance < '.floatval($advQueryRecord['value']));
			return false;
		}

		switch ($advQueryRecord['field']) {
			case 'companies.name':
				$storeParams->join(
					\GO\Addressbook\Model\Company::model()->tableName(),
					\GO\Base\Db\FindCriteria::newInstance()->addRawCondition('`t`.`company_id`','`companies'.$advQueryRecord['id'].'`.`id`'),
					'companies'.$advQueryRecord['id']
				);
				$criteriaGroup->addRawCondition(
					'companies'.$advQueryRecord['id'].'.name',
					':company_name'.$advQueryRecord['id'],
					$advQueryRecord['comparator'],
					$advQueryRecord['andor']=='AND'
				);
				$criteriaGroup->addBindParameter(':company_name'.$advQueryRecord['id'], $advQueryRecord['value']);
				return false;
				break;
			case 'contact_name':
				$criteriaGroup->addRawCondition(
					'CONCAT_WS(\' \',`t`.`first_name`,`t`.`middle_name`,`t`.`last_name`)',
					':contact_name'.$advQueryRecord['id'],
					$advQueryRecord['comparator'],
					$advQueryRecord['andor']=='AND'
				);
				$criteriaGroup->addBindParameter(':contact_name'.$advQueryRecord['id'], $advQueryRecord['value']);
				return false;
				break;
			default:
				//parent::integrateInSqlSearch($advQueryRecord, $findCriteria, $storeParams);
				return true;
				break;
		}
	}
	
	
	
	
	
	protected function afterAttributes(&$attributes, &$response, &$params, \GO\Base\Db\ActiveRecord $model) {
		unset($attributes['t.company_id']);
		//$attributes['name']=\GO::t("Name");
		$attributes['companies.name']=array('name'=>'companies.name','label'=>\GO::t("Company", "addressbook"));
		
		
		/**
		 * add the writebel addresslists to te maping store
		 */
		$findParams = new \GO\Base\Db\FindParams();
		$findParams->permissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION);
		
		$addresslists = \GO\Addressbook\Model\Addresslist::model()->find($findParams);
		foreach ($addresslists as $rec) {
			
			$attributes['addresslist_'. $rec->id] = array('name'=>'addresslist.addresslist_' . $rec->id, 'label'=>'' .GO::t("Address Lists", "addressbook"). ': ' .$rec->name, 'gotype'=>'boolean');
			
		}
		
		return parent::afterAttributes($attributes, $response, $params, $model);
	}
	
    /**
     * Before importing a contact in the database first check if the company name of this contact
     * Is a company that excists in the database. If not create a company. After this set the id
     * of the create company to the contact we insert.
     * 
     * If the email addres set to a contact does not validate. Remove it so import wont fail
     */
	protected function beforeImport($params, &$model, &$attributes, $record) {	
		
		$impBasParams = json_decode($params['importBaseParams'],true);
		$addressbookId = $impBasParams['addressbook_id'];
		
		if(!empty($attributes['Company']))
			$companyName = $attributes['Company'];
		else if(!empty($attributes['company']))
			$companyName = $attributes['company'];
		else if(!empty($attributes['company_name']))
			$companyName = $attributes['company_name'];
		else if(!empty($attributes['companyName']))
			$companyName = $attributes['companyName'];	
		else if(!empty($attributes['name']))
			$companyName = $attributes['name'];	
		
		if(!empty($companyName)) {
			$companyModel = \GO\Addressbook\Model\Company::model()->find(
				\GO\Base\Db\FindParams::newInstance()
					->single()
					->criteria(
						\GO\Base\Db\FindCriteria::newInstance()
							->addCondition('name',$companyName)
							->addCondition('addressbook_id',$addressbookId)
					)
			);
			if (empty($companyModel)) {
				$companyModel = new \GO\Addressbook\Model\Company();
				$companyModel->setAttributes(array(
					'name' => $companyName,
					'addressbook_id' => $addressbookId
				));
				$companyModel->save();
			}
			$model->company_id = $companyModel->id;
		}
		
        if(isset($attributes['email']) && !\GO\Base\Util\StringHelper::validate_email($attributes['email']))
          unset($attributes['email']);
        if(isset($attributes['email2']) && !\GO\Base\Util\StringHelper::validate_email($attributes['email2']))
          unset($attributes['email2']);
        if(isset($attributes['email3']) && !\GO\Base\Util\StringHelper::validate_email($attributes['email3']))
          unset($attributes['email3']);
        
		return parent::beforeImport($params, $model, $attributes, $record);
	}
	
	protected function actionHandleAttachedVCard($params) {
		$account = \GO\Email\Model\Account::model()->findByPk($params['account_id']);
		$imap = $account->openImapConnection($params['mailbox']);
		
		$tmpFile =\GO\Base\Fs\File::tempFile($params['filename']);
		$imap->save_to_file($params['uid'], $tmpFile->path(), $params['number'], $params['encoding']);
		
		if(!isset($params['importVCard'])) {
			\GO\Base\Util\Http::outputDownloadHeaders($tmpFile);
			echo $tmpFile->getContents();
			return;
		}
		
		$options = \Sabre\VObject\Reader::OPTION_FORGIVING + \Sabre\VObject\Reader::OPTION_IGNORE_INVALID_LINES;
		$card = \Sabre\VObject\Reader::read($tmpFile->getContents(),$options);
		$contact = new \GO\Addressbook\Model\Contact();
		$contact->importVObject($card, array(), false);
		
		//format utf-8 attributes
		foreach($contact->getAttributes('raw') as $key => $value) {
			try {
				$contact->{$key} = utf8_decode($value);
			} catch (\Exception $e) {}
		}
		
		//GO\Base\Util\Http::outputDownloadHeaders($tmpFile);
		return array('success'=>true, 'contacts'=>array($contact->getAttributes()));
		//echo $tmpFile->getContents();

	}
	
	/**
	 * Function exporting addressbook contents to VCFs. Must be called from export.php.
	 * @param type $params 
	 */
	protected function actionVCard($params) {
		$contact = \GO\Addressbook\Model\Contact::model()->findByPk($params['id']);
		
		$filename = $contact->name.'.vcf';
		header("Content-Type: text/plain");
//		\GO\Base\Util\Http::outputDownloadHeaders(new \GO\Base\FS\File($filename));		
		
		$vobject = $contact->toVObject();
		
		if(!empty($params['vcard21']))
			\GO\Base\VObject\Reader::convertVCard30toVCard21($vobject);
		
		echo $vobject->serialize();
	}
	
	
	private function createStream($data) {

			$stream = fopen('php://memory','r+');
			fwrite($stream, $data);
			rewind($stream);
			return $stream;

	}
	
	
	protected function actionImportVCard($params){
		
		$summaryLog = new \GO\Base\Component\SummaryLog();
		
		$readOnly = !empty($params['readOnly']);
		
		if(isset($_FILES['files']['tmp_name'][0]))
			$params['file'] = $_FILES['files']['tmp_name'][0];
		
		if (!empty($params['importBaseParams'])) {
			$importBaseParams = json_decode($params['importBaseParams'],true);
			$params['addressbook_id'] = $importBaseParams['addressbook_id'];
		}
		
		$file = new \GO\Base\Fs\File($params['file']);
		$file->convertToUtf8();
		
		$options = \Sabre\VObject\Reader::OPTION_FORGIVING + \Sabre\VObject\Reader::OPTION_IGNORE_INVALID_LINES;
		$vcards = new \Sabre\VObject\Splitter\VCard(fopen($file->path(),'r+'), $options);


		unset($params['file']);
		$nr=0;
		if ($readOnly)
			$contactsAttr = array();
		while($vObject=$vcards->getNext()) {
			$nr++;
			\GO\Base\VObject\Reader::convertVCard21ToVCard30($vObject);
			$contact = new \GO\Addressbook\Model\Contact();
			try {
				if ($contact->importVObject($vObject, $params, !$readOnly))
					$summaryLog->addSuccessful();
				if ($readOnly)
					$contactsAttr[] = $contact->getAttributes('formatted');
			} catch (\Exception $e) {
				$summaryLog->addError($nr, $e->getMessage());
			}
		}
		
		$response = $summaryLog->getErrorsJson();
		if ($readOnly) {
			$response['contacts'] = $contactsAttr;
		}
		$response['successCount'] = $summaryLog->getTotalSuccessful();
		$response['totalCount'] = $summaryLog->getTotal();
		$response['success']=true;
		
		return $response;
	}
	
	/**
	 * The actual call to the import CSV function
	 * 
	 * @param array $params
	 * @return array $response 
	 */
	protected function actionImportCsv($params){		
		$params['file'] = $_FILES['files']['tmp_name'][0];
		$params['importType'] = 'Csv';
		$summarylog = parent::actionImport($params);
		$response = $summarylog->getErrorsJson();
		$response['successCount'] = $summarylog->getTotalSuccessful();
		$response['totalCount'] = $summarylog->getTotal();
		$response['success'] = true;
		return $response;
	}
	
	
	/**
	 * The actual call to the import XLS function
	 * 
	 * @param array $params
	 * @return array $response 
	 */
	protected function actionImportXls($params){		
		$params['file'] = $_FILES['files']['tmp_name'][0];
		$params['importType'] = 'Xls';
		$summarylog = parent::actionImport($params);
		$response = $summarylog->getErrorsJson();
		$response['successCount'] = $summarylog->getTotalSuccessful();
		$response['totalCount'] = $summarylog->getTotal();
		$response['success'] = true;
		return $response;
	}
	
	protected function actionSelectContact($params){
		$cfId = $params["customfield_id"];
		$cfModel = \GO\Customfields\Model\Field::model()->findByPk($cfId);

		$options = $cfModel->options;
		$optionsDecoded = json_decode($options,true);
		$addressBookIds = $optionsDecoded["addressbookIds"];
		$params['addressbook_ids'] = "[" . $addressBookIds . "]";
		$response = array('total'=>0, 'results'=>array());
		
		if(isset($params['contact_id'])){
			
			$findParams = \GO\Base\Db\FindParams::newInstance()
			->joinModel(array(
						'model'=>'GO\Addressbook\Model\Company',
						'foreignField'=>'id', //defaults to primary key of the remote model
						'localField'=>'company_id', //defaults to "id"
						'tableAlias'=>'c', //Optional table alias
						'type'=>'LEFT' //defaults to INNER,

					));
			
			$contact = \GO\Addressbook\Model\Contact::model()->findByPk($params['contact_id'],$findParams);

			$record =$contact->getAttributes();
			//$record['name']=$contact->name;
			$record['cf']=$contact->id.":".$contact->name;

			$response['results'][]=$record;
			$response['total']++;			

			return $response;
		}
		
		$query = '%'.preg_replace ('/[\s*]+/','%', $params['query']).'%'; 
		
		
		
		$userContactIds=array();
		if(empty($params['addressbook_id']) && (empty($params['addressbook_ids']) || $params['addressbook_ids'] == '[]') && empty($params['no_user_contacts']) && empty($params['customfield_id'])) {
			$findParams = \GO\Base\Db\FindParams::newInstance()
					->searchQuery($query,
									array("CONCAT(t.first_name,' ',t.middle_name,' ',t.last_name)",'t.email','t.email2','t.email3'))
					->select('t.*, "'.addslashes(\GO::t("User")).'" AS ab_name,c.name AS company_name')
					->limit(10)
					->joinModel(array(
						'model'=>'GO\Addressbook\Model\Company',
						'foreignField'=>'id', //defaults to primary key of the remote model
						'localField'=>'company_id', //defaults to "id"
						'tableAlias'=>'c', //Optional table alias
						'type'=>'LEFT' //defaults to INNER,

					));
			
			if(!empty($params['requireEmail'])){
				$criteria = \GO\Base\Db\FindCriteria::newInstance()
								->addCondition("email", "","!=")
								->addCondition("email2", "","!=",'t',false)
								->addCondition("email3", "","!=",'t',false);

				$findParams->getCriteria()->mergeWith($criteria);
			}

//			$stmt = \GO\Addressbook\Model\Contact::model()->findUsers(\GO::user()->id, $findParams);
//			
//			$userContactIds=array();
//		
//			foreach($stmt as $contact){
//				$record =$contact->getAttributes();
//				$record['name']=$contact->name;
//				$record['cf']=$contact->id.":".$contact->name;
//
//				$response['results'][]=$record;
//				$response['total']++;	
//				
//				$userContactIds[]=$contact->id;
//			}
					
		}

		if(count($response['results'])<10){
		
		
			$findParams = \GO\Base\Db\FindParams::newInstance()
				->ignoreAcl()
				->select('t.*,c.name AS company_name, a.name AS ab_name')
				->searchQuery($query,
								array(
										"CONCAT(t.first_name,' ',t.middle_name,' ',t.last_name, ' ',a.name)",
										't.email',
										't.email2',
										't.email3'
										))					
				->joinModel(array(
					'model'=>'GO\Addressbook\Model\Addressbook',
					'foreignField'=>'id', //defaults to primary key of the remote model
					'localField'=>'addressbook_id', //defaults to "id"
					'tableAlias'=>'a', //Optional table alias
					'type'=>'INNER' //defaults to INNER,

				))			
				->limit(10-count($response['results']));


	//		if(!empty($params['joinCompany'])){
				$findParams->joinModel(array(
					'model'=>'GO\Addressbook\Model\Company',
					'foreignField'=>'id', //defaults to primary key of the remote model
					'localField'=>'company_id', //defaults to "id"
					'tableAlias'=>'c', //Optional table alias
					'type'=>'LEFT' //defaults to INNER,

				));
	//		}
			
			$findParams->getCriteria()->addInTemporaryTableCondition('usercontacts', 'id', $userContactIds,'t',true,true);

			if(!empty($params['addressbook_ids']) && $params['addressbook_ids'] != '[]'){

				if(!empty($params['addressbook_id'])){
					\GO::debug('Given addressbook_ids array and addressbook_id parameters. Truncate addressbook_id parameter');
					$params['addressbook_id'] = false;
				}

				$abs = json_decode($params['addressbook_ids']);

			}	else if (!empty($params['addressbook_id'])){		
				$abs= array($params['addressbook_id']);
			} else if (GO::modules()->customfields && !empty($params['customfield_id'])) {
				$colId = preg_replace('/[\D]/','',$params['customfield_id']);
				$customfieldModel = GO\Customfields\Model\Field::model()->findByPk($colId);
				$abs =
						!empty($customfieldModel->addressbook_ids)
						? explode(',',$customfieldModel->addressbook_ids)
						: \GO\Addressbook\Model\Addressbook::model()->getAllReadableAddressbookIds();
				$readableAddressbookIds = \GO\Addressbook\Model\Addressbook::model()->getAllReadableAddressbookIds();
				
				// Remove duplicate id's from the array to prevent a SQL error (Duplicate key for ...)
				$abs = array_unique($abs);
				
				foreach ($abs as $k => $abId) {
					if (!in_array($abId,$readableAddressbookIds))
						unset($abs[$k]);
				}
			} else {
				$abs = \GO\Addressbook\Model\Addressbook::model()->getAllReadableAddressbookIds();			
			}

			if(!empty($abs)){

				$findParams->getCriteria ()->addInTemporaryTableCondition('addressbooks','addressbook_id', $abs);

				if(!empty($params['requireEmail'])){
					$criteria = \GO\Base\Db\FindCriteria::newInstance()
									->addCondition("email", "","!=")
									->addCondition("email2", "","!=",'t',false)
									->addCondition("email3", "","!=",'t',false);

					$findParams->getCriteria()->mergeWith($criteria);
				}

				$stmt = \GO\Addressbook\Model\Contact::model()->find($findParams);

//				$user_ids=array();
				foreach($stmt as $contact){
					$record =$contact->getAttributes();
					//$record['name']=$contact->name;
					$record['cf']=$contact->id.":".$contact->name;

					$response['results'][]=$record;
					$response['total']++;			

//					if($contact->go_user_id)
//						$user_ids[]=$contact->go_user_id;
				}
			}
		}
		
		
		return $response;
		
	}
	
	protected function actionSearchEmail($params) {
		
		$response['success']=true;
		$response['results']=array();
		
		if(empty($params['query']))
			return $response;
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->searchQuery('%'.preg_replace ('/[\s*]+/','%', $params['query']).'%')
						->select('t.*, addressbook.name AS ab_name, c.name AS company_name')
						//->limit(20)
						->joinModel(array(
							'model'=>'GO\Addressbook\Model\Company',
							'foreignField'=>'id', //defaults to primary key of the remote model
							'localField'=>'company_id', //defaults to "id"
							'tableAlias'=>'c', //Optional table alias
							'type'=>'LEFT' //defaults to INNER,
						));

		if(!isset($params['limit']))
			$findParams->limit(20);
		else
			$findParams->limit($params['limit']);
		
		$findParams->calcFoundRows();
		
		if(isset($params['start']))
			$findParams->start($params['start']);
		
		$sortAlias = \GO::user()->sort_name=="first_name" ? array('first_name','last_name') : array('last_name','first_name');
		
		if(isset($params['sort']) && isset($params['dir'])){
			
			if($params['sort'] == 'name' )
				$findParams->order($sortAlias,$params['dir']);
			else
				$findParams->order($params['sort'],$params['dir']);
		}else{
			$findParams->order($sortAlias);
		}
		
		$criteria = \GO\Base\Db\FindCriteria::newInstance()
							->addCondition("email", "","!=")
							->addCondition("email2", "","!=",'t',false)
							->addCondition("email3", "","!=",'t',false);

		$findParams->getCriteria()->mergeWith($criteria);

		$stmt = \GO\Addressbook\Model\Contact::model()->find($findParams);
		
		$response['total']= $stmt->foundRows;

		while ($contact = $stmt->fetch()) {
			
			$record = $contact->getAttributes();
			
			if ($contact->email != "")				
				$response['results'][] = $record;

			if ($contact->email2 != "") {
				$record['email']=$contact->email2;
				$response['results'][] = $record;
			}

			if ($contact->email3 != "") {
				$record['email']=$contact->email3;				
				$response['results'][] = $record;
			}
		}
		
		return $response;
	}
	
	
	
	protected function afterImport(&$model, &$attributes, $record) {
		
		
		foreach ($attributes as $key => $value) {
			
			/**
			 * pares the mapping loking for 'addresslist_'
			 */
			
			if(stripos($key, 'addresslist_') !== FALSE) {
				$data = explode('_', $key);
				$id = $data[1];
				
				if($value == 1) {
					
					$addresslistModel = \GO\Addressbook\Model\Addresslist::model()->findByPk($id);
					$addresslistModel->addManyMany ('contacts', $model->id);
				}
			}
			
		}
		
	}
	
}
