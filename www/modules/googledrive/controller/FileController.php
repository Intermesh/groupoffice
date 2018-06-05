<?php

namespace GO\Googledrive\Controller;

use Exception;
use GO;
use Google_AuthException;
use Google_Client;
use Google_DriveFile;
use Google_DriveService;
use Google_HttpRequest;


class FileController extends \GO\Base\Controller\AbstractController {
	/**
	 *
	 * @var Google_Client 
	 */
	private $client;
	
	/**
	 *
	 * @var Google_DriveService 
	 */
	private $service;
		
	
	protected function beforeRun($action, $params, $render) {
			
		$this->client = new \GO\Googledrive\Client();		
		$this->client->setRedirectUri(\GO::url($this->getRoute("connect"),array(), false, false, false));
		
		$this->service = new Google_DriveService($this->client);
		
		$token =\GO::config()->get_setting('googledrive_token', \GO::user()->id);
		if ($token) {
			$this->client->setAccessToken($token);
		}
		
		return parent::beforeRun($action, $params, $render);
	}
	
	protected function checkSecurityToken() {
		if ($this->getAction() != 'connect'){
			return parent::checkSecurityToken();
		}else {
			return true;
		}
	}

	/**
	 * After oauth2 is finished it will redirect to this connect action.
	 * On success this will redirect to the orginal edit action
	 * 
	 * @param type $params
	 */
	protected function actionConnect($params){
		if (isset($_GET['code'])) {
			$this->client->authenticate();
			$this->saveToken();
		}
		$editUrl = GO::url($this->getRoute('edit'),array('id'=>GO::session()->values['googledrive']['edit_id_after_auth']));
		unset(GO::session()->values['googledrive']['edit_id_after_auth']);
		header("Location: ".$editUrl);
	}
	
	/**
	 * 
	 * @param type $params
	 * @return boolean
	 * @throws Exception
	 */
	protected function actionImport($params){

		$response = array('success'=>false);
		
		if ($this->client->getAccessToken()) {
			
			$file = $this->service->files->get(\GO::session()->values['googledrive']['editing']['gd_id']);

			$goFile = \GO\Files\Model\File::model()->findByPk(\GO::session()->values['googledrive']['editing']['go_file_id']);
			$mimeType = $goFile->fsFile->mimeType();
			
			//hack for strange gdocs mimetype
			if($mimeType=='application/vnd.oasis.opendocument.spreadsheet')
				$mimeType='application/x-vnd.oasis.opendocument.spreadsheet';
			
			$downloadUrl = isset($file->exportLinks[$mimeType]) ? $file->exportLinks[$mimeType] : false;

			if ($downloadUrl) {
				
				$tmpFile = \GO\Base\Fs\File::tempFile($goFile->name);
				
				$request = new Google_HttpRequest($downloadUrl, 'GET', null, null);
				$httpRequest = Google_Client::$io->authenticatedRequest($request);
				
				if ($httpRequest->getResponseHttpCode() == 200) {
					$tmpFile->putContents($httpRequest->getResponseBody());
					
					$goFile->replace($tmpFile);
					
					$this->service->files->delete(\GO::session()->values['googledrive']['editing']['gd_id']);
					
					$response['success']=true;
				}  else {
					throw new Exceptio("Got HTTP response code ".$httpRequest->getResponseHttpCode()." from Google");
				}
			}else
			{
				
				var_dump($file->exportLinks);
				throw new Exception("Document type ".$mimeType." is not supported");
				
				
			}
			$this->saveToken();
		}else
		{
			throw new Exception("No access to Google!");
		}
		
		if(!$response['success'])
			throw new Exception("Failed to import file!");
		
		return $response;
	}
	
	private function saveToken(){
		\GO::config()->save_setting('googledrive_token', $this->client->getAccessToken(), \GO::user()->id);
	}
	

	protected function actionEdit($params) {
		
		$goFile= \GO\Files\Model\File::model()->findByPk($params['id']);
		
		$fileHandler = new \GO\Googledrive\Filehandler\Googledrive();
		
		if(!$fileHandler->fileIsSupported($goFile)){						
			throw new Exception("This file is not supported");
		}	
		
		if(in_array($goFile->extension, array('doc','xls','ppt'))){
			
			if(!empty($params['rename'])){
				$goFile->name.='x';
				$goFile->save();
			}else{			
				$this->render('renameconfirm', array('continueUrl'=>\GO::url('googledrive/file/edit',array('id'=>$params['id'], 'rename'=>1))));
				exit();
			}
		}

		try{
			if ($this->client->getAccessToken()) {
				$file = new Google_DriveFile();
				$file->setTitle($goFile->name);
				$file->setDescription($goFile->path);
				$file->setMimeType($goFile->fsFile->mimeType());
				// Set the parent folder.

				$createdFile = $this->service->files->insert($file, array(
						'data' => $goFile->fsFile->getContents(),
						'mimeType' => $goFile->fsFile->mimeType(),
						'convert'=>true
				));

				\GO::session()->values['googledrive']['editing']=array('go_file_id'=>$goFile->id, 'gd_id'=>$createdFile->id);

				$this->saveToken();

				header("Location: ".$createdFile->alternateLink);			
			} else {
				$authUrl = $this->client->createAuthUrl();
				GO::session()->values['googledrive']['edit_id_after_auth']=$params['id'];
				header("Location: ".$authUrl);			
			}
		}catch(Google_AuthException $e){
			\GO::config()->delete_setting('googledrive_token', \GO::user()->id);
			
			$authUrl = $this->client->createAuthUrl();
			GO::session()->values['googledrive']['edit_id_after_auth']=$params['id'];
			header("Location: ".$authUrl);			
		}
	}
}
