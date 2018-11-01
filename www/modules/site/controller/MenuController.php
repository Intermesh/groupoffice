<?php
/**
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @package GO.modules.Site
 * @version $Id: MenuController.php 16757 2014-01-30 10:54:43Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 */
 
/**
 * The Menu controller object
 *
 * @package GO.modules.Site
 * @version $Id: MenuController.php 16757 2014-01-30 10:54:43Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 * 
 */

namespace GO\Site\Controller;


class MenuController extends \GO\Base\Controller\AbstractJsonController {
		
	/**
	 * Loads a store of content items of the current website
	 * 
	 * @param int $id
	 * @param int $site_id
	 */
	public function actionContentStore($menu_id){
		
		$menu = \GO\Site\Model\Menu::model()->findByPk($menu_id);
		
		$findCriteria = \GO\Base\Db\FindCriteria::newInstance()->addCondition('site_id', $menu->site_id);
		$findParams = \GO\Base\Db\FindParams::newInstance()->criteria($findCriteria);
		
		$store = new \GO\Base\Data\DbStore('GO\Site\Model\Content', new \GO\Base\Data\ColumnModel('GO\Site\Model\Content'), $_REQUEST,$findParams);
		
		echo $this->renderStore($store);
	}
	
	
	/**
	 * Loads a menu item
	 * 
	 * @param int $id
	 * @param int $site_id
	 */
	public function actionLoad($id = false,$site_id){
		$remoteComboFields = array();
		
		$model = $this->_loadModel($site_id,$id);
		
		if(!empty($model->content_id))
			$remoteComboFields['content_id']=$model->content->title;
		
		echo $this->renderForm($model,$remoteComboFields);
	}
	
	/**
	 * Create a new menu item
	 * 
	 * @param int $site_id
	 */
	public function actionCreate($site_id){
		$remoteComboFields = array();
		
		$model = $this->_loadModel($site_id);
		
		if(\GO\Base\Util\Http::isPostRequest()){
			$model->setAttributes($_POST);
			$model->save();
		}
		
		echo $this->renderForm($model,$remoteComboFields);
	}
	
	/**
	 * Update a new menu item
	 * 
	 * @param int $id
	 * @param int $site_id
	 */
	public function actionUpdate($id,$site_id){
		$remoteComboFields = array();
		
		$model = $this->_loadModel($site_id,$id);
		
		if(!empty($model->content_id))
			$remoteComboFields['content_id']=$model->content->title;
		
		if(\GO\Base\Util\Http::isPostRequest()){
			$model->setAttributes($_POST);
			$model->save();
		}
		
		echo $this->renderForm($model,$remoteComboFields);
	}
	
	/**
	 * Delete a new menu item
	 * 
	 * @param int $id
	 * @param int $site_id
	 */
	public function actionDelete($id,$site_id){
		
		$model = $this->_loadModel($site_id,$id);
		$model->delete();
		
		echo $this->renderForm($model);
	}
	
	/**
	 * Load the model menu object
	 * 
	 * @param int $siteId
	 * @param int $id
	 * @return \GO\Site\Model\Menu
	 * @throws Exception
	 */
	private function _loadModel($siteId, $id = false){
		
		if(!empty($id)){
			$model = \GO\Site\Model\Menu::model()->findByPk($id);
		}else{
			$model = new \GO\Site\Model\Menu();
			$model->site_id = $siteId;
		}
		
		if(!$model)
			Throw new \Exception('Model with id: '.$id.' not found.');
		
		return $model;
	}
	
}
	
