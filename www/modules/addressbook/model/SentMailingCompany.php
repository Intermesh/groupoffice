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
 * @property int $sent_mailing_id
 */


namespace GO\Addressbook\Model;


class SentMailingCompany extends \GO\Base\Db\ActiveRecord {
	
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
		return 'ab_sent_mailing_companies';
	}
	
	public function primaryKey() {
		return array('sent_mailing_id','company_id');
	}
	
	public function relations() {
		return array(
				'sentMailing' => array('type' => self::BELONGS_TO, 'model' => 'GO\Addressbook\Model\SentMailing', 'field' => 'sent_mailing_id')
		);
	}
	
	protected function afterSave($wasNew) {
		
		$sentMailingModel = $this->sentMailing;
		if (!empty($sentMailingModel)) {
			
			$sentNow = $this->sent ? 1 : 0;
			if ($this->isModified('sent'))
				$sentBefore = $this->getOldAttributeValue('sent') ? 1 : 0;
			else
				$sentBefore = $sentNow;
			$sentAdd = $sentNow - $sentBefore;
			
			$errorNow = $this->has_error ? 1 : 0;
			if ($this->isModified('has_error'))
				$errorBefore = $this->getOldAttributeValue('has_error') ? 1 : 0;
			else
				$errorBefore = $errorNow;
			$errorsAdd = $errorNow - $errorBefore;
			
			$openedNow = $this->campaigns_opened ? 1 : 0;
			if ($this->isModified('campaigns_opened'))
				$openedBefore = $this->getOldAttributeValue('campaigns_opened') ? 1 : 0;
			else
				$openedBefore = $openedNow;
			$openedAdd = $openedNow - $openedBefore;
			
			if ($sentAdd!=0 || $errorsAdd!=0 || $openedAdd!=0) {
				
//				var_dump($this->contact_id.' , '.$this->sent_mailing_id);
//				var_dump($sentNow);
//			var_dump($sentBefore);
//			exit();
				
				$sentMailingModel->sent += $sentAdd;
				$sentMailingModel->errors += $errorsAdd;
				$sentMailingModel->opened += $openedAdd;
				$sentMailingModel->save();
			}
		}
		
		return parent::afterSave($wasNew);
	}
	
	protected function afterDelete() {
		
		$sentMailingModel = $this->sentMailing;
		if (!empty($sentMailingModel)) {			
			$sentMailingModel->sent -= $this->sent ? 1 : 0;
			$sentMailingModel->errors -= $this->has_error ? 1 : 0;
			$sentMailingModel->total -= 1;
			$sentMailingModel->opened -= $this->campaigns_opened ? 1 : 0;
			$sentMailingModel->save();
		}
		
	}
	
}