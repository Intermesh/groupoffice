<?php
namespace GO\Base\View;

use GO;
use GO\Base\Export\ExportCSV;
use GO\Base\Export\ExportHTML;
use GO\Base\Export\ExportPDF;
use GO\Base\Export\ExportXLS;

class ExportView extends AbstractView{
	
	private $_store;
	private $_columnModel;
	private $_model;
	private $_findParams;
	private $_showHeader = true;
	private $_humanHeaders = false;
	private $_title;
	private $_orientation = 'P';
	private $_extraParams = array('name'=>'');
	

	public function render($viewName, $data) {
		
		$fn = "_render".$viewName;
		
		if(isset($data['savedExportModel'])){
			$this->_processExport($data);
		}

		return $this->$fn($data);
	}
	
	private function _renderHtml($data){
		$export = new ExportHTML(
						$this->_store, 
						$this->_columnModel, 
						$this->_model, 
						$this->_findParams, 
						$this->_showHeader, 
						$this->_humanHeaders, 
						$this->_title, 
						$this->_orientation, 
						$this->_extraParams
						);

		$export->output();
	}
	
	private function _renderCsv($data){
		$export = new ExportCSV(
						$this->_store, 
						$this->_columnModel, 
						$this->_model, 
						$this->_findParams, 
						$this->_showHeader, 
						$this->_humanHeaders, 
						$this->_title, 
						$this->_orientation, 
						$this->_extraParams
						);

		$export->output();
	}
	
	private function _renderXls($data){
		$export = new ExportXLS(
						$this->_store, 
						$this->_columnModel, 
						$this->_model, 
						$this->_findParams, 
						$this->_showHeader, 
						$this->_humanHeaders, 
						$this->_title, 
						$this->_orientation, 
						$this->_extraParams
						);

		$export->output();
	}
	
	private function _renderPdf($data){
		if(class_exists('\GOFS\ExportPDF')) {
			$class = '\GOFS\ExportPDF';
		} else {
			$class = '\GO\Base\Export\ExportPDF';
		}
		$export = new $class(
						$this->_store,				//	
						$this->_columnModel,	//
						$this->_model,				//
						$this->_findParams,		//
						$this->_showHeader,		//
						$this->_humanHeaders, //
						$this->_title,				//
						$this->_orientation,	//
						$this->_extraParams
						);

		$export->output();
	}
	
	private function _processExport($data) {
		
		\GO::setMaxExecutionTime(0);
		
		$className = $data['savedExportModel']->class_name;
		$class = new $className($data['savedExportModel']->export_columns);

		// Get the model for this export
		$this->_model = $class->getModel();
		
		// Translate orientation from horizontal/vertical to landscape/portrait
		$this->_orientation = $data['savedExportModel']->orientation === 'H'?'L':'P';
		
		// Retreive the name of the file
		$this->_title = $class->getName();
		
		if(!empty($data['savedExportModel']->name)) {
			$this->_extraParams['name'] = $data['savedExportModel']->name;
		}
		
		// Show the headers in the export (could be 1 instead of true or 0 instead of false)
		$this->_showHeader = $data['savedExportModel']->include_column_names == true?true:false;
		
		// Use database column names or human readable column names
		$this->_humanHeaders = $data['savedExportModel']->use_db_column_names == true?false:true;

		// Get the findparams for the export
		$this->_findParams = $class->getFindParams();
		
		// Create the column model
		$this->_columnModel = $class->getColumnModel();
		
		// Create the store for this export
		$this->_store = $class->getStore();
	}
	
		
	private function _renderException($data){
		echo $data['response'];
	}
	
}
