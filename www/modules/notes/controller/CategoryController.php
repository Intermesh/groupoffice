<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id$
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @author Merijn Schering <mschering@intermesh.nl>
 */


namespace GO\Notes\Controller;

use GO;
use GO\Base\Controller\AbstractController;
use GO\Base\Data\ColumnModel;
use GO\Base\Data\DbStore;
use GO\Base\View\JsonView;
use GO\Notes\Model\Category;

/**
 * 
 * The Category controller
 * 
 */
class CategoryController extends AbstractController {
	
	protected function init() {
		
		$this->view = new JsonView();
		parent::init();
	}

	protected function actionStore($params) {

		$columnModel = new ColumnModel(Category::model());
		$columnModel->formatColumn('user_name', '$model->user ? $model->user->name : 0');
		
		$store = new DbStore('GO\Notes\Model\Category', $columnModel, $params);
		$store->defaultSort = 'name';
		$store->multiSelectable('no-multiselect');

		echo $this->render('store',array('store'=>$store));
	}

	protected function actionCreate() {
		$model = new Category();

		if(GO::request()->isPost()){
			$model->setAttributes(GO::request()->post['category']);
			$model->save();
			
			echo $this->render('submit', array('category'=>$model));
		}else
		{
			echo $this->render('form',array('category'=>$model));
		}		
	}
	
	
	protected function actionupdate($id) {
		$model = Category::model()->findByPk($id);

		if(GO::request()->isPost()){
			$model->setAttributes(GO::request()->post['category']);
			$model->save();
			
			echo $this->render('submit',array('category'=>$model));
		}else
		{
			echo $this->render('form',array('category'=>$model));
		}		
	}
	
	protected function actionToggle($id, $enabled){
		
		//TODO: Create simpler multiselect component
		
		$value = GO::config()->get_setting('ms_no-multiselect', GO::user()->id);
		
		$value = !empty($value) ? explode(',',$value) : array();

		if($enabled){
			$value[]=$id;			
		}else
		{
			if(($key = array_search($id, $value)) !== false) {
				unset($value[$key]);
			}
		}
		
		$value = array_unique($value);
		
		
		GO::config()->save_setting('ms_no-multiselect', implode(',', $value), GO::user()->id);
		
		$this->render('json', array('success'=>true, 'value'=>$value));
	}

}
