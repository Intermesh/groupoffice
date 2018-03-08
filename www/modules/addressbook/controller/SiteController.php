<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */


namespace GO\Addressbook\Controller;


class SiteController extends \GO\Site\Components\Controller{
	
	/**
	 * Sets the access permissions for guests
	 * Defaults to '*' which means that all functions can be accessed by guests.
	 * 
	 * @return array List of all functions that can be accessed by guests 
	 */
	protected function allowGuests() {
		return array('*');
	}
	
	protected function ignoreAclPermissions(){
		return array('*');
	}
	
	
	protected function actionContact(){	
		//GOS::site()->config->contact_addressbook_id;	
		
		if (\GO\Base\Util\Http::isPostRequest()) {
			if(isset($_POST['Addressbook']['name'])){
				$addressbookModel = \GO\Addressbook\Model\Addressbook::model()->findSingleByAttribute('name', $_POST['Addressbook']['name']);
			}else
			{
				$addressbookModel = \GO\Addressbook\Model\Addressbook::model()->findByPk($_POST['Addressbook']['id']);
			}
			if (!$addressbookModel)
				throw new \Exception(sprintf(\GO::t('addressbookNotFound','defaultsite'),$_POST['Addressbook']['name']));
			
			$contactModel = \GO\Addressbook\Model\Contact::model()->findSingleByAttributes(array('email'=>$_POST['Contact']['email'],'addressbook_id'=>$addressbookModel->id));
			if (!$contactModel) {
				$contactModel = new \GO\Addressbook\Model\Contact();
				$contactModel->addressbook_id = $addressbookModel->id;
			}
			$contactModel->setValidationRule('first_name', 'required', true);
			$contactModel->setValidationRule('last_name', 'required', true);
			$contactModel->setValidationRule('email', 'required', true);
			
			$companyModel = \GO\Addressbook\Model\Company::model()->findSingleByAttributes(array('name'=>$_POST['Company']['name'],'addressbook_id'=>$addressbookModel->id));
			if (!$companyModel) {
				$companyModel = new \GO\Addressbook\Model\Company();
				$companyModel->addressbook_id = $addressbookModel->id;
			}
			$companyModel->setValidationRule('name','required',true);
			
			$companyModel->setAttributes($_POST['Company']);
			if ($companyModel->validate()){
				$companyModel->save();
				$contactModel->company_id=$companyModel->id;
			}
			
			$contactModel->setAttributes($_POST['Contact']);
			
			
			

			if($contactModel->validate()){
				$saveSuccess = $contactModel->save();

				if ($saveSuccess) {
					// Add to mailings.
					$addresslists = !empty($_POST['Addresslist']) ? $_POST['Addresslist'] : array();
					foreach ($addresslists as $addresslistName=>$checked) {
						if (!empty($checked)) {
							$addresslistModel = \GO\Addressbook\Model\Addresslist::model()->findSingleByAttribute('name',$addresslistName);
							if ($addresslistModel) {
								$addresslistContactModel = \GO\Addressbook\Model\AddresslistContact::model()->findSingleByAttributes(array('contact_id'=>$contactModel->id,'addresslist_id'=>$addresslistModel->id));
								if (!$addresslistContactModel) {
									$addresslistContactModel = new \GO\Addressbook\Model\AddresslistContact();
									$addresslistContactModel->contact_id = $contactModel->id;
									$addresslistContactModel->addresslist_id = $addresslistModel->id;
									$addresslistContactModel->save();
								}
							}
						}
					}
					echo $this->render('contactform_done');
				} else {
					echo $this->render('contactform', array('contact'=>$contactModel,'company'=>$companyModel,'addressbook'=>$addressbookModel));
				}
			
				
			}else
			{
				$validationErrors = $contactModel->getValidationErrors();
				foreach ($validationErrors as $valError)
					echo $valError;
				echo $this->render('contactform', array('contact'=>$contactModel,'company'=>$companyModel,'addressbook'=>$addressbookModel));
			}
						
		}	else {
			$addressbookModel = new \GO\Addressbook\Model\Addressbook();
			$contactModel = new \GO\Addressbook\Model\Contact();
			$companyModel = new \GO\Addressbook\Model\Company();
			echo $this->render('contactform', array('contact'=>$contactModel,'company'=>$companyModel,'addressbook'=>$addressbookModel));
		}
	}

}
