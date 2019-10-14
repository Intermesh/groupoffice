<?php

/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @author WilmarVB <wilmar@intermesh.nl>
 */


namespace GO\Addressbook\Controller;


class AddresslistController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Addressbook\Model\Addresslist';
	
	protected function beforeStore(&$response, &$params, &$store) {
		
		$store->setDefaultSortOrder('name','ASC');
		
		return parent::beforeStore($response, $params, $store);
	}
	
	protected function beforeStoreStatement(array &$response, array &$params, \GO\Base\Data\AbstractStore &$store, \GO\Base\Db\FindParams $storeParams) {
		
		if (empty($params['forContextMenu'])) {
			$multiSel = new \GO\Base\Component\MultiSelectGrid(
							'addresslist_filter', 
							"GO\Addressbook\Model\Addresslist",$store, $params, false);
			$multiSel->formatCheckedColumn();
		}		

		$storeParams->getCriteria()->addCondition('level', $params['permissionLevel'],'>=','core_acl_group');
		$storeParams->joinRelation('addresslistGroup','LEFT');

		
		if(isset(\GO::config()->addresslists_store_forced_limit)){
			$storeParams->limit(\GO::config()->addresslists_store_forced_limit);
		}
		
		// Sorting (First on Group, then on name or posted column
		$sortColumn = isset($params['sort'])?$params['sort']:'name';
		$sortDir = isset($params['dir'])?$params['dir']:'ASC';
		$storeParams->order(array('addresslistGroupName',$sortColumn),array('ASC',$sortDir));

		$storeParams->select('t.*,COALESCE(addresslistGroup.name,"'.\GO::t('Filter').'") AS addresslistGroupName');
	}

	public function formatStoreRecord($record, $model, $store) {
		if (!empty($_POST['forContextMenu'])) {
			$record['text'] = $record['name'];
			$record['addresslist_id']=$record['id'];
			unset($record['id']);
		}
		return $record;
	}
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {

		$columnModel->formatColumn('user_name', '$model->user->name');

		return parent::formatColumns($columnModel);
	}

	protected function afterLoad(&$response, &$model, &$params) {
		$response['data']['user_name'] = $model->user->name;
		return $response;
	}

	protected function actionContacts($params) {

		$store = \GO\Base\Data\Store::newInstance(\GO\Addressbook\Model\Contact::model());

		$sortAlias = \GO::user()->sort_name=="first_name" ? array('first_name','last_name') : array('last_name','first_name');
		$store->getColumnModel()->formatColumn('name','$model->getName(\GO::user()->sort_name)', array(),$sortAlias, \GO::t("Name"));
		//$store->getColumnModel()->formatColumn('name', '$model->name', array(), array('first_name', 'last_name'));
		$store->getColumnModel()->formatColumn('company_name', '$model->company->name', array(), 'company_id');
		$store->getColumnModel()->formatColumn('addressbook_name', '$model->addressbook->name', array(), 'addressbook_id');

		$store->processDeleteActions($params, "GO\Addressbook\Model\AddresslistContact", array('addresslist_id' => $params['addresslist_id']));

		$response = array();

		if (!empty($params['add_addressbook_id'])) {
			$addressbook = \GO\Addressbook\Model\Addressbook::model()->findByPk($params['add_addressbook_id']);
			$model = \GO\Addressbook\Model\Addresslist::model()->findByPk($params['addresslist_id']);
			$stmt = $addressbook->contacts();
			while ($contact = $stmt->fetch()) {
				$model->addManyMany('contacts', $contact->id);
			}
		} elseif (!empty($params['add_keys'])) {
			$add_keys = json_decode($params['add_keys'], true);
			$model = \GO\Addressbook\Model\Addresslist::model()->findByPk($params['addresslist_id']);
			foreach ($add_keys as $add_key)
				$model->addManyMany('contacts', $add_key);
		}elseif(!empty($params['add_search_result'])){
			$findParams = \GO::session()->values["contact"]['findParams'];
			$findParams->getCriteria()->recreateTemporaryTables();
			$findParams->limit(0)->select('t.id');
			
			$model = \GO\Addressbook\Model\Addresslist::model()->findByPk($params['addresslist_id']);
			$stmt = \GO\Addressbook\Model\Contact::model()->find($findParams);
			foreach ($stmt as $contact)
				$model->addManyMany('contacts', $contact->id);
			
		}

		$stmt = \GO\Addressbook\Model\Addresslist::model()->findByPk($params['addresslist_id'])->contacts($store->getDefaultParams($params));

		$store->setDefaultSortOrder('name', 'ASC');
		$store->setStatement($stmt);

		return array_merge($response, $store->getData());
	}

	protected function actionCompanies($params) {

		$store = \GO\Base\Data\Store::newInstance(\GO\Addressbook\Model\Company::model());

		$store->getColumnModel()->formatColumn('name', '$model->name', array(), array('first_name', 'last_name'));
		$store->getColumnModel()->formatColumn('addressbook_name', '$model->addressbook->name', array(), 'addressbook_id');
		
		$store->processDeleteActions($params, "GO\Addressbook\Model\AddresslistCompany", array('addresslist_id' => $params['addresslist_id']));

		$response = array();

		if (!empty($params['add_addressbook_id'])) {
			$addressbook = \GO\Addressbook\Model\Addressbook::model()->findByPk($params['add_addressbook_id']);
			$model = \GO\Addressbook\Model\Addresslist::model()->findByPk($params['addresslist_id']);
			$stmt = $addressbook->companies();
			while ($company = $stmt->fetch()) {
				$model->addManyMany('companies', $company->id);
			}
		} elseif (!empty($params['add_keys'])) {
			$add_keys = json_decode($params['add_keys'], true);
			$model = !isset($model) ? \GO\Addressbook\Model\Addresslist::model()->findByPk($params['addresslist_id']) : $model;
			foreach ($add_keys as $add_key)
				$model->addManyMany('companies', $add_key);
		}elseif(!empty($params['add_search_result'])){
			$findParams = \GO::session()->values["company"]['findParams'];
			$findParams->getCriteria()->recreateTemporaryTables();
			$findParams->limit(0)->select('t.id');
			
			$model = \GO\Addressbook\Model\Addresslist::model()->findByPk($params['addresslist_id']);
			
			$stmt = \GO\Addressbook\Model\Company::model()->find($findParams);
			foreach ($stmt as $contact)
				$model->addManyMany('companies', $contact->id);
			
		}

		$stmt = \GO\Addressbook\Model\Addresslist::model()->findByPk($params['addresslist_id'])->companies($store->getDefaultParams($params));

		$store->setDefaultSortOrder('name', 'ASC');
		$store->setStatement($stmt);

		return array_merge($response, $store->getData());
	}
	
	protected function actionGetRecipientsAsString($params){
				
		if(empty($params['addresslists']))
			throw new \Exception();
			
		$recipients = new \GO\Base\Mail\EmailRecipients();
		
		$addresslistIds = json_decode($params['addresslists']);
				
		foreach($addresslistIds as $addresslistId){
		
			$addresslist = \GO\Addressbook\Model\Addresslist::model()->findByPk($addresslistId);
			
			if($addresslist){
				$contacts = $addresslist->contacts(\GO\Base\Db\FindParams::newInstance()->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('email', '','!=')));
				while($contact = $contacts->fetch())				
						$recipients->addRecipient($contact->email, $contact->name);

				$companies = $addresslist->companies(\GO\Base\Db\FindParams::newInstance()->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('email', '','!=')));
				while($company = $companies->fetch())
						$recipients->addRecipient($company->email, $company->name);
			}
		}	
		
		return array(
				'success'=>true,
				'recipients'=>(string) $recipients
		);
	}
	
	/**
	 * Add contacts to an addresslist.
	 * @param type $params MUST contain addresslistId AND (EITHER senderNames and
	 * senderEmails OR contactIds)
	 * @return $response If there are email addresses that are not found in any
	 * addressbook, the corresponding senders are registered in 
	 * $response['unknownSenders'], and  $response['success'] becomes false, so
	 * that the user can decide what to do with the unknown senders.
	 */
	public function actionAddContactsToAddresslist($params) {
		$addresslistModel = \GO\Addressbook\Model\Addresslist::model()->findByPk($params['addresslistId']);
		$response = array(
			'success'=>true,
		);
		
		$nAddedContacts = 0;
		
		if (!empty($params['contactIds'])) {
			// Only contact ids are sent from the client
			$contactIds = json_decode($params['contactIds']);
			foreach ($contactIds as $contactId) {
				$addresslistModel->addManyMany('contacts',$contactId);
			}
		} else {
			// email addresses and names are sent from the client
			$senderEmails = json_decode($params['senderEmails']);
			$senderNames = json_decode($params['senderNames']);
			$senders = array(); // format: $senders[$senderEmail] = array('first_name'=>'Jack','middle_name'=>'','last_name'=>'Johnson');
			$unknownSenders = array(); // format: $unknownSenders[$senderEmail] = array('first_name'=>'Jack','middle_name'=>'','last_name'=>'Johnson');

			// Create array of senders
			foreach ($senderEmails as $key => $senderEmail) {
				if (empty($senders[$senderEmail]))
					$senders[$senderEmail] = $senderNames[$key];
			}

			foreach($senders as $senderEmail => $senderNameArr){
				$contactNameArr = \GO\Base\Util\StringHelper::split_name($senderNameArr);
				$contactStmt = \GO\Addressbook\Model\Contact::model()
					->find(
						\GO\Base\Db\FindParams::newInstance()
							->criteria(
								\GO\Base\Db\FindCriteria::newInstance()
									->addCondition('email', $senderEmail, '=', 't', false)
									->addCondition('email2', $senderEmail, '=', 't', false)
									->addCondition('email3', $senderEmail, '=', 't', false)
							)
					);//->findSingleByAttribute('email', $senderEmail);

				if (empty($contactStmt) && empty($unknownSenders[$senderEmail])) {
					// Keep track of contacts not found in database.
					$unknownSenders[] = array(
						'email'=>$senderEmail,
						'name'=>$senderNameArr,
						'first_name'=>$contactNameArr['first_name'],
						'middle_name'=>$contactNameArr['middle_name'],
						'last_name'=>$contactNameArr['last_name']
					);
				} else {
					// add contact to addresslist, but ensure only one email per addresslist
					$emailAlreadyInAddresslist = false;
					$linkableContactModel = false;
					while ($contactModel = $contactStmt->fetch()) {
						if ($addresslistModel->hasManyMany('contacts',$contactModel->id))
							$emailAlreadyInAddresslist = true;
						else
							$linkableContactModel = $contactModel;
					}
					if (!empty($linkableContactModel) && !$emailAlreadyInAddresslist) {
						$linkableContactModel->first_name = $contactNameArr['first_name'];
						$linkableContactModel->middle_name = $contactNameArr['middle_name'];
						$linkableContactModel->last_name = $contactNameArr['last_name'];
						$linkableContactModel->save();
						$addresslistModel->addManyMany('contacts', $linkableContactModel->id);
						$nAddedContacts++;
					}
				}
			}
			
			$response['addedSenders'] = $nAddedContacts;
			
			if (count($unknownSenders)) {
				$response['success'] = false;
				$response['unknownSenders'] = json_encode($unknownSenders);
				$response['addresslistId'] = $addresslistModel->id;
			}
		}
		
		return $response;
	}

	public function actionDeleteContactsFromAddresslist($params) {
		$addresslistModel = \GO\Addressbook\Model\Addresslist::model()->findByPk($params['addresslistId']);
		$response = array(
			'success'=>true,
			'nRemoved'=>0
		);
		
		// email addresses and names are sent from the client
		$senderEmails = json_decode($params['senderEmails']);

		foreach($senderEmails as $senderEmail){
			$contactStmt = \GO\Addressbook\Model\Contact::model()
				->find(
						\GO\Base\Db\FindParams::newInstance()
							->criteria(
								\GO\Base\Db\FindCriteria::newInstance()
									->addCondition('email', $senderEmail, '=', 't', false)
									->addCondition('email2', $senderEmail, '=', 't', false)
									->addCondition('email3', $senderEmail, '=', 't', false)
							)
					);//->findSingleByAttribute('email',$senderEmail);
			while ($contactModel = $contactStmt->fetch()) {
				if ($addresslistModel->hasManyMany('contacts', $contactModel->id)) {
					$removed = $addresslistModel->removeManyMany('contacts', $contactModel->id);
					if ($removed)
						$response['nRemoved']++;
				}
			}
		}
		
		return $response;
	}
	
	public function actionAdd($params) {
		$response = array('success'=>true);
		
		$listId = $params['addresslistId'];
		$contactIds = json_decode($params['contacts'],true);
		$companyIds = json_decode($params['companies'],true);
		$removeOther = isset($params['move']) && $params['move']=='true';
		
		$targetAddresslistModel = \GO\Addressbook\Model\Addresslist::model()->findByPk($listId);
		if (!$targetAddresslistModel->checkPermissionLevel(\GO\Base\Model\Acl::CREATE_PERMISSION))
			throw new \GO\Base\Exception\AccessDenied();
				
		if (!empty($removeOther)) {
			foreach ($contactIds as $contactId) {
				$contactModel = \GO\Addressbook\Model\Contact::model()->findByPk($contactId);
				$contactModel->removeAllManyMany('addresslists');
					}
			foreach ($companyIds as $companyId) {
				$companyModel = \GO\Addressbook\Model\Company::model()->findByPk($companyId);
				$companyModel->removeAllManyMany('addresslists');
					}
				}

		$addresslistModel = \GO\Addressbook\Model\Addresslist::model()->findByPk($listId);
		foreach ($contactIds as $contactId)
			$addresslistModel->addManyMany ('contacts', $contactId);
		foreach ($companyIds as $companyId)
			$addresslistModel->addManyMany ('companies', $companyId);
		
		return $response;
	}
	
	// TODO: get cross-session "selected addresslist" identifiers for getting store
}

