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
 * @copyright Copyright Intermesh BV
 * @version $Id InstallationUser.php 2012-09-03 09:34:14 mdhart $
 * @author Michael de Hart <mdehart@intermesh.nl> 
 * @package GO.servermanager,model
 */
/**
 * Activerecord for every user per installations database
 *
 * @package GO.servermanager.model
 * @copyright Copyright Intermesh
 * @version $Id InstallationUser.php 2012-09-03 09:34:14 mdhart $ 
 * @author Michael de Hart <mdehart@intermesh.nl> 
 *
 * @property int $user_id
 * @property int $installation_id
 * @property string $username
 * @property string $used_modules
 * @property int $ctime
 * @property int $lastlogin
 * @property boolean $enabled
 * 
 * @property Installation $installation the installation this module was installed for
 */


namespace GO\ServerManager\Model;


class InstallationUser extends \GO\Base\Db\ActiveRecord {

	public $modules;
	
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function primaryKey()
	{
		return array('user_id', 'installation_id');
	}
	
	protected function init()
	{
		$this->columns['lastlogin']['gotype'] = 'unixtimestamp';
		$this->modules = !empty($this->used_modules) ? explode(',', $this->used_modules) : array();
	}
	
	protected function beforeSave()
	{
		$this->used_modules = implode(',', $this->modules);
		return parent::beforeSave();
	}
	public function addModule($module_id)
	{
		$this->modules[] = $module_id;
	}
	public function setAttributesFromUser($user)
	{
		$this->user_id = $user->id;
		$this->lastlogin = $user->lastlogin;
		$this->enabled = $user->enabled;
		$this->username = $user->username;
		$this->email = $user->email;
		$this->ctime = $user->ctime;
	}
	
	public function allowedToModule($module_id)
	{
		return in_array($module_id, $this->modules);
	}
	
	public function relations()
	{
		return array(
				'installation'=>array('type'=>self::BELONGS_TO, 'model'=>'GO\ServerManager\Model\Installation', 'field'=>'installation_id'),
		);
	}

	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'sm_installation_users';
	}
	
	/**
	 * User is still in trail or does the client have to pay?
	 * @return boolean true when the user is still in trail period
	 */
	public function isTrial()
	{
		return $this->trialDaysLeft > 0;
	}
	
	/**
	 * @return int the amount of days the trial period has left.
	 */
	public function getTrialDaysLeft()
	{
		if(empty($this->ctime)) 
			return $this->installation->trial_days;
		$trial_end_stamp = \GO\Base\Util\Date::date_add($this->ctime, $this->installation->trial_days);
		
		$seconds_to_go = $trial_end_stamp - time();
		$days_to_go = $seconds_to_go / 60 / 60 / 24;
		
		$days_left = ($days_to_go > 0) ? ceil($days_to_go) : 0;
		
		return $days_left;
	}
	/**
	 * Send an email to the installations admin_email
	 * @return boolean true if mail was send correctly
	 */
	public function sendTrialTimeLeftMail()
	{
		if(!$this->isTrial())
			return true;
		
		$message = \GO\Base\Mail\Message::newInstance();
		$subject = vsprintf(\GO::t('user_trial_email_title','servermanager'),array($this->username));
		$message->setSubject($subject);
		
		$fromName = \GO::config()->product_name;
	
		$parts = explode('@', \GO::config()->webmaster_email);
		$fromEmail = 'noreply@'.$parts[1];
		
		$toEmail = $this->installation->config['webmaster_email'];

		$emailBody = \GO::t('user_trial_email_body','servermanager'); //TODO: add to translation
		$emailBody = vsprintf($emailBody,array($this->username, $this->trialDaysLeft));
		
		$message->setBody($emailBody);
		$message->addFrom($fromEmail,$fromName);
		$message->addTo($toEmail);
		
		return \GO\Base\Mail\Mailer::newGoInstance()->send($message);
	}
	

}
