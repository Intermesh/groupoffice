<?php

namespace GO\Addressbook\Customfields\Model;


class Contact extends \GO\Customfields\Model\AbstractCustomFieldsRecord{
	public function extendsModel() {		
		return "GO\Addressbook\Model\Contact";
	}
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return \GO\Addressbook\Model\ContactCustomFieldsRecord 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
}