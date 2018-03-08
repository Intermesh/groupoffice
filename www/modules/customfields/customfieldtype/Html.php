<?php

namespace GO\Customfields\Customfieldtype;


class Html extends Textarea{
	
	public function name(){
		return 'HTML';
	}
	
	public function formatDisplay($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model){
		return $attributes[$key];
	}
	
	public function selectForGrid(){
		return false;
	}
}