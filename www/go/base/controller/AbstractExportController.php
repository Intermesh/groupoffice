<?php

namespace GO\Base\Controller;


abstract class AbstractExportController extends AbstractController{

	/**
	 * Attributes that should be exported from the statement result.
	 * 
	 * @return array
	 */
	
	abstract function exportableAttributes();
	
//	/**
//	 * array indexed by parameter name
//	 * 
//	 * array('date'=>array('label'=>'Some date','gotype'=>'date'))
//	 * @return array
//	 */
//	abstract function exportParameters();
	
	public function actionAttributes($params){
		
		$store = new \GO\Base\Data\ArrayStore();
		
		$attr = $this->exportableAttributes();
		
		$className = get_class($this);
		$selected = \GO::config()->get_setting($className.'_attributes');
		if($selected){
			$selected=  unserialize($selected);
		}else
		{
			$selected=array();
		}
		
		foreach($attr as $attribute=>$label){
			$store->addRecord(array(
				'id'=>$attribute,
				'name'=>$label,
				'checked'=>in_array($attribute, $selected)
			));
		}		
		return $store->getData();		
	}
	
//	public function actionParameters($params){
//		$store = new \GO\Base\Data\ArrayStore();
//		
//		$attr = $this->exportParameters();
//		
//		foreach($attr as $paramName=>$options){
//			$store->addRecord(array(
//				'name'=>$paramName,
//				'label'=>$options['label'],
//				'gotype'=>$options['gotype']
//			));
//		}		
//		return $store->getData();	
//	}
	
	public function actionExport($params){

//		$params['book_id']=2;
		
		$params['attributes']=empty($params['attributes']) ? $this->exportableAttributes() : explode(',',$params['attributes']);
	
		$className = get_class($this);
		
		\GO::config()->save_setting($className.'_attributes', serialize($params['attributes']));
		
		$this->export($params);
	}
	
	abstract function export($params);
	
}
