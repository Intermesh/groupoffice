<?php

namespace GO\Customfields\Customfieldtype;


class Checkbox extends AbstractCustomfieldtype{
	
	public function name(){
		return 'Checkbox';
	}
	
	public function formatDisplay($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		return !empty($attributes[$key]) ? \GO::t('yes') : \GO::t('no');
	}
	
	public function formatFormInput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		$attributes[$key]=empty($attributes[$key]) || $attributes[$key]=="false" ? 0 : 1;
		
		return $attributes[$key];
	}
	
	public function fieldSql() {
		return "BOOLEAN NOT NULL DEFAULT '0'";
	}
}