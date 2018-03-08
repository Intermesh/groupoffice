<?php

namespace GO\Customfields\Customfieldtype;


class Heading extends AbstractCustomfieldtype{
	
	public function name(){
		return 'Heading';
	}
	
	public function formatFormOutput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model){
		return '';
	}
	public function formatFormInput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		return '';
	}
	
	public function formatDisplay($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		return '';
	}
	
	
	public function selectForGrid(){
		return false;
	}
}