<?php
/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AddresslistgroupController.php 19685 2014-09-17 09:14:54Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

namespace GO\Addressbook\Controller;

use GO;
use GO\Addressbook\Model\AddresslistGroup;
use GO\Base\Controller\AbstractJsonController;
use GO\Base\Data\ColumnModel;
use GO\Base\Data\DbStore;
use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Acl;

class AddresslistgroupController extends AbstractJsonController{

	protected function actionSubmit($params) {
		
		// Only create new Subscriptionservice when you have at minimal WRITE permissions
		if(GO::modules()->addressbook->permissionLevel < Acl::WRITE_PERMISSION){
			throw new AccessDenied(GO::t('noPermission','addressbook'));
		}
		
		$model = AddresslistGroup::model()->createOrFindByParams($params);
		$model->setAttributes($params);
		
		$model->save();
		
		echo $this->renderSubmit($model);
	}

	/**
	 * Action for fetchin a JSON array to be loaded into a ExtJS form
	 * @param array $params the $_REQUEST data
	 */
	protected function actionLoad($params) {

		//Load or create model
		$model = AddresslistGroup::model()->createOrFindByParams($params);

		$remoteComboFields = array();
		$extraFields = array();
		
		$response = $this->renderForm($model,$remoteComboFields,$extraFields);
		
		echo $response;
	}
	
	/**
	 * Render JSON output that can be used by ExtJS GridPanel
	 * @param array $params the $_REQUEST params
	 */
	protected function actionStore($params) {
		//Create ColumnModel from model
		$columnModel = new ColumnModel(AddresslistGroup::model());

		//Create store
		$store = new DbStore('GO\Addressbook\Model\AddresslistGroup', $columnModel, $params);
		echo $this->renderStore($store);
	}
	
}
