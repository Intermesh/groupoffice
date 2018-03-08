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
 * @property int $company_id
 * @property int $addresslist_id
 */


namespace GO\Addressbook\Model;

use GO;


class AddresslistCompany extends \GO\Base\Db\ActiveRecord {
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Company 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function tableName(){
		return 'ab_addresslist_companies';
	}
	
	public function primaryKey() {
		return array('addresslist_id','company_id');
	}
	
	public function relations() {
	 return array(
			 'company' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Addressbook\Model\Company', 'field'=>'company_id'),
			 'addresslist' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Addressbook\Model\Addresslist', 'field'=>'addresslist_id'),
	 );
	}
	
	protected function afterSave($wasNew) {
		
		if(\GO::modules()->isInstalled('log')){
			\GO\Log\Model\Log::create($wasNew?\GO\Log\Model\Log::ACTION_ADD:\GO\Log\Model\Log::ACTION_UPDATE,  'Added '.$this->company->name.' to addresslist '.$this->addresslist->name, $this->className(),$this->company_id.':'.$this->addresslist_id);
		}
		
		return parent::afterSave($wasNew);
	}
	
	protected function afterDelete() {
		
		if(\GO::modules()->isInstalled('log')){
			\GO\Log\Model\Log::create(\GO\Log\Model\Log::ACTION_DELETE,  'Removed '.$this->company->name.' from addresslist '.$this->addresslist->name, $this->className(),$this->company_id.':'.$this->addresslist_id);
		}
		
		return parent::afterDelete();
	}
	
}