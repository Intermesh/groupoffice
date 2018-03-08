<?php

namespace GO\Calendar\Customfields\Model;


class Event extends \GO\Customfields\Model\AbstractCustomFieldsRecord{
	public function extendsModel() {		
		return "GO\Calendar\Model\Event";
	}
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return \GO\Calendar\Model\EventCustomFieldsRecord 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
}