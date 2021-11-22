<?php
/*
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

/**
 * class to export data in GO
 * 
 * 
 * @package GO.base.controller
 * @version $Id: AbstractExportController.php 7607 2011-06-15 09:17:42Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits <wsmits@intermesh.nl> 
 * @abstract
 */

namespace GO\Core\Controller;

use GO;
use GO\Base\Controller\AbstractController;
use GO\Base\Fs\Folder;
use GO\Base\Export\Settings;
use GO\Base\View\JsonView;
use GO\Base\View\ExportView;
use GO\Base\Model\SavedExport;
use GO\Base\Db\FindParams;
use GO\Base\Db\FindCriteria;
use GO\Base\Data\ColumnModel;
use GO\Base\Data\DbStore;

class ExportController extends AbstractController { 

	protected function init() {
		
		$this->view = new JsonView();
		parent::init();
	}
	
	/**
	 * 
	 * @param StringHelper $class_name
	 * @param StringHelper $export_columns
	 * @param boolean $include_column_names
	 * @param StringHelper $orientation
	 * @param boolean $use_db_column_names
	 * @param StringHelper $view
	 * @param int $id
	 * @throws Exception
	 */
	protected function actionExport($class_name,$export_columns,$include_column_names,$orientation,$use_db_column_names,$view,$id=null) {
		
		
		// Fixes the problem that a string "false" is handled as a bool "true".
		if($include_column_names === "false"){
			$include_column_names=false;
		}
		
		if($use_db_column_names === "false"){
			$use_db_column_names=false;
		}
				
		$currentGrid = new $class_name();
		
		if(!in_array($view, $currentGrid->getSupportedViews())){
			Throw new Exception('No supported view');
		}

		$this->view = new ExportView();
		
		// If the id is set then search the savedExportModel
		if(isset($id)){
			$savedExportModel = SavedExport::model()->findByPk($id);
		}
		
		// If there is no savedExportModel found then create a temporary one for this export
		if(!$savedExportModel){
			$savedExportModel = new SavedExport();
			$savedExportModel->class_name = $class_name;
			$savedExportModel->export_columns = $export_columns;
			$savedExportModel->include_column_names = $include_column_names;
			$savedExportModel->orientation = $orientation;
			$savedExportModel->use_db_column_names = $use_db_column_names;
			$savedExportModel->view = $view;
		}
		
		echo $this->render($view, array('savedExportModel'=>$savedExportModel));
	}
	
	public function actionSavedExportsStore($className){

		$columnModel = new ColumnModel(SavedExport::model());

		// "t.name as text" needs to be added because this store is also used to create the menu. (And the menu expects the "text" property for it's label)
		$findParams = FindParams::newInstance()->select('t.*,t.name as text')->criteria(FindCriteria::newInstance()->addCondition('class_name', $className));
		
		$store = new DbStore('GO\Base\Model\SavedExport', $columnModel, GO::request()->post, $findParams);
		$store->defaultSort = 'id';

		echo $this->render('store', array('store'=>$store));
	}
	
	/**
	 * Updates a SavedExport POST for save and GET for retrieve
	 * 
	 * @param $id SavedExport ID
	 */
	protected function actionUpdate($id,$className) {

		$model = SavedExport::model()->findByPk($id);
		
		$currentGrid = new $className;
		
		$sViews = $currentGrid->getSupportedViews();
		$supportedViews = array();
		foreach($sViews as $view){
			$supportedViews[] = array('view'=>$view);
		}
	
		$availableColumns = $currentGrid->getColumns();
	
		if (!$model)
			throw new \GO\Base\Exception\NotFound();
		
		if(GO::request()->isPost()){
			$savedExport = GO::request()->post['savedExport'];

			$model->setAttributes($savedExport);
			$model->save();

			echo $this->render('submit', array('savedExport'=>$model,'supportedViews'=>$supportedViews,'columns'=>$availableColumns));
		}else{
			echo $this->render('form',array('savedExport'=>$model,'supportedViews'=>$supportedViews,'columns'=>$availableColumns));
		}		
	}
	
	
	/**
	 * Creates a SavedExport
	 */
	protected function actionCreate($className,$exportColumns=false) {

		$model = new SavedExport();
		
		$currentGrid = new $className;
		
		// Which columns need to be exported
		if(!empty($exportColumns)){
			$model->export_columns = $exportColumns;
		}
		
		$sViews = $currentGrid->getSupportedViews();
		$supportedViews = array();
		foreach($sViews as $view){
			$supportedViews[] = array('view'=>$view);
		}
		
		$availableColumns = $currentGrid->getColumns();

		if(GO::request()->isPost()){
			$savedExport = GO::request()->post['savedExport'];

			$model->setAttributes($savedExport);
			$model->save();

			echo $this->render('submit',array('savedExport'=>$model,'supportedViews'=>$supportedViews,'columns'=>$availableColumns));
		}else{
			echo $this->render('form',array('savedExport'=>$model,'supportedViews'=>$supportedViews,'columns'=>$availableColumns));
		}		
	}

	
	/**
	 * Get the exporttypes that can be used and get the data for the checkboxes
	 * 
	 * @param array $params
	 * @return array 
	 */
	protected function actionLoad($params){
		$response = array();		
		$response['data'] = array();
		
		$settings =  Settings::load();
		$data = $settings->getArray();
		
		// retreive checkbox settings
		$response['data']['includeHeaders'] = $data['export_include_headers'];
		$response['data']['humanHeaders'] = $data['export_human_headers'];
		$response['data']['includeHidden'] = $data['export_include_hidden'];
		
		$response['outputTypes'] = $this->_getExportTypes(GO::config()->root_path.'go/base/export/');
		
		if(!empty($params['exportClassPath']))
			$response['outputTypes'] = array_merge($response['outputTypes'], $this->_getExportTypes(GO::config()->root_path.$params['exportClassPath']));
		
		$response['success'] =true;
		echo $this->render('json', $response);
	}

	
	/**
	 * Return the default found exportclasses that are available in the export 
	 * folder and where the showInView parameter is true
	 * 
	 * @return array 
	 */
	private function _getExportTypes($path) {
		
		$defaultTypes = array();
		
		$folder = new Folder($path);
		$contents = $folder->ls();
		
		$classParts = explode('/',$folder->stripRootPath());
		
		$classPath='GO\\';
		foreach($classParts as $part){
			if($part!='go' && $part != 'modules')
				$classPath.=ucfirst($part).'\\';
		}
		
		foreach($contents as $exporter) {
			if(is_file($exporter->path())) {
				$classname = $classPath.$exporter->nameWithoutExtension();
				if($classname != 'GO\Base\Export\ExportInterface' && $classname != 'GO\Base\Export\Settings')
				{
					$class = new \ReflectionClass($classname);
					$showInView=$class->getStaticPropertyValue('showInView');
					$name = $class->getStaticPropertyValue('name');
					$useOrientation = $class->getStaticPropertyValue('useOrientation');

					if($showInView)
						$defaultTypes[$classname] = array('name'=>$name,'useOrientation'=>$useOrientation);
				}
			}
		}

		return $defaultTypes;
	}
}
