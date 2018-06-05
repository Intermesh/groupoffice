<?php


namespace GO\Site\Controller;


class FrontController extends \GO\Site\Components\Controller {
	protected function allowGuests() {
		return array('content','thumb','search','ajaxwidget', 'sitemap');
	}
	
	protected function actionContent($params){
		
		if(!isset($params['slug']))
			$params['slug']='';
		
		$content = \GO\Site\Model\Content::model()->findBySlug($params['slug']);
		
		if(!$content){
			
			header("HTTP/1.0 404 Not Found");
			header("Status: 404 Not Found");
			
			echo $this->render('/site/404');
		}else{
			
			$this->setPageTitle($content->metaTitle);
			
			if(!empty($content->meta_description)){
				\Site::scripts()->registerMetaTag($content->meta_description, 'description');
			}
			
			if(!empty($content->meta_keywords)){
				\Site::scripts()->registerMetaTag($content->meta_keywords, 'keywords');
			}
			
			// Check if the template is not empty
			if(empty($content->template)) {
				$defaultTemplate = \Site::config()->getDefaultTemplate();
				if(!empty($defaultTemplate))
					$content->template = $defaultTemplate;
			}
			
			echo $this->render($content->template,array('content'=>$content));
		}
	}
	
	/**
	 * Search through the site content
	 * 
	 * @param array $params
	 * @throws Exception
	 */
	protected function actionSearch($params){
		
		if(!isset($params['searchString']))
			throw new \Exception('No searchstring provided');
		
		$searchString = $params['searchString'];
		
		
		$searchParams = \GO\Base\Db\FindParams::newInstance()
						->select('*')
						->criteria(\GO\Base\Db\FindCriteria::newInstance()
										->addSearchCondition('title', $searchString, false)
										->addSearchCondition('meta_title', $searchString, false)
										->addSearchCondition('meta_description', $searchString, false)
										->addSearchCondition('meta_keywords', $searchString, false)
										->addSearchCondition('content', $searchString, false)
							);
		
		$columnModel = new \GO\Base\Data\ColumnModel();
		$store = new \GO\Base\Data\DbStore('GO\Site\Model\Content',$columnModel,$params,$searchParams);
	
		echo $this->render('search', array('searchResults'=>$store));
	}
	
	/**
	 * Will select all content item from a website and pass them to the sitemap template
	 * @param array $params [empty]
	 */
	protected function actionSitemap($params) {
		$sitemap = \GO\Site\Model\Content::getTreeNodes(2);
		
		echo $this->render('sitemap', array('sitemap'=>$sitemap));
	}
	
	/**
	 * This will copy a file in the files module to a public accessable folder
	 * 
	 * @param array $params
	 * - stromg src: path the the file relative the the sites public storage folder.
	 * @return the rsult of the thumb action on the core controller
	 * @throws \GO\Base\Exception\AccessDenied when unable to create the folder?
	 */
	protected function actionThumb($params){
			
		$rootFolder = new \GO\Base\Fs\Folder(\GO::config()->file_storage_path.'site/'.\Site::model()->id);
		$file = new \GO\Base\Fs\File(\GO::config()->file_storage_path.'site/'.\Site::model()->id.'/'.$params['src']);
		$folder = $file->parent();
		
		$ok = $folder->isSubFolderOf($rootFolder);
		
		if(!$ok)
			throw new \GO\Base\Exception\AccessDenied();
		
		
		$c = new \GO\Core\Controller\CoreController();
		return $c->run('thumb', $params, true, false);
	}
	
	/**
	 * Post to this action to execute a function inside a widget
	 * Using an AJAX call this the controller action
	 * 
	 * @param array $params
	 * - string widget_class eg. 'GO\Site\Widget\Plupload\Widget'
	 * - string widget_method name of the widgets static method eg. 'upload'
	 * @throws Exception when not all required parameters are supplied
	 */
	protected function actionAjaxWidget($params){
		if(!isset($params['widget_class']))
			throw new \Exception ('Widget class not given.');
		
		if(!isset($params['widget_method']))
			throw new \Exception('Widget method not given.');
			
		$widgetClassName = $params['widget_class'];
		$widgetMethod = $params['widget_method'];
				
		$response = $widgetClassName::$widgetMethod($params);

		echo $response;
	}
	
}
