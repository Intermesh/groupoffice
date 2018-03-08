<?php

namespace GO\Customfields\Customfieldtype;


class Datetime extends Date{
	
	public function name(){
		return 'Date time';
	}
	
	public function formatFormOutput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model){
		$unixtime = strtotime($attributes[$key]);
		return $attributes[$key]=\GO\Base\Util\Date::get_timestamp($unixtime, true);
	}
	public function formatFormInput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {

//		if(empty($attributes[$key]))
//			return null;
//		
//		$time = $attributes[$key];
//		
//		if(isset($attributes[$key."_hour"]))
//			$time .= ' '.$attributes[$key."_hour"].':'.$attributes[$key."_min"];
		
		return \GO\Base\Util\Date::to_db_date($attributes[$key], true);
	}
	
	public function formatDisplay($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		return \GO\Base\Util\Date::get_timestamp(strtotime($attributes[$key]), true);
	}
	
	public function fieldSql() {
		return 'DATETIME NULL';
	}
}