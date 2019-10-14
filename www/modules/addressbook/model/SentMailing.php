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
 * @property int $errors
 * @property int $sent
 * @property int $total
 * @property int $status
 * @property int $alias_id
 * @property int $addresslist_id
 * @property int $ctime
 * @property string $message_path
 * @property string $subject
 * @property int $user_id
 * @property int $id
 * @property int $opened
 * @property int $campaign_id
 * @property int $campaign_status_id
 * @property string $temp_pass The temporary password for sending newsletters
 * 
 * @property \GO\Base\Fs\File $logFile
 * @property \GO\Base\Fs\File $messageFile
 */

namespace GO\Addressbook\Model;


class SentMailing extends \GO\Base\Db\ActiveRecord {
	const STATUS_RUNNING=1;
	const STATUS_FINISHED=2;
	const STATUS_PAUSED=3;
	const STATUS_WAIT_PAUSED=4;

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Addressbook 
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'ab_sent_mailings';
	}
	
	public function relations() {
		return array(
				'addresslist' => array('type' => self::BELONGS_TO, 'model' => 'GO\Addressbook\Model\Addresslist', 'field' => 'addresslist_id'),
				'campaign' => array('type' => self::BELONGS_TO, 'model' => 'GO\Campaigns\Model\Campaign', 'field' => 'campaign_id'),
				'contacts' => array('type'=>self::MANY_MANY, 'model'=>'GO\Addressbook\Model\Contact', 'field'=>'sent_mailing_id', 'linkModel' => 'GO\Addressbook\Model\SentMailingContact'),
				'companies' => array('type'=>self::MANY_MANY, 'model'=>'GO\Addressbook\Model\Company', 'field'=>'sent_mailing_id', 'linkModel' => 'GO\Addressbook\Model\SentMailingCompany')
		);
	}
	
	public function aclField(){
		return 'addresslist.acl_id';	
	}
	
	protected function afterSave($wasNew) {
		
		$campaignModel = $this->campaign;
		if (!empty($campaignModel)) {
			$sentAdd = $this->isModified('sent') ? $this->sent - $this->getOldAttributeValue('sent') : 0;
			$errorAdd = $this->isModified('errors') ? $this->errors - $this->getOldAttributeValue('errors') : 0;
			$totalAdd = $this->isModified('total') ? $this->total - $this->getOldAttributeValue('total') : 0;
			$openedAdd = $this->isModified('opened') ? $this->opened - $this->getOldAttributeValue('opened') : 0;
			if ($sentAdd!=0 || $errorAdd!=0 || $totalAdd!=0 || $openedAdd!=0) {
				$campaignModel->n_sent += $sentAdd;
				$campaignModel->n_send_errors += $errorAdd;
				$campaignModel->n_total += $totalAdd;
				$campaignModel->n_opened += $openedAdd;
				$campaignModel->save();
			}
		}
		
		return parent::afterSave($wasNew);
	}
	
	/**
	 * Clears or initializes the sending status of the mailing.
	 */
	public function reset() {
		$nMailsToSend = 0;

		// Clear list of company recipients to send to, if there are any in this list
		$this->removeAllManyMany('companies');

		// Add company recipients to this list and count them
		$stmt = Addresslist::model()->findByPk($this->addresslist_id)->companies();
		while ($company = $stmt->fetch()) {
			$this->addManyMany('companies', $company->id);			
			$nMailsToSend++;			
		}

		// Clear list of contact recipients to send to, if there are any in this list
		$this->removeAllManyMany('contacts');

		// Add contact recipients to this list and count them
		$stmt = Addresslist::model()->findByPk($this->addresslist_id)->contacts();
		while ($contact = $stmt->fetch()) {
			$this->addManyMany('contacts', $contact->id);			
			$nMailsToSend++;			
		}

		$this->setAttributes(
						array(
								"status" => self::STATUS_RUNNING,
								"total" => $nMailsToSend
						)
		);
		$this->save();
	}
	
	protected function getLogFile(){
		$file = new \GO\Base\Fs\File(\GO::config()->file_storage_path.'log/mailings/'.$this->id.'.log');		
		return $file;
	}
	
	protected function getMessageFile(){
		$file = new \GO\Base\Fs\File(\GO::config()->file_storage_path.$this->message_path);		
		return $file;
	}
	
	protected function beforeDelete() {
		if($this->status==self::STATUS_RUNNING)
			throw new \Exception("Can't delete a running mailing. Pause it first.");		
		return parent::beforeDelete();
	}
	
	protected function afterDelete() {
		
		$this->logFile->delete();
		$this->messageFile->delete();
		
		$this->removeAllManyMany('contacts');
		$this->removeAllManyMany('companies');
		
		$campaignModel = $this->campaign;
		if (!empty($campaignModel)) {
			$campaignModel->n_sent -= $this->sent;
			$campaignModel->n_send_errors -= $this->errors;
			$campaignModel->n_total -= $this->total;
			$campaignModel->n_opened -= $this->opened;
			$campaignModel->save();
		}
		
		return parent::afterDelete();
	}

}
