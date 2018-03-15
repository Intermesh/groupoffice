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
 * @version $Id: CronCollection.php 7962 2011-08-24 14:48:45Z wsmits $
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @package GO.base.cron
 */

/**
 * 
 * @package GO.base.cron
 */

namespace GO\Base\Cron;


class CronCollection extends \GO\Base\Model {
	
	/**
	 * The foldername where the cronjob scripts are located in.
	 * 
	 * @var String 
	 */
	private $_cronFolder = 'cron';
	
	/**
	 * When there are modules that need to be excluded from the cron listings 
	 * then add the module name to this array.
	 * 
	 * @var array 
	 */
	private $_excludedModules = array();
	
	/**
	 * Get all available CronJobClasses
	 * 
	 * @return array
	 */
	public function getAllCronJobClasses(){
		$modules = \GO::modules()->getAllModules();

		$foundCronJobClasses=array();
		foreach($modules as $module){
			
			if(in_array($module->name,$this->_excludedModules))
				continue;
			
			$foundCronJobClasses = array_merge($this->getModuleCronJobClasses($module),$foundCronJobClasses);
		}
		
		$foundCronJobClasses = array_merge($this->getCoreCronJobClasses(),$foundCronJobClasses);
		$foundCronJobClasses = array_merge($this->getFileStorageCronJobClasses(),$foundCronJobClasses);
		
		return $foundCronJobClasses;
	}
	
	public function getCoreCronJobClasses(){
		$foundCronJobClasses=array();
		$folderPath = \GO::config()->root_path.'go/base/cron';
		
		$folder = new \GO\Base\Fs\Folder($folderPath);
		\GO::debug("CRONFILE SEARCH IN FOLDER: ".$folder->path());
		if($folder->exists()){
			$items = $folder->ls();
			$reflectionClasses = array();
			foreach($items as $item){
				if(is_file($item)){
					$className = 'GO\\Base\\Cron\\'.$item->nameWithoutExtension();
					$reflectionClasses[] = new \ReflectionClass($className);
				}
			}
			
			foreach($reflectionClasses as $reflectionClass){
				if($this->_checkIsCronJobClassFile($reflectionClass)){
					\GO::debug("CRONFILE FOUND: ".$reflectionClass->name);
					$cronJob = new $reflectionClass->name();
					$foundCronJobClasses[$reflectionClass->name] = $cronJob->getLabel();
				}
			}
		}
		
		return $foundCronJobClasses;
	}
	
	public function getFileStorageCronJobClasses($folderName='cron'){
		$foundCronJobClasses=array();
		$folderPath = \GO::config()->file_storage_path.'php/'.$folderName;
		
		$folder = new \GO\Base\Fs\Folder($folderPath);
		\GO::debug("CRONFILE SEARCH IN FOLDER: ".$folder->path());
		if($folder->exists()){
			$items = $folder->ls();
			$reflectionClasses = array();
			foreach($items as $item){
				if(is_file($item)){
					$className = 'GOFS\\Cron\\'.$item->nameWithoutExtension();
					$reflectionClasses[] = new \ReflectionClass($className);
				}
			}
			
			foreach($reflectionClasses as $reflectionClass){
				if($this->_checkIsCronJobClassFile($reflectionClass)){
					\GO::debug("CRONFILE FOUND: ".$reflectionClass->name);
					$cronJob = new $reflectionClass->name();
					$foundCronJobClasses[$reflectionClass->name] = $cronJob->getLabel();
				}
			}
		}
		
		return $foundCronJobClasses;
	}
	
	
	
	/**
	 * Get an array of all cronjobs that are available for the given module.
	 * Example output:
	 * array(
	 *	'uniqueCronName'=>'GO\Addressbook\Cron\Check',
	 *	'uniqueCronName2'=>'GO\Addressbook\Cron\AutoMail'
	 * );
	 * 
	 * @param \GO\Base\Module $module
	 * @return array
	 */
	public function getModuleCronJobClasses($module){
		$foundCronJobClasses = array();
		
		if(!$module || !empty($module->package)) //TODO new modules can't have cron jobs now.
			return $foundCronJobClasses;
		
		$reflectionClasses = array_merge($foundCronJobClasses, $module->moduleManager->findClasses($this->_cronFolder));
		
		foreach($reflectionClasses as $reflectionClass){
		
			if($this->_checkIsCronJobClassFile($reflectionClass)){
				
				$cronJob = new $reflectionClass->name();
				$foundCronJobClasses[$reflectionClass->name] = $cronJob->getLabel();
								
			}
		}
		return $foundCronJobClasses;
	}
	
	/**
	 * Check if the class is a subclass of the "GO\Base\Cron\AbstractCron" classfile.
	 * 
	 * @param ReflectionClass $reflectionClass
	 * @return boolean
	 */
	private function _checkIsCronJobClassFile(\ReflectionClass $reflectionClass){
		return $reflectionClass->isSubclassOf("GO\Base\Cron\AbstractCron")?true:false;
	}
	
}
