<?php

/**
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl>
 * @author Wilmar van Beusekom <wilmar@intermesh.nl>
 *
 */

namespace GO\Addressbook\Controller;
use GO;

class CompanyController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Addressbook\Model\Company';

	protected function afterDisplay(&$response, &$model, &$params) {

        \GO::debug(get_class($this));

		$response['data']['photo_url']=$model->photoThumbURL;
		$response['data']['original_photo_url']=$model->photoURL;
		
		$response['data']['addressbook_name'] = $model->addressbook->name;

		$response['data']['google_maps_link'] = \GO\Base\Util\Common::googleMapsLink(
										$model->address, $model->address_no, $model->city, $model->country);

		$response['data']['formatted_address'] = nl2br($model->getFormattedAddress());

		$response['data']['post_google_maps_link'] = \GO\Base\Util\Common::googleMapsLink(
										$model->post_address, $model->post_address_no, $model->post_city, $model->post_country);

		$response['data']['post_formatted_address'] = nl2br($model->getFormattedPostAddress());

		$response['data']['employees'] = array();
		$sortAlias = \GO::user()->sort_name=="first_name" ? array('first_name','last_name') : array('last_name','first_name');
		$stmt = $model->contacts(\GO\Base\Db\FindParams::newInstance()->order($sortAlias));
		while ($contact = $stmt->fetch()) {
			$response['data']['employees'][] = array(
					'id' => $contact->id,
					'name' => $contact->getName(\GO::user()->sort_name),
					'function' => $contact->function,
					'email' => $contact->email
			);
		}
		
		
		if(\GO::modules()->customfields && isset($response['data']['customfields']) && \GO\Customfields\Model\DisableCategories::isEnabled("GO\Addressbook\Model\Company", $model->addressbook_id)){
			$ids = \GO\Customfields\Model\EnabledCategory::model()->getEnabledIds("GO\Addressbook\Model\Company", $model->addressbook_id);
			
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

		return parent::afterDisplay($response, $model, $params);
	}
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		
		$columnModel->formatColumn('ab_name','$model->addressbook_name', array(),'addressbook_name', \GO::t('addressbook','addressbook'));
$columnModel->formatColumn('username', '$model->user->displayName', array(), 'user_id');
		$columnModel->formatColumn('musername', '$model->mUser->displayName', array(), 'muser_id');
		return parent::formatColumns($columnModel);
	}

	public function formatStoreRecord($record, $model, $store) {

		$record['name_and_name2'] = $model->name;

		if (!empty($model->name2))
			$record['name_and_name2'] .= ' - ' . $model->name2;

		//$record['ab_name'] = $model->addressbook->name;
		
		$record['cf'] = $model->id.":".$model->name;//special field used by custom fields. They need an id an value in one.)

		return parent::formatStoreRecord($record, $model, $store);
	}

	protected function remoteComboFields() {
		return array(
				'addressbook_id' => '$model->addressbook->name'
		);
	}

	protected function afterLoad(&$response, &$model, &$params) {

		if (\GO::modules()->customfields)
			$response['customfields'] = \GO\Customfields\Controller\CategoryController::getEnabledCategoryData("GO\Addressbook\Model\Company", $model->addressbook_id);

		$response['data']['photo_url']=$model->photoThumbURL;		
		$response['data']['original_photo_url']=$model->photoURL;
		
		$stmt = $model->addresslists();
		while ($addresslist = $stmt->fetch()) {
			$response['data']['addresslist_' . $addresslist->id] = 1;
		}
		
		$response['data']['name_and_name2'] = $model->name;
		if (!empty($model->name2))
			$response['data']['name_and_name2'] .= ' - ' . $model->name2;


		return parent::afterLoad($response, $model, $params);
	}

	protected function beforeSubmit(&$response, &$model, &$params) {
		
		//workaroud extjs iframe hack for file upload
		$_SERVER["HTTP_X_REQUESTED_WITH"] = "XMLHttpRequest";
		
		$this->checkMaxPostSizeExceeded();
		
		return parent::beforeSubmit($response, $model, $params);
	}	
	
	protected function afterSubmit(&$response, &$model, &$params, $modifiedAttributes) {
		$stmt = \GO\Addressbook\Model\Addresslist::model()->find();
		while ($addresslist = $stmt->fetch()) {
			$linkModel = $addresslist->hasManyMany('companies', $model->id);
			$mustHaveLinkModel = isset($params['addresslist_' . $addresslist->id]);
			if ($linkModel && !$mustHaveLinkModel) {
				$linkModel->delete();
			}
			if (!$linkModel && $mustHaveLinkModel) {
				$addresslist->addManyMany('companies', $model->id);
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
	
	
	protected function actionPhoto($params){
		//fetching contact will check read permission
		$company = \GO\Addressbook\Model\Company::model()->findByPk($params['id']);
		
		\GO\Base\Util\Http::outputDownloadHeaders($company->getPhotoFile(), true, false);
		$company->getPhotoFile()->output();
	}
	
	
	protected function beforeStoreStatement(array &$response, array &$params, \GO\Base\Data\AbstractStore &$store, \GO\Base\Db\FindParams $storeParams) {
		
		if(!empty($params['filters'])){
			$abMultiSel = new \GO\Base\Component\MultiSelectGrid(
							'books', 
							"GO\Addressbook\Model\Addressbook",$store, $params, true);
			
			$abMultiSel->addSelectedToFindCriteria($storeParams, 'addressbook_id');
			
			//$abMultiSel->setButtonParams($response);
			//$abMultiSel->setStoreTitle();

			$addresslistMultiSel = new \GO\Base\Component\MultiSelectGrid(
							'addresslist_filter', 
							"GO\Addressbook\Model\Addresslist",$store, $params, false);

			if(!empty($params['addresslist_filters']))
			{
				$addresslistMultiSel->addSelectedToFindCriteria($storeParams, 'addresslist_id','ac');

				if(count($addresslistMultiSel->selectedIds)){
					//we need to join the addresslist link model if a filter for the addresslist is enabled.
					$storeParams->join(
									\GO\Addressbook\Model\AddresslistCompany::model()->tableName(), 
									\GO\Base\Db\FindCriteria::newInstance()->addCondition('id', 'ac.company_id', '=', 't', true, true), 
									'ac'
						);
					
					$storeParams->group('t.id');
				}
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
						->addModel(\GO\Addressbook\Model\Company::model(), 't');
					
		// Filter by clicked letter
		if (!empty($params['clicked_letter'])) {
			if ($params['clicked_letter'] == '[0-9]') {
				$query = '^[0-9].*$';
				$query_type = 'REGEXP';
			} else {
				$query = $params['clicked_letter'] . '%';
				$query_type = 'LIKE';
			}
			$criteria->addCondition('name', $query, $query_type);
		}

		$storeParams = \GO\Base\Db\FindParams::newInstance()
						->export("company")
						->criteria($criteria)
						->joinAclFieldTable()
						->select('t.*, addressbook.name AS addressbook_name');
//						->joinModel(array(
//				'model' => 'GO\Addressbook\Model\Addressbook',
//				'localField' => 'addressbook_id',
//				'tableAlias' => 'ab', //Optional table alias
//						));
										

		if (!empty($params['addressbook_id'])) {
			$storeParams->getCriteria()->addCondition('addressbook_id', $params['addressbook_id']);
		}
		
		if(!empty($params['require_email']))
			$storeParams->getCriteria()->addCondition('email', "","!=");
		
		return $storeParams;
	}

	protected function actionChangeAddressbook($params) {
		$ids = json_decode($params['items']);

		$response['success'] = true;
		$response['failedToMove'] = array();

		foreach ($ids as $id) {
			$model = \GO\Addressbook\Model\Company::model()->findByPk($id);
			try {
				$model->addressbook_id=$params['book_id'];
				$model->save();
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

	protected function actionMoveEmployees($params) {
		$to_company = \GO\Addressbook\Model\Company::model()->findByPk($params['to_company_id']);

		$contacts = \GO\Addressbook\Model\Contact::model()->find(
						\GO\Base\Db\FindCriteria::newInstance()
										->addCondition('company_id', $params['from_company_id'])
		);

		foreach ($contacts as $contact) {
			$attributes = array(
					'addressbook_id' => $to_company->addressbook_id,
					'company_id' => $to_company->id
			);
			$contact->setAttributes($attributes);
			$contact->save();
		}

		$response['success'] = true;
		return $response;
	}

	protected function beforeHandleAdvancedQuery($advQueryRecord, \GO\Base\Db\FindCriteria &$criteriaGroup, \GO\Base\Db\FindParams &$storeParams) {

		if($advQueryRecord['comparator'] == 'RADIUS') {
			list($lat, $long) = json_decode($advQueryRecord['field'], true);
			$defaultSelect = 't.*, addressbook.name AS ab_name';
			$storeParams->debugSql()->select($defaultSelect.',( 6371 * acos( cos( radians('.$lat.') ) * cos( radians( t.latitude ) ) * cos( radians( t.longitude ) - radians('.$long.') ) + sin( radians('.$lat.') ) * sin( radians( t.latitude ) ) ) ) AS distance');
			$storeParams->having('distance < '.floatval($advQueryRecord['value']));
			return false;
		}
		switch ($advQueryRecord['field']) {
			case 'employees.name':
				$storeParams->join(
								\GO\Addressbook\Model\Contact::model()->tableName(), \GO\Base\Db\FindCriteria::newInstance()->addRawCondition('`t`.`id`', '`employees' . $advQueryRecord['id'] . '`.`company_id`'), 'employees' . $advQueryRecord['id']
				);
				$criteriaGroup->addRawCondition(
								'CONCAT_WS(\' \',`employees' . $advQueryRecord['id'] . '`.`first_name`,`employees' . $advQueryRecord['id'] . '`.`middle_name`,`employees' . $advQueryRecord['id'] . '`.`last_name`)', ':employee' . $advQueryRecord['id'], $advQueryRecord['comparator'], $advQueryRecord['andor'] == 'AND'
				);
				$criteriaGroup->addBindParameter(':employee' . $advQueryRecord['id'], $advQueryRecord['value']);
				return false;
				break;
			default:
				return true;
				break;
		}
	}

// This breaks the company combobox selections (replaces t.name for employees.name (both have the name as ext))
// 
//	protected function afterAttributes(&$attributes, &$response, &$params, \GO\Base\Db\ActiveRecord $model) {
//		//unset($attributes['t.company_id']);
//		$attributes['employees.name'] = array('name'=>'employees.name','label'=>\GO::t("Employee", "addressbook"));
//		return parent::afterAttributes($attributes, $response, $params, $model);
//	}
	
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
	
	/**
	 * Remove the invalid emails from records to be imported
	 */
	protected function beforeImport($params, &$model, &$attributes, $record) {	
	  if(isset($attributes['email']) && !\GO\Base\Util\StringHelper::validate_email($attributes['email']))
          unset($attributes['email']);
        
	  return parent::beforeImport($params, $model, $attributes, $record);
	}
	
	protected function actionSelectCompany($params){
		
				$response = array('total'=>0, 'results'=>array());
			$query = !empty($params['query']) ? $params['query'] : '';
			$params['start'] = !empty($params['start']) ? $params['start'] : 0;
			$params['limit'] = !empty($params['limit']) ? $params['limit'] : 10;
			
				
		$query = '%'.preg_replace ('/[\s*]+/','%', $query).'%'; 
				
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
			->ignoreAcl()
			->select('t.*, a.name AS ab_name')->calcFoundRows()
			->searchQuery($query,
							array(
									"CONCAT(t.name,' ',t.name2,' ',' ',a.name)",
									't.email'
									))					
			->joinModel(array(
				'model'=>'GO\Addressbook\Model\Addressbook',
				'foreignField'=>'id', //defaults to primary key of the remote model
				'localField'=>'addressbook_id', //defaults to "id"
				'tableAlias'=>'a', //Optional table alias
				'type'=>'INNER' //defaults to INNER,

			))			
			->start($params['start'])
			->limit($params['limit'])
			->order('t.name');

		if(!empty($params['addressbook_ids']) && $params['addressbook_ids'] != '[]'){

			if(!empty($params['addressbook_id'])){
				\GO::debug('Given addressbook_ids array and addressbook_id parameters. Truncate addressbook_id parameter');
				$params['addressbook_id'] = false;
			}

			$abs = json_decode($params['addressbook_ids']);

		}	else if(!empty($params['addressbook_id'])){		
			$abs= array($params['addressbook_id']);
		}else if (GO::modules()->customfields && !empty($params['customfield_id'])) {
			
			$colId = preg_replace('/[\D]/','',$params['customfield_id']);
			$customfieldModel = \GO\Customfields\Model\Field::model()->findByPk($colId);
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
		} 
		
		if(empty($abs)){
			$abs = \GO\Addressbook\Model\Addressbook::model()->getAllReadableAddressbookIds();			
		}

		if(!empty($abs)){

			$findParams->getCriteria ()->addInTemporaryTableCondition('addressbooks','addressbook_id', $abs);

			$stmt = \GO\Addressbook\Model\Company::model()->find($findParams);

//				$user_ids=array();
			foreach($stmt as $company){
				$record =$company->getAttributes();
				
				$record['addressbook_name'] = $company->addressbook?$company->addressbook->name:'';
				$record['name_and_name2'] = !empty($company->name2) ? $company->name.' '.$company->name2 : $company->name;
				//$record['name']=$contact->name;
				$record['cf']=$company->id.":".$company->name;

				$response['results'][]=$record;	

//					if($contact->go_user_id)
//						$user_ids[]=$contact->go_user_id;
			}
			$response['total']= $stmt->foundRows;
			
		}
		
		
		return $response;
		
	}
	
	protected function afterAttributes(&$attributes, &$response, &$params, GO\Base\Db\ActiveRecord $model) {
		/**
		 * add the writebel addresslists to te maping store
		 */
		$findParams = new \GO\Base\Db\FindParams();
		$findParams->permissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION);
		
		$addresslists = \GO\Addressbook\Model\Addresslist::model()->find($findParams);
		foreach ($addresslists as $rec) {
			
			$attributes['addresslist_'. $rec->id] = array('name'=>'addresslist.addresslist_' . $rec->id, 'label'=>'' .GO::t("Address Lists", "addressbook"). ': ' .$rec->name, 'gotype'=>'boolean');
			
		}
		
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
					$addresslistModel->addManyMany ('companies', $model->id);
				}
			}
			
		}
		
	}
	
}
