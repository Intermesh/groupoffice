<?php

namespace GO\Customfields\Customfieldtype;


class Date extends AbstractCustomfieldtype{
	
	public function name(){
		return 'Date';
	}
	
	public function formatDisplay($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		return \GO\Base\Util\Date::format($attributes[$key], false);
	}
	
	public function formatFormOutput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model){
		return \GO\Base\Util\Date::format($attributes[$key], false);
	}
	public function formatFormInput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		return \GO\Base\Util\Date::to_db_date($attributes[$key]);
	}
	
	public function fieldSql() {
		return 'DATE NULL';
	}
}