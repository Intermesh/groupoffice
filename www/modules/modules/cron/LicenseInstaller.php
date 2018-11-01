<?php


namespace GO\Modules\Cron;

use GO;
use GO\Base\Cron\AbstractCron;
use GO\Base\Cron\CronJob;
use GO\Base\Model\User;


class LicenseInstaller extends AbstractCron {
	
	/**
	 * Return true or false to enable the selection fo users and groups for 
	 * this cronjob.
	 * 
	 * CAUTION: This will give the run() function a different behaviour. 
	 *					Please see the documentation of the run() function 
	 *					to see what is different.
	 */
	public function enableUserAndGroupSupport(){
		return false;
	}
	
	/**
	 * Get the unique name of the Cronjob
	 * 
	 * @return String
	 */
	public function getLabel(){
		return "License installer";
	}
	
	/**
	 * Get the unique name of the Cronjob
	 * 
	 * @return String
	 */
	public function getDescription(){
		return "Checks for new licenses uploaded by the user";
	}
	
	/**
	 * The code that needs to be called when the cron is running
	 * 
	 * If $this->enableUserAndGroupSupport() returns TRUE then the run function 
	 * will be called for each $user. (The $user parameter will be given)
	 * 
	 * If $this->enableUserAndGroupSupport() returns FALSE then the 
	 * $user parameter is null and the run function will be called only once.
	 * 
	 * @param CronJob $cronJob
	 */
	public function run(CronJob $cronJob){
		
		GO::session()->runAsRoot();
		
		$licenseFile = \GO::getLicenseFile();
		
		$temporaryLicenseFile = new \GO\Base\Fs\File(GO::config()->file_storage_path.'license/'.$licenseFile->name());
		
		
		if($temporaryLicenseFile->exists()){
			if(!$temporaryLicenseFile->move($licenseFile)){
				throw new \Exception("Could not move license file to Group-Office root!");
			}else
			{
				if(!GO::scriptCanBeDecoded()){
					GO\Base\Mail\AdminNotifier::sendMail("Group-Office license invalid", "You attempted to install a license but the license file you provided didn't work. Please contant Intermesh about this error.");
				}  else {
					//add all users to the modules they have access too
					
					\GO\Professional\License::autoConfigureModulePermissions();
					
					
					GO\Base\Mail\AdminNotifier::sendMail("Group-Office license installed successfully!", "Your license was installed and the new users were automatically added to the App permissions if necessary.\n\nThank you for using Group-Office!");
					
				}
			}
		}
	}
}
