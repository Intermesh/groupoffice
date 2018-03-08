<?php

namespace GO\Site\Customfieldtype;


class Sitefile extends \GO\Customfields\Customfieldtype\AbstractCustomfieldtype{
	
	public function name(){
		return 'Sitefile';
	}
	
	public function formatDisplay($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		$html="";
		if(!empty($attributes[$key])) {

			if(!\GO\Customfields\Model\AbstractCustomFieldsRecord::$formatForExport){
				$html='<a href="#" onclick=\'GO.linkHandlers["GO\\Files\\Model\\File"].call(this,"'.
					$attributes[$key].'");\' title="'.$attributes[$key].'">'.
						$attributes[$key].'</a>';
			}else
			{
				$html=$attributes[$key];
			}
		}
		return $html;
	}
	
	public function formatFormOutput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		
		if(!\GO\Customfields\Model\AbstractCustomFieldsRecord::$formatForExport){
			return parent::formatFormOutput($key, $attributes, $model);
		}else
		{
			return $this->getName($attributes[$key]);
		}		
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