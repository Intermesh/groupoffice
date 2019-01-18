<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/**
 * 
 * The Addressbook model
 * 
 * @property String $name The name of the Addressbook
 * @property int $files_folder_id
 * @property bool $users true if this addressbook is the special addressbook that holds the Group-Office users.
 * @property string $default_salutation
 * @property boolean $shared_acl
 * @property int $acl_id
 * @property int $user_id
 * @property boolean $user_id
 */


namespace GO\Addressbook\Model;


 class Addressbook extends \GO\Base\Model\AbstractUserDefaultModel{
		 
	 /**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Addressbook 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	public function aclField(){
		return 'acl_id';	
	}
	
	public function settingsModelName() {
		return "GO\Addressbook\Model\Settings";
	}
	
	public function settingsPkAttribute() {
		return 'default_addressbook_id';
	}
	
	public function tableName(){
		return 'ab_addressbooks';
	}
	
	public function hasFiles(){
		return true;
	}
	
	public function relations(){
		return array(
				'contacts' => array('type'=>self::HAS_MANY, 'model'=>'GO\Addressbook\Model\Contact', 'field'=>'addressbook_id', 'delete'=>true),
				'companies' => array('type'=>self::HAS_MANY, 'model'=>'GO\Addressbook\Model\Company', 'field'=>'addressbook_id', 'delete'=>true)
		);
	}
	
	/**
	 * Get's a unique URI for the calendar. This is used by CalDAV
	 * 
	 * @return StringHelper
	 */
	public function getUri(){
		return preg_replace('/[^\w-]*/', '', (strtolower(str_replace(' ', '-', $this->name)))).'-'.$this->id;
	}
	
	protected function beforeSave() {
		
		if(!isset($this->default_salutation))
			$this->default_salutation=\GO::t("Dear {first_name}", "addressbook");
			
		return parent::beforeSave();
	}
	
	public function beforeDelete() {
		
		if($this->users)			
			throw new \Exception("You can't delete the users addressbook");
		
		return parent::beforeDelete();
	}
	
	/**
	 * Get the addressbook for the user profiles. If it doesn't exist it will be
	 * created.
	 * 
	 * @return Addressbook 
	 */
	public function getUsersAddressbook(){
		$ab = Addressbook::model()->findSingleByAttribute('users', '1'); //\GO::t("Users"));
		if (!$ab) {
			$ab = new Addressbook();
			$ab->name = \GO::t("Users");
			$ab->users = true;
			$ab->save(true);
			
			$ab->acl->addGroup(\GO::config()->group_internal);
		}
		return $ab;
	}
	
	public function defaultAttributes() {
		$attr = parent::defaultAttributes();
		$attr['default_salutation']=\GO::t("Dear {first_name}", "addressbook");
		return $attr;
	}

	/**
	 * Remove all contacts and companies from the addressbook
	 */
	public function truncate(){
		$contacts = $this->contacts;
		
		foreach($contacts as $contact){
			$contact->delete();
		}
		
		$companies = $this->companies;
		
		foreach($companies as $company){
			$company->delete();
		}
	}
	
	/**
	 * joining on the addressbooks can be very expensive. That's why this 
	 * session cached useful can be used to optimize addressbook queries.
	 * 
	 * @return array
	 */
	public function getAllReadableAddressbookIds(){
		if(!isset(\GO::session()->values['addressbook']['readable_addressbook_ids'])){
			\GO::session()->values['addressbook']['readable_addressbook_ids']=array();
			$stmt = $this->find();
			while($ab = $stmt->fetch()){
				\GO::session()->values['addressbook']['readable_addressbook_ids'][]=$ab->id;
			}
		}
		
		return \GO::session()->values['addressbook']['readable_addressbook_ids'];
	}
}
