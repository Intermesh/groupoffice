<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: CronJob.php 7607 2011-06-15 09:17:42Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

/**
 * The CronJob model
 * 
 * @property int $id
 * @property string $name
 * @property int $active
 * @property int $runonce
 * @property string $minutes
 * @property string $hours
 * @property string $monthdays
 * @property string $months
 * @property string $weekdays
 * @property string $years
 * @property string $job
 * @property string $params
 * @property int $nextrun // timestamp of the next run
 * @property int $lastrun // timestamp of the latest run
 * @property int $completedat // timestamp of the latest run
 * 
 */

namespace GO\Base\Cron;

use GO;
use GO\Base\Db\PDO;

use Exception;


class CronJob extends \GO\Base\Db\ActiveRecord {
		
	public function getParamsToSet() {
		return $this->_jsonToParams($this->params);
	} 
	
	
	public function setAttributes($attributes, $format = null) {
		
		$publicProperties = $this->_getAdditionalJobProperties();
		
		$propArray = array();
		foreach($publicProperties as $property){			
			if(key_exists($property['name'],$attributes)) {
				$propArray[$property['name']] = $attributes[$property['name']];
			}
		}
		
		$this->params = json_encode($propArray);
		
		return parent::setAttributes($attributes, $format);
	}
	
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return \GO\Notes\Model\Note 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function isRunning(){
		if($this->lastrun > 0 && $this->completedat == 0)
			return true;
//		if($this->completedat == 0)
//			return false;
		else
			return $this->lastrun > $this->completedat;
	}
	
	protected function init() {
		$this->columns['name']['unique']=true;
		$this->columns['nextrun']['gotype']='unixtimestamp';
		$this->columns['lastrun']['gotype']='unixtimestamp';
		$this->columns['completedat']['gotype']='unixtimestamp';
		return parent::init();
	}
	
	public function tableName(){
		return 'go_cron';
	}
	
	public function primaryKey() {
		return 'id';
	}
	
	public function relations() {
		return array(
				'users' => array('type'=>self::MANY_MANY, 'model'=>'GO\Base\Model\User', 'field'=>'cronjob_id', 'linkModel' => 'GO\Base\Cron\CronUser'),
				'groups' => array('type'=>self::MANY_MANY, 'model'=>'GO\Base\Model\Group', 'field'=>'cronjob_id', 'linkModel' => 'GO\Base\Cron\CronGroup'),
		);
	}
	
	/**
	 * TODO: IMPLEMENT AND RETURN THE STATEMENT
	 * @return \GO\Base\Db\ActiveStatement $stmnt
	 */
	public function getAllUsers(){
		
		$id = $this->id;
		
		$query = "SELECT * FROM `core_user` as `t`
							WHERE `id` IN (
								SELECT `id` FROM `go_cron_users` cu 
								WHERE user_id=`t`.`id` AND `cu`.`cronjob_id`=:cronjob_id1
							)
							OR `id` IN (
								SELECT `ug`.`userId` FROM `go_cron_groups` cg 
								INNER JOIN `core_user_group` ug ON `ug`.`groupId`=`cg`.`group_id`
								WHERE `cg`.`cronjob_id`=:cronjob_id2
							);";
		$stmnt = GO::getDbConnection()->prepare($query);
		$stmnt->bindParam("cronjob_id1", $id, PDO::PARAM_INT);
		$stmnt->bindParam("cronjob_id2", $id, PDO::PARAM_INT);
		$stmnt->execute();

		$stmnt->setFetchMode(PDO::FETCH_CLASS, "GO\Base\Model\User",array(false));
		
		return $stmnt;
	}
	
	
	/**
	 * Validate the inputfields
	 * 
	 * @return boolean
	 */
	public function validate() {
		
		if(!$this->_validateExpression('minutes'))
			$this->setValidationError('minutes', GO::t("Minutes does not match the required format.", "cron"));
		
		if(!$this->_validateExpression('hours'))
			$this->setValidationError('hours', GO::t("Hours does not match the required format.", "cron"));
		
		if(!$this->_validateExpression('monthdays'))
			$this->setValidationError('monthdays', GO::t("Monthdays does not match the required format.", "cron"));
		
		if(!$this->_validateExpression('months'))
			$this->setValidationError('months', GO::t("Months does not match the required format.", "cron"));
		
		if(!$this->_validateExpression('weekdays'))
			$this->setValidationError('weekdays', GO::t("Weekdays does not match the required format.", "cron"));
		
		if(!$this->_validateExpression('years'))
			$this->setValidationError('years', GO::t("Years does not match the required format.", "cron"));
		
		if($this->hasValidationErrors())
			$this->setValidationError('active', '<br /><br />'.$this->_getExampleFormats());

		return parent::validate();
	}
	
	/**
	 * Function for creating the pattern for checking the correct values
	 * 
	 *		*
	 *		0,10
	 *		* /5
	 *		1,3,5
	 *		1-5
	 * 
	 * 
	 * @var StringHelper $field
	 * @return StringHelper The regular expression
	 */
	private function _getValidationRegex($field){
		$regex = '/';
		switch($field){
			case 'minutes':
				$regex .= '([0-6][0-9]?[- ]?|\*)*,*';
				break;
			case 'hours':
				$regex .= '([0-2][0-9]?[- ]?|\*)*,*';
				break;
			case 'monthdays':
				$regex .= '([1-3][0-9]?[- ]?|\*)*,*';
				break;
			case 'months':
				$regex .= '([1-9][0-9]?[- ]?|\*)*,*';
				break;
			case 'weekdays':
				$regex .= '([0-6][- ]?|\*)*,*';
				break;
			case 'years':
				$regex .= '(([1-9][0-9]{3}[- ]?|\*)*),*';
				break;
		}
		$regex .= '/';
		
		return $regex;
	}
		
	private function _validateExpression($field){
			
		if($this->{$field} == '')
			return false;
		
		return preg_match($this->_getValidationRegex($field), $this->{$field});
	}
	
	private function _getExampleFormats(){
		return GO::t("Please use one of these formats (eg. hour, no spaces):", "cron").
			'<table>'.
				'<tr><td>*</td><td>'.GO::t("(all)", "cron").'</td></tr>'.
				'<tr><td>1</td><td>'.GO::t("(only the first)", "cron").'</td></tr>'.
				'<tr><td>1-5</td><td>'.GO::t("(All between 1 and 5)", "cron").'</td></tr>'.
				'<tr><td>0-23/2</td><td>'.GO::t("(Every 2nd between 0 and 23)", "cron").'</td></tr>'.
				'<tr><td>1,2,3,13,22</td><td>'.GO::t("(Only on the given numbers)", "cron").'</td></tr>'.
				'<tr><td>0-4,8-12</td><td>'.GO::t("(Between 0 and 4 and between 8 and 12)", "cron").'</td></tr>'.
			'<table>';
	}
	
	/**
	 * Build the cron expresssion from the attributes of this model 
	 * 
	 * *    *    *    *    *		 *
   * -    -    -    -    -    -
   * |    |    |    |    |    |
   * |    |    |    |    |    + year [optional]
   * |    |    |    |    +----- day of week (0 - 7) (Sunday=0 or 7)
   * |    |    |    +---------- month (1 - 12)
   * |    |    +--------------- day of month (1 - 31)
   * |    +-------------------- hour (0 - 23)
   * +------------------------- min (0 - 59)
	 *	
	 * 
	 * @return StringHelper The complete expression 
	 */
	public function _buildExpression(){
		$expression = '';
	
		$expression .= $this->minutes;
		$expression .= ' ';
		$expression .= $this->hours;
		$expression .= ' ';
		$expression .= $this->monthdays;
		$expression .= ' ';
		$expression .= $this->months;
		$expression .= ' ';
		$expression .= $this->weekdays;
		$expression .= ' ';
		$expression .= $this->years;
	
		return $expression;
	}
	
	public function formatInput($column, $value) {
		$value=trim($value);
		return parent::formatInput($column, $value);
	}
	
	/**
	 * Function to calculate the next running time for this cronjob
	 * 
	 * @return int The next run time (timestamp)
	 */
	private function _calculateNextRun(){
		$completeExpression = new \GO\Base\Util\Cron($this->_buildExpression());
		return $completeExpression->getNextRunDate()->getTimestamp();
	}
	
	public function run(){
		GO::session()->runAsRoot();
		GO::debug('CRONJOB ('.$this->name.') START : '.date('d-m-Y H:i:s'));
		
		if($this->_prepareRun()){

			$failed = false;
			try {

				//check if module is available
				$parts = explode('\\', $this->job);
				$moduleId = strtolower($parts[1]);
				
				if ($moduleId != 'base' && !GO::modules()->isInstalled($moduleId)) {

					$this->error ='Aborted because module ' . $moduleId . ' is not installed';
					$this->save();

					return false;
				}

				if ($moduleId != 'base' && !GO::modules()->isAvailable($moduleId)) {
					$msg = 'Aborted because module ' . $moduleId . ' is not available';
					
					$ioncubeInstalled = extension_loaded('ionCube Loader');
					
					if(!$ioncubeInstalled) {
						$msg .= 'Ioncube is NOT installed on the CLI interface. This might be a problem if this is a professional module.';
					}

					$this->error = $msg;
					$this->save();

					return false;
				}

				if (!class_exists($this->job)) {
					$this->error = 'Aborted because cron job class file is missing';
					$this->save();

					return false;
				}
				
				// Run the specified cron file code
				$cronFile = new $this->job;
				
				
				// set param on the job
				$paramsToSet = $this->getParamsToSet();
				if(count($paramsToSet)) {
					foreach ($paramsToSet as $key => $value) {
						$cronFile->{$key} = $value;
					}
				}
				
				$cronFile->run($this);
				$this->error=null;	
			}catch(\Throwable $e){
				GO::debug("EXCEPTION: ".(string) $e);
				$failed=true;
				
				\GO\Base\Mail\AdminNotifier::sendMail("CronJob ".$this->name." failed", "EXCEPTION: ".(string) $e);
				// if we trigger and error again here the cron wont be saved
				//trigger_error("CronJob ".$this->name." failed. EXCEPTION: ".(string) $e, E_USER_WARNING);
				\go\core\ErrorHandler::logException($e);
			
				$this->error = date('c') . ": " .(string)$e;

			}
			
			$this->_finishRun($failed);
	
			return true;
		} else {
			GO::debug('CRONJOB ('.$this->name.') FAILED TO RUN : '.date('d-m-Y H:i:s'));
			return false;
		}
	}
	
	
	protected function beforeSave() {
		
		//$this->params = $this->_paramsToJson();
		
		$this->nextrun = $this->_calculateNextRun();
		
		//if the cron happens within a minute then substract one minute for immediate testing.
//		if(GO::config()->debug && PHP_SAPI!='cli'){
//			if($this->nextrun<time()+61){
//				$this->nextrun-=60;
//			}
//		}
		// GO::debug('CRONJOB ('.$this->name.') NEXTRUN : '.date('c', $this->getAttribute('nextrun'));
		return parent::beforeSave();
	}
	
	private function _getAdditionalJobProperties(){
		if(empty($this->job) || !class_exists($this->job)) {
			return array();
		}
		
		$returnProperties = array();
		
		$jobReflection = new \ReflectionClass($this->job);
		$parentReflection = $jobReflection->getParentClass();

		$jobProperties = $jobReflection->getProperties(\ReflectionProperty::IS_PUBLIC);
		$parentProperties = $parentReflection->getProperties(\ReflectionProperty::IS_PUBLIC);
		
		$publicProperties = array_diff($jobProperties, $parentProperties);
		
		$defaultProperties = $jobReflection->getDefaultProperties();

		foreach($publicProperties as $property){
	
			$returnProperties[] = array(
				'name'=>$property->name,
				'defaultValue'=>$defaultProperties[$property->name]
			);
		}

		return $returnProperties;
	}
	
	/**
	 * Convert a Json string to PUBLIC parameters of this object
	 * ($this->params)
	 * 
	 * @param String $jsonString
	 */
	private function _jsonToParams($jsonString = ''){
		
		$propArray = array();
		$jsonProperties = json_decode($jsonString, true);
		$publicProperties = $this->_getAdditionalJobProperties();
 
		foreach($publicProperties as $property){
			$propArray[$property['name']] = '';
			if(!empty($jsonProperties[$property['name']])){
				$propArray[$property['name']] = $jsonProperties[$property['name']];
			} else {
				if(!empty($property['defaultValue']))
					$propArray[$property['name']] = $property['defaultValue'];
			}
		}
		
		return $propArray;
	}
	
	/**
	 * This function needs to be called at the START of the run of this cronjob.
	 * It calculates the next run time and sets the last run time.
	 * 
	 * @return boolean
	 */
	private function _prepareRun() {
		
		$this->active = false;
		
		$this->lastrun = time();
		return $this->save();
	}
	
	private function _finishRun($failed=false){
		
		if($this->autodestroy){
			return $this->delete();
		}
		
		if(!$this->runonce){
			$this->active = true;
			if($failed){
				GO::debug('CRONJOB ('.$this->name.') FAILED');
			}
		} else {
			GO::debug('CRONJOB ('.$this->name.') HAS RUNONCE OPTION, DISABLING NOW');
		}
		
		$this->completedat = time();
		$this->save();
			
		GO::debug('CRONJOB ('.$this->name.') FINISHED : '.date('d-m-Y H:i:s'));
	}
	
}
