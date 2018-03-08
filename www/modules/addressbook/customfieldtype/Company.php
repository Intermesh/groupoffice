<?php

namespace GO\Addressbook\Customfieldtype;


class Company extends \GO\Customfields\Customfieldtype\AbstractCustomfieldtype{
	
	public function name(){
		return 'Company';
	}
	
	public static function getModelName() {
		return 'GO\Addressbook\Model\Company';
	}
	
	public function includeInSearches() {
		return true;
	}
	
	public function formatDisplay($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		$html="";
		if(!empty($attributes[$key])) {

			if(!\GO\Customfields\Model\AbstractCustomFieldsRecord::$formatForExport){
				$name = htmlspecialchars($this->getName($attributes[$key]), ENT_COMPAT, 'UTF-8');
				$html='<a href="#" onclick=\'GO.linkHandlers["GO\\\\Addressbook\\\\Model\\\\Company"].call(this,'.
					$this->getId($attributes[$key]).');\' title="'.$name.'">'.
						$name.'</a>';
			}else
			{
				$html=$this->getName($attributes[$key]);
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
}