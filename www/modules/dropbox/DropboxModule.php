<?php
namespace GO\Dropbox;

use GO;
use GO\Base\Module;
use GO\Dropbox\Model\Settings;

class DropboxModule extends Module {
	
	public function package() {
		return self::PACKAGE_UNSUPPORTED;
	}

	public static function initListeners() {
		GO::config()->addListener('init', 'GO\Dropbox\DropboxModule', 'registerAutoload');
		
		\GO\Base\Model\User::model()->addListener('delete', "GO\Dropbox\DropboxModule", "userDelete");
	}
	
	public function depends() {
		return array('files');
	}
	
	public function install() {
		parent::install();
		
		$cron = new \GO\Base\Cron\CronJob();
		
		$cron->name = 'Dropbox sync';
		$cron->active = true;
		$cron->runonce = false;
		$cron->minutes = '*';
		$cron->hours = '*';
		$cron->monthdays = '*';
		$cron->months = '*';
		$cron->weekdays = '*';
		$cron->job = 'GO\\Dropbox\\Cron\\Sync';

		$cron->save();
	}
	
	public function uninstall() {
		parent::uninstall();
		
		$cron = \GO\Base\Cron\CronJob::model()->findSingleByAttribute('job','GO\\Dropbox\\Cron\\Sync');
		
		if(!$cron) // No cronjob found, so nothing needs to be deleted.
			return true;
		
		if(!$cron->delete()) // Try to delete the cronjob, if it's not possible then throw an exception.
			Throw new \Exception('The Dropbox systemtask could not be deleted automatically. Please try to delete it manually in the "System tasks module."');
		else
			return true;
	}
	
	public static function userDelete(\GO\Base\Model\User $user){
		$dbxUser = Model\DropboxUser::model()->findByPk($user->id);
		
		if($dbxUser){
			$dbxUser->delete();
		}
	}
	
	public static function registerAutoload() {		
		require(dirname(__FILE__).'/vendor/autoload.php');
	}
	
	/**
	 * Get the Key of the dropbox app (Configured by the administrator)
	 * 
	 * @return string
	 * @throws Exception
	 */
	public static function getAppKey(){
		$settings = Settings::load();
		$key = $settings->app_key;
		
		if(empty($key)){
			Throw new Exception(GO::t('notConfiguredError','dropbox'));
		}
		
		return $key;
	}
	
	/**
	 * Get the Secret of the dropbox app (Configured by the administrator)
	 * 
	 * @return string
	 * @throws Exception
	 */
	public static function getAppSecret(){
		$settings = Settings::load();
		$secret = $settings->app_secret;
		
		if(empty($secret)){
			Throw new Exception(GO::t('notConfiguredError','dropbox'));
		}
		
		return $secret;
	}
	
	/**
	 * Get callback uri that needs to be set in the dropbox App
	 * 
	 * @return string
	 */
	public static function getCallbackUri(){
		$callbackuri = GO::url('dropbox/auth/callback', array(), false, false, false);
		return $callbackuri;
	}
	
	/**
	 * Get webhook uri that needs to be set in the dropbox App
	 * 
	 * @return string
	 */
	public static function getWebhookUri(){
		$webhookuri = GO::url('dropbox/auth/webhook', array(), false, false, false);
		return $webhookuri;
	}
	
}