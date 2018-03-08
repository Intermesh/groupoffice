<?php

namespace GO\Site\Customfieldtype;


class Sitemultifile extends \GO\Customfields\Customfieldtype\AbstractCustomfieldtype{
	
	public function name(){
		return 'Sitemultifile';
	}
	
	public function formatDisplay($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		
		return 'No display created (in Sitemultifile)';
	}
	
	public function formatFormOutput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {

		$column = $model->getColumn($key);
		if(!$column)
			return null;
				
		$fieldId = $column['customfield']->id;
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
				->select('COUNT(*) AS count')
				->single()
			->criteria(\GO\Base\Db\FindCriteria::newInstance()
				->addCondition('model_id', $model->model_id)
				->addCondition('field_id', $fieldId));

		$model = \GO\Site\Model\MultifileFile::model()->find($findParams);
		
		$string = '';
		$string = sprintf(\GO::t('multifileSelectValue','site'), $model->count);
		
		return $string;
	}	
	
	public function formatRawOutput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {

		$column = $model->getColumn($key);
		if(!$column)
			return null;
		
		$fieldId = $column['customfield']->id;
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
				->ignoreAcl()
				->order('mf.order')
				->joinModel(array(
					'model' => 'GO\Site\Model\MultifileFile',
					'localTableAlias' => 't',
					'localField' => 'id',
					'foreignField' => 'file_id',
					'tableAlias' => 'mf'))
		
			->criteria(\GO\Base\Db\FindCriteria::newInstance()
				->addCondition('model_id', $model->model_id,'=','mf')
				->addCondition('field_id', $fieldId,'=','mf'));

		return \GO\Files\Model\File::model()->find($findParams,'false',true);
	}	
	
	public function selectForGrid(){
		return false;
	}
	
	/**
	 * Function to enable this customfield type for some models only.
	 * When no modeltype is given then this customfield will work on all models.
	 * Otherwise it will only be available for the given modeltypes.
	 * 
	 * Example:
	 *	return array('GO\Site\Model\Content','GO\Site\Model\Site');
	 *  
	 * @return array
	 */
	public function supportedModels(){
		return array('GO\Site\Model\Content','GO\Site\Model\Site');
	}
}