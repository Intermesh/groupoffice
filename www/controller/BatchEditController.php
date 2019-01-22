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
 * Abstract class to export data in GO
 * 
 * 
 * @package GO.base.controller
 * @version $Id: BatchEditController.php 7607 2011-11-16 09:17:42Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits <wsmits@intermesh.nl> 
 * 
 */

namespace GO\Core\Controller;


class BatchEditController extends \GO\Base\Controller\AbstractController {
	
	/**
	 * Update the given id's with the given data
	 * The params array must contain at least:
	 * 
	 * @param array $params 
	 * <code>
	 * $params['data'] = The new values that need to be set
	 * $params['keys'] = The keys of the records that need to get the new data
	 * $params['model_name']= The model classname of the records that need to be updated
	 * </code>
	 */
	protected function actionSubmit($params) {
		if(empty($params['data']) || empty($params['keys']) || empty($params['model_name']))
			return false;
		
		$data = json_decode($params['data'], true);
		
		$keys = json_decode($params['keys'], true);
		
		if(is_array($keys)) {
			foreach($keys as $key) {
				$model = \GO::getModel($params['model_name'])->findByPk($key);
				if(!empty($model)) {
					foreach ($data as &$item) {
						if($item['mergeable'] && !$item['replace']) {
							
							if($item['gotype']=='customfield') {
								
								switch ($item['customfieldtype']) {
									case 'GO\Customfields\Customfieldtype\Select':
										if($item['multiselect']) {
											$type = 'multiselect';
										}
										break;
									case 'GO\Customfields\Customfieldtype\Textarea':

										$type = 'textarea';
										break;

									case 'GO\\Customfields\\Customfieldtype\\Text':
										$type = 'textfield';
										break;

									default:
										break;
								}
								$existing = $model->resolveAttribute($item['name']);
								
							} else {
								$type = $item['gotype'];
								$existing = $model->{$item['name']};
							}
							
							if(!empty($existing) && !empty($item['value'])) {
								switch ($type) {
									case 'textfield':

										$item['value'] = $existing .' ; '. $item['value'];

										break;
									case 'textarea':
										$item['value'] = $existing."\n". $item['value'];

										break;

									case 'multiselect':

										$existing = explode('|', $existing);
										$new = explode('|', $item['value']);
										$existing = array_merge($existing, $new);
										$existing = array_unique($existing);
										sort($existing);
										$item['value'] = implode('|', $existing);									

										break;

									default:
										break;
								}
							}
						}
					}		
					$this->_updateModel($model, $data);
				}
			}
		}
		
		$response['success'] = true;
		
		$this->fireEvent('submit', array(
				&$this,
				&$response,
				&$model,
				&$params
		));
		
		return $response;
	}
	
	/**
	 * Update the model with the given attributes
	 *  
	 * @param \GO\Base\Db\ActiveRecord $model
	 * @param array $data
	 * @return Boolean 
	 */
	private function _updateModel($model, $data) {
		
		$changeAttributes = array();
		
		foreach($data as $attr=>$value){
			if($value['value'] || $value['replace']) {
					$changeAttributes[$value['name']] = $value['value'];
			}
		}

		$model->setAttributes($changeAttributes);
		
		return $model->save();
	}
	
	
	/**
	 * Return all attribute labels and names for the given object type
	 * With this data the batchedit form can be created
	 * 
	 * @param array $params 
	 * <code>
	 * $params['model_name']= The model classname of the records that need to be updated
	 * </code>
	 */
	protected function actionAttributesStore($params) {
		if(empty($params['model_name']))
			return false;
		
		$mergeableTypes = array('textfield', 'textarea');
		
		$tmpModel = new $params['model_name']();
		$columns = $tmpModel->getColumns();
		
		$params['excludeColumns']=array('ctime','mtime','model_id');
		
		if(isset($params['exclude']))
			$params['excludeColumns']=  array_merge($params['excludeColumns'],explode(',', $params['exclude']));
		
		$rows = array();
		foreach($columns as $key=>$value) {

			if(!in_array($key, $params['excludeColumns'])) {
				$row = array();

				$row['name']= $key;
				$row['label']= $tmpModel->getAttributeLabel($key);
				$row['value']='';
				$row['edit']='';
				$row['customfield'] = false;
				$row['multiselect'] = false;
				$row['gotype']=!empty($value['gotype'])?$value['gotype']:'';
				if(!empty($value['regex'])){
					$regexDelimiter = substr($value['regex'], 0,1);
					$parts = explode($regexDelimiter, $value['regex']);
					$row['regex']=$parts[1];
					$row['regex_flags']=$parts[2];					
				}else
				{
					$row['regex_flags']='';
					$row['regex']='';
				}
				
				
				$row['has_data'] = false;
				if(in_array($row['gotype'], $mergeableTypes)) {
					$row['mergeable'] = true;
				} else {
					$row['mergeable'] = false;
				}
				
				$rows[] = $row;
			}
		}
		
//		// Get the customfields for this model
//		$cf = $tmpModel->getCustomfieldsRecord();
//		if($cf){
//			$cfcolumns = $cf->getColumns();
//
//			$cfrows = array();
//			foreach($cfcolumns as $key=>$value) {
//				if(!in_array($key, $params['excludeColumns']) && !empty($value['gotype'])) {
//					$row = array();
//					
//					$row['name']= 'customFields.'.$key;
//					$row['label']= $cf->getAttributeLabel($key);
//					$row['value']='';
//					$row['edit']='';
//					$row['customfield'] = true;
//					$row['gotype']=$value['gotype'];
//					$row['category_id']=$value['customfield']->category->id;
//					$row['category_name']=$value['customfield']->category->name;
//					$row['customfieldtype']=$value['customfield']->datatype;
//					$row['multiselect']=$value['customfield']->getOption('multiselect');
//					
//					$row['has_data'] = false;
//					
//					if($value['customfield']->getOption('multiselect') || in_array($value['customfield']->datatype , array('GO\Customfields\Customfieldtype\Textarea', 'GO\Customfields\Customfieldtype\Text'))) {
//						$row['mergeable'] = true;
//					} else {
//						$row['mergeable'] = false;
//					}
//				
//					$cfrows[] = $row;
//				}
//			}
//		
//			
//			$module = call_user_func_array($params['model_name'].'::model', array());
//			$stmt = call_user_func_array(array($module, 'find'), 
//							array(\GO\Base\Db\FindParams::newInstance()->debugSql()->ignoreAcl()
//							->criteria(
//											\GO\Base\Db\FindCriteria::newInstance()
//											->addInCondition($params['primaryKey'], json_decode($params['keys']))
//											)
//									)
//							);		
//			
//			
//			usort($cfrows,function ($a,$b) {
//				if ($a['category_name']==$b['category_name'])
//					return strcmp($a['label'],$b['label']);
//				else
//					return strcmp($a['category_name'],$b['category_name']);
//			});
//			
//			$rows = array_merge($rows,$cfrows);
//			
//			
//			foreach ($stmt as $value) {
//
//				foreach ($rows as &$field) {
//					if (!$field['customfield']) {
//						if (!empty($value->{$field['name']})) {
//							$field['has_data'] = true;
//						}
//					} else {
//						if (!empty($value->customfieldsRecord->{$field['name']})) {
//							$field['has_data'] = true;
//						}
//					}
//				}
//			}
//		}
		$response['results'] = $rows;
				
		
		$this->fireEvent('store', array(
				&$this,
				&$response,
				&$tmpModel,
				&$params
			)
		);	
		
		return $response;
	}
}
