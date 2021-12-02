<?php

namespace go\core\auth;
use Exception;
use go\core\model\Module;
use go\core\model\User;
use stdClass;

abstract class State {
	/**
	 * Get the ID logged in user
	 * 
	 * @return int|null
	 */
	abstract function getUserId(): ?int;
	
	/**
	 * Get the logged in user
	 * 
	 * @return User|null
	 */
	abstract function getUser(): ?User;
	
	
	/**
	 * Check if a user is authenticated
	 * 
	 * @return boolean
	 */
	abstract function isAuthenticated(): bool;

	/**
	 * Check if the logged in user is an admin
	 * 
	 * @return bool
	 */
	abstract public function isAdmin(): bool;

	private static $classRights = [];

	/**
	 * Get the permission level of the module this controller belongs to.
	 *
	 * @return stdClass For example ['mayRead' => true, 'mayManage'=> true, 'mayHaveSuperCowPowers' => true]
	 * @throws Exception
	 */
	public function getClassRights($cls) : stdClass {
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
	abstract protected function getBaseUrl(): string;

	public function getDownloadUrl($blobId): string
	{
		return $this->getBaseUrl() . "/download.php?blob=".$blobId;
	}

	/**
	 * Get URL to page.php
	 *
	 * @return string
	 */
	public function getPageUrl(): string
	{
		return $this->getBaseUrl(). "/page.php";
	}

	public function getApiUrl(): string
	{
		return $this->getBaseUrl() . '/jmap.php';
	}

	public function getUploadUrl(): string
	{
		return $this->getBaseUrl(). '/upload.php';
	}

	public function getEventSourceUrl(): ?string
	{
		return go()->getConfig()['core']['general']['sseEnabled'] ? $this->getBaseUrl() . '/sse.php' : null;
	}

}

