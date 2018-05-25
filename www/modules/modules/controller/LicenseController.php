<?php

namespace GO\Modules\Controller;

use Exception;

use GO;
use GO\Base\Model\Module;
use GO\Base\Controller\AbstractJsonController;

use GO\Base\Data\DbStore;
use GO\Base\Data\ColumnModel;
use GO\Base\Db\FindParams;
//use GO\Base\Data\JsonResponse;



class LicenseController extends AbstractJsonController{
	/**
	 * Render JSON output that can be used by ExtJS GridPanel
	 * @param array $params the $_REQUEST params
	 */
	protected function actionUsers($module) {
		//Create ColumnModel from model
		$columnModel = new ColumnModel(Module::model());
		
		$columnModel->formatColumn('checked', '\GO::scriptCanBeDecoded() && \GO\Professional\License::userHasModule($model->username, $module, true)', array('module'=>$module));
		
		$findParams = FindParams::newInstance()			
						->select('t.first_name,t.middle_name,t.last_name,t.username')
						->ignoreAcl()
						->limit(0);
						
		//Create store
		$store = new DbStore('GO\Base\Model\User', $columnModel, $_POST, $findParams);
		$store->defaultSort='username';
		$response = $this->renderStore($store);		
		
		$props = \GO::scriptCanBeDecoded() ? \GO\Professional\License::properties() : array();
		
		$response['license_id']=isset($props['licenseid']) ? $props['licenseid'] : 0;
		$response['hostname']=$_SERVER['HTTP_HOST'];
		
		
		echo $response;
	}
	
	
	protected function actionUpload(){

		if(!is_uploaded_file($_FILES['license_file']['tmp_name'][0])){
			throw new Exception("No file received");
		}
		
		if(!extension_loaded('ionCube loader')){
			throw new Exception("The required free ionCube loader is not installed. ");
		}
		
		$licenseFile = \GO::getLicenseFile();
				
		if($_FILES['license_file']['name'][0]!=$licenseFile->name()){
			throw new Exception("File should be named ".$licenseFile->name());
		}
		
		
		if(!$licenseFile->exists() || !$licenseFile->isWritable()){
			throw new Exception("Could not write file ".$licenseFile->name().". Please upload the file to the webserver and change the permissions so that the webserver can write to it.");
		}
		
//		$destinationFolder = new GO\Base\Fs\Folder(GO::config()->file_storage_path.'license/');
//		$destinationFolder->create();
//		
						
		$success = move_uploaded_file($_FILES['license_file']['tmp_name'][0],$licenseFile->path());
		
		//use cron to move the license as root.
//		GO\Modules\Cron\LicenseInstaller::runOnce();

		
		echo json_encode(array('success'=>$success));
			
	}
	
	protected function actionInstall(){
		
		//license check done in a separate request because it can't be done in one run
		
		if(!\GO::scriptCanBeDecoded()){
			throw new Exception("The license file you provided didn't work. Please contact Intermesh about this error.");
		}  else {
			//add all users to the modules they have access too

			\GO\Professional\License::autoConfigureModulePermissions();
			
			echo json_encode(array('success'=>true));
		}
	}
}
