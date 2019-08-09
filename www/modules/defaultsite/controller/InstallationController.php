<?php

namespace GO\Defaultsite\Controller;


class InstallationController extends \GO\Base\Controller\AbstractJsonController {
	
	protected function actionInstallModules($params){

		$response = array(
			'success'=>true,
			'feedback'=>'' // Needed when an error occurs
		);
		
		if(!\GO::modules()->isAvailable('site')){
			throw new \Exception("site module is not available!");
		}
		
		if(!\GO::modules()->isAvailable('defaultsite')){
			throw new \Exception("defaultsite module is not available!");
		}

		$siteModule = new \GO\Base\Model\Module();
		$siteModule->name='site';
		if(\GO::modules()->isInstalled('site') || $siteModule->save()){
			$defaultSiteModule = new \GO\Base\Model\Module();
			$defaultSiteModule->name='defaultsite';
			if(!$defaultSiteModule->save()){
				$response['success'] = false;
				$response['feedback'] = \GO::t("Could not install the \"defaultsite\" module.", "defaultsite");
			}
		} else {
			$response['success'] = false;
			$response['feedback'] = \GO::t("Could not install the \"site\" module.", "defaultsite");
		}

		echo $this->renderJson($response);
	}
}
