<?php

/**
 * Group-Office
 * 
 * Copyright Intermesh BV. 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @package GO.modules.postfixadmin.model
 * @version $Id: example.php 7607 20120101Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */
 
/**
 * The Domain model
 *
 * @package GO.modules.postfixadmin.model
 * @property int $user_id
 * @property string $domain
 * @property string $description
 * @property int $max_aliases
 * @property int $max_mailboxes
 * @property int $total_quota
 * @property int $default_quota
 * @property string $transport
 * @property boolean $backupmx
 * @property int $ctime
 * @property int $mtime
 * @property boolean $active
 * @property int $acl_id
 */


namespace GO\Postfixadmin\Model;


class Domain extends \GO\Base\Db\ActiveRecord {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Domain 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function aclField() {
		return 'acl_id';
	}

	public function tableName() {
		return 'pa_domains';
	}
	

	public function relations() {
		return array(
			'mailboxes' => array('type' => self::HAS_MANY, 'model' => 'GO\Postfixadmin\Model\Mailbox', 'field' => 'domain_id', 'delete' => true),
			'aliases' => array('type' => self::HAS_MANY, 'model' => 'GO\Postfixadmin\Model\Alias', 'field' => 'domain_id', 'delete' => true)
		);
	}
	
	public function getLogMessage($action) {		
		return $this->domain;
	}
	
	public function defaultAttributes() {
		$attr = parent::defaultAttributes();
		$attr['total_quota']=1024*1024*10;//10 GB of quota per domain by default.
		$attr['default_quota']=1024*512; //512 MB of default quota
		return $attr;
	}
	
	protected function init() {
		$this->columns['domain']['unique']=true;
		$this->columns['total_quota']['gotype']='number';
		$this->columns['default_quota']['gotype']='number';
		$this->columns['max_aliases']['gotype']='number';
		$this->columns['max_mailboxes']['gotype']='number';
		return parent::init();
	}
		
	/**
	 * @return Int The sum of the current domain's mailbox quotas.
	 */
	public function getSumUsedQuota() {
		$activeRecord = Mailbox::model()->find(
			\GO\Base\Db\FindParams::newInstance()
				->single()
				->select('SUM(`quota`) AS sum_used_quota')
				->criteria(
					\GO\Base\Db\FindCriteria::newInstance()
						->addCondition('domain_id',$this->id)
				)
		);
		return isset($activeRecord->sum_used_quota) ? $activeRecord->sum_used_quota : 0;
	}
	
	public function getSumMailboxes() {
		
		$record = Mailbox::model()->find(
			\GO\Base\Db\FindParams::newInstance()
				->single()
				->select('COUNT(*) AS count')
				->criteria(
					\GO\Base\Db\FindCriteria::newInstance()
						->addCondition('domain_id',$this->id)
				)
		);
		return $record->count;
	}
	
	public function getSumAliases() {
		$record = Alias::model()->find(
			\GO\Base\Db\FindParams::newInstance()
				->single()
				->select('COUNT(*) AS count')
				->criteria(
					\GO\Base\Db\FindCriteria::newInstance()
						->addCondition('domain_id',$this->id)
				)
		);
		return $record->count;
	}
	
	public function export() {
		$data = $this->getAttributes('raw');
		unset($data['id'], $data['acl_id']);
		
		$data['mailboxes'] = array();
		foreach($this->mailboxes as $mailbox) {
			$attr = $mailbox->getAttributes('raw');
			unset($attr['id'], $attr['domain_id']);
			$data['mailboxes'][] = $attr;
		}
		
		$data['aliases'] = array();
		foreach($this->aliases as $alias) {
			$attr = $alias->getAttributes('raw');
			unset($attr['id']);
			$data['aliases'][] = $attr;
		}
		
		return $data;
	}
	
	public function import($data) {
		$mailboxes = $data['mailboxes'];
		$aliases = $data['aliases'];
		
		unset($data['mailboxes']);
		unset($data['aliases']);
		
		$data['total_quota']=$data['max_mailboxes']=$data['max_aliases']=0;
		
		$this->setAttributes($data, false);
		
		if(!$this->save()){
			throw new \Exception("couldnt save domain");
		}
		
		foreach($mailboxes as $mailboxAttr){
			$mailbox = new Mailbox();			
			$mailbox->setAttributes($mailboxAttr, false);
			$mailbox->domain_id = $this->id;
			$mailbox->skipPasswordEncryption = true;
			if(!$mailbox->save()) {
				echo "Failed to save mailbox: ".var_export($mailbox->getValidationErrors(), true)."\n\n";				
			}
		}
		
		
		foreach($aliases as $aliasAttr){
			$alias = new Alias();
			$alias->setAttributes($aliasAttr, false);
			$alias->domain_id = $this->id;
			
			
			if(!$alias->save()) {
				echo "Failed to save alias: ".var_export($alias->getValidationErrors(), true)."\n\n";				
			}
		}
		
		return true;
	}

}
