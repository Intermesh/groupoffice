<?php

namespace GO\Addressbook\Customfields\Model;


class Company extends \GO\Customfields\Model\AbstractCustomFieldsRecord{
	public function extendsModel() {		
		return "GO\Addressbook\Model\Company";
	}
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return \GO\Addressbook\Model\CompanyCustomFieldsRecord 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
}