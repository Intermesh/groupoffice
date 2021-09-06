<?php

namespace go\core\auth;
use go\core\model\Module;

abstract class State {
	/**
	 * Get the ID logged in user
	 * 
	 * @return int|null
	 */
	abstract function getUserId();
	
	/**
	 * Get the logged in user
	 * 
	 * @return go\core\model\User|null
	 */
	abstract function getUser();
	
	
	/**
	 * Check if a user is authenticated
	 * 
	 * @return boolean
	 */
	abstract function isAuthenticated();

	/**
	 * Check if the logged in user is an admin
	 * 
	 * @return bool
	 */
	abstract public function isAdmin();

	private static $classRights = [];

	/**
	 * Get the permission level of the module this controller belongs to.
	 * 
	 * @return int
	 */
	public function getClassRights($cls) {
		if(!isset(self::$classRights[$cls])) {
			$mod = Module::findByClass($cls, ['id', 'name', 'package']);
			self::$classRights[$cls]= $mod->getUserRights();
		}


		return self::$classRights[$cls];
	}

	/**
	 * Return absolute URL to /api folder
	 *
	 * @return string
	 */
	abstract protected function getBaseUrl();

	public function getDownloadUrl($blobId) {
		return $this->getBaseUrl() . "/download.php?blob=".$blobId;
	}

	/**
	 * Get URL to page.php
	 *
	 * @return string
	 */
	public function getPageUrl() {
		return $this->getBaseUrl(). "/page.php";
	}

	public function getApiUrl() {
		return $this->getBaseUrl() . '/jmap.php';
	}

	public function getUploadUrl() {
		return $this->getBaseUrl(). '/upload.php';
	}

	public function getEventSourceUrl() {
		return go()->getConfig()['core']['general']['sseEnabled'] ? $this->getBaseUrl() . '/sse.php' : null;
	}

}

