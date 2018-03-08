<?php
namespace GO\Customcss\Controller;

use GO;
use GO\Base\Controller\AbstractJsonController;
use GO\Base\Fs\Folder;
use GO\Base\Fs\File;

use GO\Base\Util\Http;


class CustomcssController extends AbstractJsonController {
	
	public function actionData($params){
		
		$response = array('success'=>true,'data'=>array());

		try {
			$customCssFolder = new Folder(GO::config()->file_storage_path.'customcss');
			if(!$customCssFolder->exists())
				$customCssFolder->create(0755);

			$cssFile = new File(GO::config()->file_storage_path.'customcss/style.css');
			$jsFile = new File(GO::config()->file_storage_path.'customcss/javascript.js');

			if(Http::isPostRequest()){

				if(isset($_POST['css']))
					$cssFile->putContents ($_POST['css']);

				if(isset($_POST['javascript']))
					$jsFile->putContents ($_POST['javascript']);
			}

			if($cssFile->exists()){
				$response['data']['css'] = $cssFile->getContents();
			} else {
				$response['data']['css'] = '/*
* Put custom styles here that will be applied to Group-Office. You can use the select file button to upload your logo and insert the URL in to this stylesheet.
*/

/* this will override the logo at the top right */
#go-logo, #headerLeft {
background-image:url(/insert/url/here) !important;
/*
background-size: auto 36px;
background-position: 0 -5px;
*/
}

/* this will override the logo at the login screen */
.go-app-logo {
background-image:url(/insert/url/here) !important;
}';
			}
			
			if($jsFile->exists()){
				$response['data']['javascript'] = $jsFile->getContents();
			}
			
		} 
		catch(Exception $e){
			$response['feedback'] = $e->getMessage();
			$response['success']=false;
		}

		echo $this->renderJson($response);
	}
}

