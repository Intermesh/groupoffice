<?php

namespace go\core;

use Exception;
use go\core\cache\None;
use go\core\db\Utils;
use go\core\module\Base;
use go\core\orm\Entity;
use go\core\util\ClassFinder;
use go\modules\core\groups\model\Group;
use go\modules\core\users\model\User;
use go\modules\core\users\model\UserGroup;

class Installer {
	
	private $isInProgress = false;
	
	/**
	 * Check if it's installing or upgrading
	 * 
	 * @return bool
	 */
	public function isInProgress() {
		return $this->isInProgress;
	}	
	
	/**
	 * 
	 * @param array $adminValues
	 * @param Base[] $installModules
	 * @return boolean
	 * @throws Exception
	 */
	public function install(array $adminValues = [], $installModules = []) {
		
		
		//don't cache on install
		App::get()->getCache()->flush(false);
		App::get()->setCache(new None());
		
		$this->isInProgress = true;
		
		$database = App::get()->getDatabase();
		
		if (count($database->getTables())) {
			throw new Exception("Database is not empty");
		}

		$database->setUtf8();

		Utils::runSQLFile(Environment::get()->getInstallFolder()->getFile("install/install.sql"));
		
		
		App::get()->getDbConnection()->query("SET FOREIGN_KEY_CHECKS=0;");
		foreach (["Admins", "Everyone", "Internal"] as $groupName) {
			$group = new Group();
			$group->name = $groupName;
			if (!$group->save()) {
				throw new Exception("Could not create group");
			}
		}

		$admin = new User();
		$admin->displayName = "System administrator";
		$admin->username = "admin";
		$admin->email = "admin@localhost.localdomain";
		$admin->setPassword("admin");
		
		$admin->setValues($adminValues);
		
		if(!isset($admin->recoveryEmail)) {
			 $admin->recoveryEmail = $admin->email;
		}
		
		$admin->groups[] = (new UserGroup)->setValues(['groupId' => Group::ID_ADMINS]);
		$admin->groups[] = (new UserGroup)->setValues(['groupId' => Group::ID_INTERNAL]);


		if (!$admin->save()) {
			throw new Exception("Failed to create admin user: ".var_export($admin->getValidationErrors(), true));
		}

		$classFinder = new ClassFinder(false);
		$classFinder->addNamespace("go\\modules\\core");

		$coreModules = $classFinder->findByParent(Base::class);
		
		foreach($coreModules as $coreModule) {
			$mod = new $coreModule();
			
			if(!$mod->install()) {
				throw new \Exception("Failed to install core module ". $coreModule);
			}
		}
		
		
		//register core entities
		$classFinder = new ClassFinder(false);
		$classFinder->addNamespace("go\\core");
		$classFinder->addNamespace("go\\modules\\core");
		
		$entities = $classFinder->findByParent(Entity::class);
		
		foreach($entities as $entity) {
			if(!$entity::getType()) {
				return false;
			}
		}
		
		foreach($installModules as $installModule) {
			$installModule->install();
		}
		
		
		//for new framework
		App::get()->getSettings()->databaseVersion = App::get()->getVersion();
		App::get()->getSettings()->save();
		
		App::get()->setCache(new cache\Disk());
		\go\core\event\Listeners::get()->init();		
	}
	
	public function upgrade() {
		//todo
		$this->isInProgress = true;
	}
}
