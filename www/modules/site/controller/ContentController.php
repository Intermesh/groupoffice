<?php


namespace GO\Site\Controller;

use GO;
use GO\Site\Model\Content;
use GO\Site\Model\Site;



class ContentController extends \GO\Base\Controller\AbstractJsonController {
	
	
	/**
	 * Redirect to the homepage
	 * 
	 * @param array $params
	 */
	protected function actionRedirect($content_id){
		
		$content = Content::model()->findByPk($content_id);
		
		header("Location: ".GO::config()->host.'modules/site/index.php?site_id='.$content->site_id.'&slug='.$content->slug);
		exit();
	}
	
//	/**
//	 * 
//	 * 
//	 * @param array $params
//	 * @return array
//	 */
//	protected function actionDefaultSlug($params){
//		
//		$response = array();
//		$response['defaultslug']=false;
//		$response['success'] = false;
//		
//		if(empty($params['parentId']))
//			Throw new \Exception('No Parent ID given!');
//		
//		$parent = \GO\Site\Model\Content::model()->findByPk($params['parentId']);
//		
//		if(!$parent)
//			Throw new \Exception('No content item found with the following id: '.$params['parentId']);
//		
//		$response['defaultslug']=$parent->slug.'/';
//		$response['success'] = true;
//		
//		return $response;
//	}
	
	protected function actionTemplateStore($params){
		
		if(empty($params['siteId']))
			Throw new \Exception('No Site ID given!');
		
		$site = Site::model()->findByPk($params['siteId']);
		
		if(!$site)
			Throw new \Exception('No site found with the following id: '.$id);
		
		$templateFiles = array();
		
		$config = new \GO\Site\Components\Config($site);

		if($config->templates){			
			// Read config items and convert to json
			foreach($config->templates as $path=>$name)
				$templateFiles[] = array('path'=>$path,'name'=>$name);
		}
		
		$response = array(
				"success" => true,
				"results" => $templateFiles,
				'total' => count($templateFiles)
		);
		
		echo $this->renderJson($response);
	}
	
//	protected function actionLoad($params){
//
//		$model= \GO\Site\Model\Content::model()->createOrFindByParams($params);
//		
//		
//		echo $this->renderForm($model, $remoteComboFields, $extraFields);
//	}
	
	protected function actionUpdate($params){
		
		if(empty($params['id']))
			Throw new \Exception('No ID given!');
		
		$model = $this->_loadModel($params['id']);
			
		unset($params['id']); // unset because it doesn't need to be updated
				
		$model->setAttributes($params);
		
		
		if(\GO\Base\Util\Http::isPostRequest()){	
			
			
			$model->save();
			echo $this->renderSubmit($model);
		}  else {
			$remoteComboFields = array();
		
			echo $this->renderForm($model, $remoteComboFields, array(
					'baseslug'=>$model->baseslug, 
					'parentslug'=>$model->parentslug));
		}
	}
	
	protected function actionCreate($params) {
		$model = new \GO\Site\Model\Content();
		$model->setAttributes($params);
				
		$model->setDefaultTemplate();

		if(\GO\Base\Util\Http::isPostRequest()){
			$model->save();
			echo $this->renderSubmit($model);
		}  else {
			echo $this->renderForm($model, array() , array(
					'baseslug'=>$model->baseslug, 
					'parentslug'=>$model->parentslug));
		}
  }
		
	protected function actionDelete($params) {
		if(empty($params['id']))
			Throw new \Exception('No ID given!');
		
		$model = $this->_loadModel($params['id']);
		
		$response = array();
		
		$response['success'] = $model->delete();
		
		echo $this->renderJson($response);
	}
	
	private function _loadModel($id){
		$model = \GO\Site\Model\Content::model()->findByPk($id);
		
		if(!$model)
			Throw new \Exception('No content item found with the following id: '.$id);

		return $model;
	}
	
	public function actionContentTree($params){
		$response=array();
	
		if(empty($params['site_id']))
			Throw new \Exception('No Site ID given!');
				
		if(!isset($params['node']))
			return $response;
		
		$site = Site::model()->findByPk($params['site_id']);
		
		$args = explode('_', $params['node']);
		
		$siteId = $args[0];
		
		if(!isset($args[1]))
			$type = 'root';
		else
			$type = $args[1];
		
		if(isset($args[2]))
			$parentId = $args[2];
		else
			$parentId = null;
		
		switch($type){
			case 'content':
				if($parentId === null){
					$response = $site->loadContentNodes();
				} else {
					$parentNode = \GO\Site\Model\Content::model()->findByPk($parentId);
					if($parentNode)
						$response = $parentNode->getChildrenTree();
				}
				break;
//			case 'news':
//				$response = \GO\Site\Model\News::getTreeNodes($site);
//				break;
		}
		
		echo $this->renderJson($response);
	}

	
	
}
