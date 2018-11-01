<?php

namespace GO\Customfields\Customfieldtype;


class UserGroup extends \GO\Customfields\Customfieldtype\AbstractCustomfieldtype{
	
	public function name(){
		return 'User group';
	}
	
//	public static function getModelName() {
//		return 'GO\Addressbook\Model\Contact';
//	}
	
	public function includeInSearches() {
		return true;
	}

	public function formatDisplay($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		$html="";
		if(!empty($attributes[$key])) {
			$html=$this->getName($attributes[$key]);

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
}
