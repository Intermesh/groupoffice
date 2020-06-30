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
 * @package GO.modules.log.model
 * @version $Id: example.php 7607 20120101Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */
 
/**
 * The Log model
 *
 * @package GO.modules.log.model
 * @property int $id
 * @property int $user_id
 * @property string $username
 * @property string $model_id
 * @property int $ctime
 * @property string $user_agent
 * @property string $ip
 * @property string $controller_route
 * @property string $action
 * @property string $message
 * @property string $jsonData
 */


namespace GO\Log\Model;
use GO;

class Log extends \GO\Base\Db\ActiveRecord {
	
	
	const ACTION_ADD='create';
	const ACTION_DELETE='delete';
	const ACTION_UPDATE='update';
	const ACTION_LOGIN='login';
	const ACTION_LOGOUT='logout';
	
	public $object;
	
//	protected $insertDelayed=true;
	
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
	
	public function tableName(){
		return 'go_log';
	}
	
	protected function init() {
		
		// set gotype to HTML because the jsondata will not be HTML encoded this way
		$this->columns['jsonData']['gotype'] = 'html';
		
		//$this->columns['time']='unixtimestamp';
		
		return parent::init();
	}
	
	public function validate() {
		
		$this->cutAttributeLengths();
			
		return parent::validate();
	}
	
	public function defaultAttributes() {
		$attr = parent::defaultAttributes();
		if(PHP_SAPI=='cli')
			$attr['user_agent']='cli';
		else
			$attr['user_agent']= isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
		$attr['ip']=isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
		$attr['controller_route']=\GO::router()->getControllerRoute();
		$attr['username']=\GO::user() ? \GO::user()->username : 'notloggedin';
		return $attr;
	}

	protected function afterSave($wasNew) {
		if(!isset(GO::config()->file_log) || !is_array(GO::config()->file_log))
			return true;
		
		if(isset(GO::config()->file_log[$this->model])) {
			
			file_put_contents(GO::config()->file_storage_path.'log/'.GO::config()->file_log[$this->model], 
					"[".$this->object->className().' '.date('Y-m-d H:i',$this->ctime)."] [".$this->username."] [".$this->action."] ".$this->message."\n",
					FILE_APPEND);

		}
		return true;
	}

	/**
	 * Log a custom message
	 * 
	 * @param string $action eg. update, save, delete
	 * @param string $message
	 * @param string $model_name
	 * @param int $model_id
	 * @param string/array $data
	 */
	public static function create($action, $message, $model_name="", $model_id=0, $data=""){

		// jsonData field in go_log might not exist yet during upgrade
		if(\GO::router()->getControllerRoute() == 'maintenance/upgrade') {
			return true;
		}
		// Check if the given data is already JSON, if not, then we json_encode it.
		if(!GO\Base\Util\StringHelper::isJSON($data)){
			$data = json_encode($data);
		}
		
		$log = new Log();
		$log->model_id=$model_id;
		$log->action=$action;
		$log->model=$model_name;			
		$log->message = $message;
		$log->jsonData = $data;
		$log->save();
	}
}
