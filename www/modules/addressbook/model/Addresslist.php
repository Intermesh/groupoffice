<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author Wilmar van Beusekom <wilmar@intermesh.nl>
 * @property string $default_salutation
 * @property string $name
 * @property int $acl_id
 * @property int $user_id
 * @property int $id
 * @property int $addresslist_group_id
 * @property int $mtime
 * @property int $ctime
 */


namespace GO\Addressbook\Model;


class Addresslist extends \GO\Base\Db\ActiveRecord {

	// TODO : move language from mailings module to addressbook module
	protected function getLocalizedName() {
		return \GO::t("Address list", "addressbook");
	}
	
	public function relations(){
		return array(
				'contacts' => array('type'=>self::MANY_MANY, 'model'=>'GO\Addressbook\Model\Contact', 'field'=>'addresslist_id', 'linkModel' => 'GO\Addressbook\Model\AddresslistContact'),
				'companies' => array('type'=>self::MANY_MANY, 'model'=>'GO\Addressbook\Model\Company', 'field'=>'addresslist_id', 'linkModel' => 'GO\Addressbook\Model\AddresslistCompany'),
				'sentMailings' => array('type'=>self::HAS_MANY, 'model'=>'GO\Addressbook\Model\SentMailing','field'=>'addresslist_id', 'delete'=> self::DELETE_CASCADE),
				'addresslistGroup' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Addressbook\Model\AddresslistGroup', 'field'=>'addresslist_group_id')
		);
	}
	
	public function aclField(){
		return 'acl_id';	
	}
	
	public function tableName(){
		return 'ab_addresslists';
	}
	
	public function hasLinks() {
		return true;
	}
	
	protected function getCacheAttributes() {
		return array('name'=>$this->name);
	}
	
	/**
	 * Add a contact to this addresslist
	 * 
	 * @param Contact $contact
	 */
	public function addContact($contact){
		$this->addManyMany('contacts', $contact->id);
	}
	
	/**
	 * Add a company to this addresslist
	 * 
	 * @param Company $company
	 */
	public function addCompany($company){
		$this->addManyMany('companies', $company->id);
	}
}
