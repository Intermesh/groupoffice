<?php

namespace GO\Calendar\Customfields\Model;


class Calendar extends \GO\Customfields\Model\AbstractCustomFieldsRecord{
	public function extendsModel() {		
		return "GO\Calendar\Model\Calendar";
	}
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return \GO\Calendar\Model\CalendarCustomFieldsRecord 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
}