<?php
/**
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @package GO.modules.emailportlet
 * @version $Id: \GO\emailportlet\Model\EmailPortletFolder.php 7607 2012-08-24 12:05:55Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */
 
/**
 * The \GO\emailportlet\Model\EmailPortletFolder model
 *
 * @package GO.modules.emailportlet
 * @version $Id: \GO\emailportlet\Model\EmailPortletFolder.php 7607 2012-08-24 12:05:55Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 *
 * @property int $account_id
 * @property string $folder_name
 * @property int $user_id
 * @property int $mtime
 * 
 * @property string $name
 */


namespace GO\email\Model;


class PortletFolder extends \GO\Base\Db\ActiveRecord{	
	
	public $name = 'UNDEFINED';
	
	private $_imapMailbox;
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return \GO\emailportlet\Model\EmailPortletFolder
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	protected function init() {
		$this->_load();
		return parent::init();
	}
	
	public function primaryKey() {
		return array('account_id','folder_name','user_id');
	}
		
	private function _load(){
		
		if(!empty($this->account))
			$this->_imapMailbox = new \GO\Email\Model\ImapMailbox($this->account,array('name'=>$this->folder_name));
		
		if(!empty($this->_imapMailbox))
			$this->name =  $this->_imapMailbox->getDisplayName();
	}
	
	/**
	 * Enable this function if you want this model to check the acl's automatically.
	 */
	// public function aclField(){
	//	 return 'acl_id';	
	// }

	/**
	 * Returns the table name
	 */
	 public function tableName() {
		 return 'em_portlet_folders';
	 }

	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 */
	 public function relations() {
		 return array(
				 'account' => array('type' => self::BELONGS_TO, 'model' => 'GO\Email\Model\Account', 'field' => 'account_id')
		 );
	 }
	 
	 
	 
}
