<?php

namespace GO\Addressbook\Controller;


class AddressbookController extends \GO\Base\Controller\AbstractModelController{
	
	protected function checkSecurityToken() {
		return false;
		parent::checkSecurityToken();
	}
	
	protected function actionCleanupContacts($params) {
		
		header('Content-Type: text/html;charset=utf-8');
		$this->render('externalHeader');
		
		echo "<script language='javascript'>"
			."function toggle(source) {"
			."	checkboxes = document.getElementsByClassName('checkbox');"
			."	for(var i=0, n=checkboxes.length;i<n;i++) {"
			."		checkboxes[i].checked = source.checked;"
			."  }"
			."}"
			."</script>";
		
		echo '<form method="POST" action="'.\GO::url('addressbook/addressbook/cleanupContacts').'">';
		
		echo '<input type="hidden" name="addressbook_id" value="'.$params['addressbook_id'].'" />';
		
		echo '<div class="row"><input type="checkbox" name="nolinks" id="nolinks" '.(!empty($_POST['nolinks']) ? "checked" : "").' /><label for="nolinks">No links</label></div>';
		echo '<div class="row"><input type="checkbox" name="noinvoice" id="noinvoice" '.(!empty($_POST['noinvoice']) ? "checked" : "").' /><label for="noinvoice">No invoice or company with invoice.</label></div>';
		echo '<div class="row"><input type="checkbox" name="noproject" id="noproject" '.(!empty($_POST['noproject']) ? "checked" : "").' /><label for="noproject">No project or company with project.</label></div>';
		echo '<div class="row"><input type="checkbox" name="nouser" id="nouser" '.(!empty($_POST['nouser']) ? "checked" : "").' /><label for="nouser">No project or company with project.</label></div>';
		
		echo '<input type="submit" value="OK" />';
		
		$entityTypeId = \GO\Addressbook\Model\Contact::model()->getType()->getId();
		
		if($_SERVER['REQUEST_METHOD'] == "POST") {
			
			
			$fp = (new \GO\Base\Db\FindParams())->order('mtime', 'DESC');
			$c = $fp->getCriteria();//->addCondition('addressbook_id', $_POST['addressbook_id']);
			
			if(!empty($_POST['nolinks'])) {
				$c->addRawCondition('not exists(select toId from core_link where toEntityTypeId=:entityTypeId and toId=t.id)')
					->addRawCondition('not exists(select fromId from core_link where fromEntityTypeId=:entityTypeId and fromId=t.id)')			
								->addBindParameter(':entityTypeId', $entityTypeId);
			}
			
			if(!empty($_POST['noinvoice'])) {
				$c->addRawCondition('not exists (select * from bs_orders o where o.contact_id=t.id or o.company_id=t.company_id and o.company_id > 0)');
			}
			
			if(!empty($_POST['noproject'])) {
				$c->addRawCondition('not exists (select * from pr2_projects o where o.contact_id=t.id or o.company_id=t.company_id and o.company_id > 0)');
			}
			
			
			
			if(!empty($_POST['nouser'])) {
				$c->addRawCondition('go_user_id = 0');
			}
			
			$contacts  = \GO\Addressbook\Model\Contact::model()->find($fp);
			
			echo '<p>'. $contacts->rowCount().' contacts found</p>';
			
			echo '<table>';
			
			foreach($contacts as $contact) {
				echo '<tr>';
				echo '<td><input type="checkbox" name="selected[]" value="'.$contact->id.'" /></td>';
				echo '<td>'. $contact->getName() .'</td>';
				echo '<td>'. ($contact->company ? $contact->company->name : '-') .'</td>';
				echo '<td>'. \GO\Base\Util\Date::get_timestamp($contact->mtime) .'</td>';
				echo '</tr>';
			}
			
			
			echo '</table>';
			
		}
		
		
		
		echo '</form>';
		
		
		
		
		$this->render('externalFooter');
	}
	
	
	protected function beforeStoreStatement(array &$response, array &$params, \GO\Base\Data\AbstractStore &$store, \GO\Base\Db\FindParams $storeParams) {
		
		$multiSel = new \GO\Base\Component\MultiSelectGrid(
						'books', 
						"GO\Addressbook\Model\Addressbook",$store, $params, true);
		$multiSel->setFindParamsForDefaultSelection($storeParams);
		$multiSel->formatCheckedColumn();
		
		return parent::beforeStoreStatement($response, $params, $store, $storeParams);
	}
	
	protected $model = 'GO\Addressbook\Model\Addressbook';
	
	protected function remoteComboFields() {
		return array('user_id'=>'$model->user->name');
	}

	protected function beforeStore(&$response, &$params, &$store) {
		$store->setDefaultSortOrder('name','ASC');
		return parent::beforeStore($response, $params, $store);
	}
	
	protected function actionSearchSender($params) {

		$contacts = \GO\Addressbook\Model\Contact::model()->findByEmail($params['email']);
		$response['success']=true;
		$response['results']=array();

		foreach($contacts as $contact)
		{
			$res_contact['id']=$contact->id;
			$res_contact['name']=$contact->name.' ('.$contact->addressbook->name.')';

			$response['results'][]=$res_contact;
		}
		return $response;
	}
	
	public function formatStoreRecord($record, $model, $store) {
		
		$record['user_name']=$model->user ? $model->user->name : 'unknown';
		if(\GO::modules()->customfields){
			$record['contactCustomfields']=\GO\Customfields\Controller\CategoryController::getEnabledCategoryData("GO\Addressbook\Model\Contact", $model->id);
			$record['companyCustomfields']=\GO\Customfields\Controller\CategoryController::getEnabledCategoryData("GO\Addressbook\Model\Company", $model->id);
		}
		
		return parent::formatStoreRecord($record, $model, $store);
	}
	
	/**
	 * Function exporting addressbook contents to VCFs.
	 * 
	 * @param array $params 
	 */
	public function actionExportVCard($params) {
		
		$findParams = \GO\Base\Db\FindParams::loadExportFindParams('contact');
		
		$findParams->limit(0);
		
		$store = new \GO\Base\Data\DbStore('GO\Addressbook\Model\Contact',new \GO\Base\Data\ColumnModel('GO\Addressbook\Model\Contact'),$params,$findParams);
		
		$file = new \GO\Base\Fs\File(\GO::t("Contacts", "addressbook").'.vcf');
		\GO\Base\Util\Http::outputDownloadHeaders($file);
		
		while($record = $store->nextRecord()){
			$model = \GO\Addressbook\Model\Contact::model()->findByPk($record['id']);
			
			if(!isset($fileStream))
				$fileStream=fopen('php://output','w+');		

			fwrite($fileStream,$model->toVObject()->serialize());
		}
	}
	
	/**
	 * Function exporting addressbook contents to VCFs. Must be called from export.php.
	 * @param type $params 
	 */
//	public function exportVCard($params) {
//		$addressbook = \GO\Addressbook\Model\Addressbook::model()->findByPk($params['addressbook_id']);
//		
//		$filename = $addressbook->name.'.vcf';
//		\GO\Base\Util\Http::outputDownloadHeaders(new \GO\Base\FS\File($filename));		
//	
//		foreach ($addressbook->contacts(\GO\Base\Db\FindParams::newInstance()->select('t.*')) as $contact)
//			echo $contact->toVObject()->serialize();
//	}
	
	protected function afterSubmit(&$response, &$model, &$params, $modifiedAttributes) {
		
//		if(isset($_FILES['files']['tmp_name'][0]))
//			$response = array_merge($response,$this->run("upload",$params,false));
		
		return parent::afterSubmit($response, $model, $params, $modifiedAttributes);
	}
	
	protected function actionUpload($params) {
		//$params['a'] = $addressbook_id = $params['addressbook_id'];
		$import_filetype = isset($params['fileType']) ? ($params['fileType']) : null;
//				
//		
//		if (!empty($_FILES['import_file']['tmp_name']))
//			$import_filename = ($_FILES['import_file']['tmp_name']);
//		elseif (!empty($params['import_file']))
//			$import_filename = ($params['import_file']);
//		
////		$separator	= isset($params['separator']) ? ($params['separator']) : ',';
////		$quote	= isset($params['quote']) ? ($params['quote']) : '"';
//		$params['file'] = \GO::config()->tmpdir.uniqid(time());
//		$response['success'] = true;
//		\GO::debug($import_filename);
//
//		if(!move_uploaded_file($import_filename, $params['file'])) {
//			throw new \Exception('Could not move '.$import_filename);
//	  }

//		$file = new \GO\Base\Fs\File($_FILES['importFiles']['tmp_name']);
//	  $file->convertToUtf8();
		$params['file'] = $_FILES['files']['tmp_name'][0];
		ini_set('max_execution_time', 360);
		ini_set('memory_limit', '256M');
		$response = array();
		
	  switch($import_filetype) {
			case 'vcf':				
				$response = array_merge($response,$this->run("importVcf",$params,false));
				break;
			default:
				
				if($params['controller']=='GO\Addressbook\Controller\Contact')
					$controller = new Contact();
				elseif($params['controller']=='GO\Addressbook\Controller\Company')
					$controller = new Company();
				else
					throw new \Exception("No or wrong controller given");
				
				$response = array_merge($response,$controller->run("ImportCsv",$params,false));
				break;
	  }		
		
		$response['success'] = true;
		return $response;
	}
	
	
	public function actionTruncate($params){
		$addressbook = \GO\Addressbook\Model\Addressbook::model()->findByPk($params['addressbook_id']);
		
		if(!$addressbook)
			throw new \GO\Base\Exception\NotFound();
		
		$addressbook->truncate();
		
		$response['success']=true;
		
		return $response;
	}
	
	
	protected function actionCheck($params){
		$model = \GO::getModel($this->model)->findByPk($params["id"]);
		$model->checkDatabase();
		
		$stmt = $model->contacts;
		
		foreach($stmt as $contact)
			$contact->checkDatabase();
		
		$stmt = $model->companies;
		
		foreach($stmt as $company)
			$contact->checkDatabase();
		
		echo "Done\n";
	}
	
	/**
	 * Imports VCF file.
	 * Example command line call: /path/to/groupoffice/groupoffice addressbook/addressbook/importVcf --file=filename.txt --addressbook_id=1
	 * @param Array $params Parameters. MUST contain string $params['file'].
	 */
//	protected function actionImportVcf($params){
//		$file = new \GO\Base\Fs\File($params['file']);
//		$file->convertToUtf8();
//
//		$data = $file->getContents();
//		
//		$contact = new \GO\Addressbook\Model\Contact();
//		$vcard = \GO\Base\VObject\Reader::read($data);
//		
//		\GO\Base\VObject\Reader::convertVCard21ToVCard30($vcard);
//		
//		
//		
//		if(!empty($params["addressbook_id"]))
//			throw new \Exception("Param addressbook_id may not be empty");
//		//$params['addressbook_id'] = !empty($params['a']) ? $params['a'] : 1;
//		
//		if (is_array($vcard)) {
//			foreach ($vcard as $item) {
//				$contact->importVObject(
//					$item,
//					array(
//						'addressbook_id' => $params['addressbook_id']
//					)
//				);
//			}
//		} else {
//			$contact->importVObject(
//				$vcard,
//				array(
//					'addressbook_id' => $params['addressbook_id']
//				)
//			);
//		}
//		return array('success'=>true);
//	}
//	
	
	
	public function actionRemoveDuplicates($params){
		
		\GO::setMaxExecutionTime(300);
		\GO::setMemoryLimit(1024);
		
		$this->render('externalHeader');
		
		$addressbook = \GO\Addressbook\Model\Addressbook::model()->findByPk($params['addressbook_id']);
		
		if(!$addressbook)
			throw new \GO\Base\Exception\NotFound();
		
		\GO\Base\Fs\File::setAllowDeletes(false);
		//VERY IMPORTANT:
		\GO\Files\Model\Folder::$deleteInDatabaseOnly=true;
		
		
		\GO::session()->closeWriting(); //close writing otherwise concurrent requests are blocked.
		
		$checkModels = array(
				"GO\Addressbook\Model\Contact"=>array('first_name', 'middle_name', 'last_name', 'company_id', 'email','addressbook_id'),
			);		
		
		foreach($checkModels as $modelName=>$checkFields){
			
			if(empty($params['model']) || $modelName==$params['model']){

				echo '<h1>'.\GO::t("Remove duplicates").'</h1>';

				$checkFieldsStr = 't.'.implode(', t.',$checkFields);
				$findParams = \GO\Base\Db\FindParams::newInstance()
								->ignoreAcl()
								->select('t.id, count(*) AS n, '.$checkFieldsStr)
								->group($checkFields)
								->having('n>1');
				
				$findParams->getCriteria()->addCondition('addressbook_id', $addressbook->id);

				$stmt1 = \GO::getModel($modelName)->find($findParams);

				echo '<table border="1">';
				echo '<tr><td>ID</th><th>'.implode('</th><th>',$checkFields).'</th></tr>';

				$count = 0;

				while($dupModel = $stmt1->fetch()){
					
					$select = 't.id';
					
					if(\GO::getModel($modelName)->hasFiles()){
						$select .= ', t.files_folder_id';
					}

					$findParams = \GO\Base\Db\FindParams::newInstance()
								->ignoreAcl()
								->select($select.', '.$checkFieldsStr)
								->order('id','ASC');
					
					$findParams->getCriteria()->addCondition('addressbook_id', $addressbook->id);

					foreach($checkFields as $field){
						$findParams->getCriteria()->addCondition($field, $dupModel->getAttribute($field));
					}							

					$stmt = \GO::getModel($modelName)->find($findParams);

					$first = true;

					while($model = $stmt->fetch()){
						echo '<tr><td>';
						if(!$first)
							echo '<span style="color:red">';
						echo $model->id;
						if(!$first)
							echo '</span>';
						echo '</th>';				

						foreach($checkFields as $field)
						{
							echo '<td>'.$model->getAttribute($field,'html').'</td>';
						}

						echo '</tr>';

						if(!$first){							
							if(!empty($params['delete'])){

								if($model->hasLinks() && $model->countLinks()){
									echo '<tr><td colspan="99">'.\GO::t("Skipped delete because model has links").'</td></tr>';
								}elseif(($filesFolder = $model->getFilesFolder(false)) && ($filesFolder->hasFileChildren() || $filesFolder->hasFolderChildren())){
									echo '<tr><td colspan="99">'.\GO::t("Skipped delete because model has folder or files").'</td></tr>';
								}else{									
									$model->delete();
								}
							}

							$count++;
						}

						$first=false;
					}
				}	
					

				echo '</table>';

				echo '<p>'.sprintf(\GO::t("Found %s duplicates."),$count).'</p>';
				echo '<br /><br /><a href="'.\GO::url('addressbook/addressbook/removeDuplicates', array('delete'=>true, 'addressbook_id'=>$addressbook->id)).'">'.\GO::t("Click here to delete the newest duplicates marked in red.").'</a>';
				
			}
		}
		
		$this->render('externalFooter');
		
		
	}
	
	protected function actionFirstWritableAddressbookId($params) {
		$addressbookIds = json_decode($params['addressbook_ids']);
		$firstAddressbookId = !empty($addressbookIds) ? $addressbookIds[0] : -1;
		foreach ($addressbookIds as $addressbookId) {
			$addressbookModel = \GO\Addressbook\Model\Addressbook::model()->findByPk($addressbookId);
			if ($addressbookModel && $addressbookModel->checkPermissionLevel(\GO\Base\Model\Acl::CREATE_PERMISSION)) {
				$response = array('success'=>true,'data'=>array('addressbook_id'=>$addressbookModel->id));
				echo json_encode($response); exit();
			}
		}
		$response = array('success'=>true,'data'=>array('addressbook_id'=>$firstAddressbookId));
		echo json_encode($response);
	}
	
}
