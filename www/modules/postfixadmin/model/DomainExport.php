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
 * @version $Id: DomainExport.php 7607 20120101Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 */
 
/**
 * The DomainExport model
 *
 * @package GO.modules.postfixadmin.model
 * @property string $domain
 */


namespace GO\Postfixadmin\Model;

use GO;
use GO\Base\Fs\File;
use GO\Base\Model;
use GO\Base\Util\Http;


class DomainExport extends Model {
	
	public $resetPasswords = false;
	public $remoteModelId = null;
	public $domain = '';
	
	public $columns = array(
		'username' => 'Username',
		'email' => 'Email',
		'name' => 'Name',
		'password' => 'Password',
		'active' => 'Active'
	);
	
	private $_fp;
	
	/**
	 * Get all mailboxes of the current domain
	 * 
	 * @return static ActiveStatement
	 */
	private function _getMailboxes(){
		return Mailbox::model()->findByAttribute('domain_id', $this->remoteModelId);
	}

	/**
	 * 
	 * @param Mailbox $mailbox
	 * @return type
	 */
	private function _prepareRecord($mailbox){
		
		$c = array_keys($this->columns);
		$frecord = array();
		
		$newPass = '';
		
		if(!empty($this->resetPasswords)){
			//Reset the password of this mailbox
			$newPass = \GO\Base\Util\StringHelper::randomPassword(8);
			$mailbox->password = $newPass;
			if(!$mailbox->save()){
				$newPass = 'Error while creating new password';
			}
		}
		
		foreach($c as $key){
			
			if($key == 'password'){
				$frecord[$key] = $newPass; // Apply new password
			} else if($key == 'active'){
				$frecord[$key] = $mailbox->getAttribute($key)==1?'yes':'no'; // print yes or no
			} else if($key == 'email'){
				$frecord[$key] = $mailbox->getAttribute('username'); // The email address is the same as the username
			}else {
				$frecord[$key] = $mailbox->getAttribute($key);
			}
		}

		return $frecord;
	}
	
	
	public function download(){
		
		// Remove the password field from the columns
		if(empty($this->resetPasswords) && isset($this->columns['password'])){
			unset($this->columns['password']);
		}
		
		// Send the download headers
		$this->_sendHeaders();
		
		// Write the headers
		$this->_write(array_keys($this->columns));
		
		//	 Query all accounts
		$mailboxes = $this->_getMailboxes();
		
		// Loop through the mailboxes and write the export line
		while($mailbox = $mailboxes->fetch()){
			$record = $this->_prepareRecord($mailbox);
			$this->_write($record);
		}
	}
	
	/**
	 * Send the output headers
	 */
	private function _sendHeaders(){	
		if(!isset($this->_fp)){
			$file = new File($this->domain.'.csv');
			Http::outputDownloadHeaders($file);
		}
	}
	
	/**
	 * Write the data to the csv file
	 * 
	 * @param array $data
	 */
	private function _write($data){
		if(!isset($this->_fp)){
			$this->_fp=fopen('php://output','w+');		
		}		
		fputcsv($this->_fp, $data, GO::user()->list_separator, GO::user()->text_separator);
	}	
}