<?php
namespace GO\Base\Model;

use Exception;
use GO;
use GO\Base\Util\Http;

/**
 * The Client model
 *
 * @version $Id: Group.php 7607 2011-08-04 13:41:42Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @package GO.base.model
 *
 * @property int $id
 * @property string $footprint (Unique)
 * @property int $user_id
 * @property string $ip
 * @property string $user_agent
 * @property int $ctime
 * @property int $last_active
 * @property Boolean $in_use

 * @method Client findByPk();
 */
class Client extends GO\Base\Db\ActiveRecord {
	
	public static $cookieName = 'GO_Unique_Client_Footprint';
	public static $cookieLifetime = (86400 * 7);
	
	public function tableName() {
		return 'go_clients';
	}

	public function relations() {
		return array(
			'user' => array('type' => self::BELONGS_TO, 'model' => 'GO\Base\Model\User', 'field' => 'user_id')
		);
	}
	
	protected function beforeSave() {
		
		if($this->getIsNew()){
			
			if(!empty($this->footprint)){
				throw new Exception('It\'s not allowed to set the footprint by yourself');
			}
			
			$this->ip = Http::getClientIp();
			$this->in_use = true;
			$this->last_active = time();
			
			$browser = Http::getBrowser();
			$this->user_agent = $browser['name'].' - '.$browser['version'].' - '.$browser['platform'];
			$this->footprint = $this->_buildFootprint();
			
		}
		
		return parent::beforeSave();
	}
	
	protected function afterSave($wasNew) {
		
		// Clear ALL old records when a new record is created
		if($wasNew){
			$this->_clearExpiredClientRecords();
		}
		
		return parent::afterSave($wasNew);
	}
	
	
	private function _buildFootprint(){
		return  md5($this->ip.time().GO::session()->id());
	}
	
	/**
	 * Lookup the client based on the footprint in the cookie
	 * 
	 * @param int $userId
	 * @return Client | false
	 */
	public static function lookup($userId){
		$client = false;
		
		$footprint = self::getFootPrintFromCookie();
		
		if(!empty($footprint)){
			$client = self::model()->findSingleByAttributes(array('footprint'=>$footprint,'user_id'=>$userId));
		}
		
		if(!$client){
			$client = new Client();
			$client->user_id = $userId;
			if($client->save()){
				$client->setFootPrintToCookie();
			} else {
				throw new \Exception('Could not save client model');
			}
		}
		
		return $client;
	}
	
	/**
	 * Lookup the client based on the user id
	 * 
	 * @param int $userId
	 * @return Client[] | false
	 */
	public static function lookupByUser($userId){
		return self::model()->findByAttribute('user_id', $userId);
	}
	
	/**
	 * 
	 * @param int $userId
	 * @param string $footprint
	 * @return Boolean
	 */
	public static function updateLastActive($userId,$footprint){
		$client = self::model()->findSingleByAttributes(array('user_id'=>$userId,'footprint'=>$footprint));
		$client->last_active = time();
		return $client->save();
	}
	
	public function setFootPrintToCookie(){
		if(!headers_sent()) {
			Http::setCookie(self::$cookieName, $this->footprint, self::$cookieLifetime);
		}
	}
	
	public static function getFootPrintFromCookie(){
		
		$cookie = Http::getCookie(self::$cookieName);
		
		return $cookie;
	}
	
	public static function removeFootPrintFromCookie(){
		if(!headers_sent()) {
			return Http::unsetCookie(self::$cookieName);
		}
	}
	
	
	public function checkLoggedInOnOtherLocation(){

		// Check if there is an other Client record that is in_use
		$otherInUseClient = self::model()->findSingleByAttributes(
			array('user_id'=>$this->user_id,'in_use'=>true), 
			GO\Base\Db\FindParams::newInstance()->criteria(
				GO\Base\Db\FindCriteria::newInstance()->addCondition('id', $this->id,'!=')
				)->order('last_active', 'DESC')
			);
		
		return $otherInUseClient;		
	}
	
	// Clear all records from go_clients table that are older than the cookie_lifetime
	private function _clearExpiredClientRecords(){
		
		$expiredTime = (time() - self::$cookieLifetime);
		
		\GO::debug('Client.php: Clear records older than the cookie lifetime. ('.$expiredTime.')');
		
		$sql = "DELETE FROM ".$this->tableName()." WHERE ctime < ".$expiredTime.";";
		\GO::getDbConnection()->query($sql);
	}
}
