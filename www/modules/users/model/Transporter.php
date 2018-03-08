<?php

namespace GO\Users\Model;

use GO;
use GO\Email\Model\Account;
use GO\Addressbook\Model\Addressbook;
use GO\Projects2\Model\Project as Project2;
use GO\Projects\Model\Project;
use GO\Calendar\Model\Calendar;
use GO\Base\Model\Acl;

/* 
 * This class will transport data from 1 user account to another
 * only need from and to ID from both accounts
 * 
 * 1. Calendars
 * 2. Address books
 * 3. Projects
 * 4. E-mail accounts
 */
class Transporter {
	
	protected $from;
	protected $id;
	
	
	public function __construct($from_id, $to_id) {
		$this->from = $from_id;
		$this->to = $to_id;
	}
	
	public function sync() {
		$success = true;
		
		GO::$db->beginTransaction();
		
		try{
		
			$success = $this->calendars() && $success;
			$success = $this->addressbooks() && $success;
			$success = $this->projects() && $success;
			$success = $this->emailAccounts() && $success;
			$success = $this->acls() && $success;

			GO::$db->commit();
		
		} catch (PDOException $e) {
			GO::$db->rollBack();
			return false;
		}
		return $success;
	}
	
	
	private function calendars() {
		$calendars = Calendar::model()->findByAttributes(array('user_id'=>$this->from));
		$success = true;
		foreach($calendars as $calendar) {
			$calendar->user_id = $this->to;
			$success = $calendar->save() && $success;
		}
		return $success;
	}
	
	private function addressbooks() {
		$addressbook = AddressBook::model()->findByAttributes(array('user_id'=>$this->from));
		$success = true;
		foreach($addressbook as $item) {
			$item->user_id = $this->to;
			$success = $item->save() && $success;
		}
		return $success;
	}
	
	private function projects() {
		if(GO::modules()->isInstalled('projects2')) {
			$class=Project2::className();
		} elseif(GO::modules()->isInstalled('projects')) {
			$class=Project::className();
		} else {
			return true;
		}
		$projects = GO::getModel($class)->findByAttributes(array('user_id'=>$this->from));
		$success = true;
		foreach($projects as $item) {
			$item->user_id = $this->to;
			$success = $item->save() && $success;
		}
		return $success;
	}
	
	private function emailAccounts() {
		$accounts = Account::model()->findByAttributes(array('user_id'=>$this->from));
		$success = true;
		foreach($accounts as $item) {
			$item->user_id = $this->to;
			$success = $item->save() && $success;
		}
		return $success;
	}
	
	private function acls() {
		$acls = Acl::model()->findByAttributes(array('user_id'=>$this->from));
		$success = true;
		foreach($acls as $item) {
			$item->user_id = $this->to;
			$success = $item->save() && $success;
		}
		return $success;
	}
}