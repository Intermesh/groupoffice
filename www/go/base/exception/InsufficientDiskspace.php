<?php
/*
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * Thrown when a user doesn't have access
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: exceptions.class.inc.php 6002 2010-10-27 13:21:25Z mschering $
 * @copyright Copyright Intermesh
 * @package GO.base.exception
 * 
 * @uses Exception
 */


namespace GO\Base\Exception;


class InsufficientDiskspace extends \Exception
{
	private $_total_file_storage;
	
	public function __construct($message='') {

		$message = \GO::t("You don't have anymore diskspace left. Please delete some files or contact your provider to raise the quota")."\n".sprintf(\GO::t("You are using %s of %s"),  \GO\Base\Util\Number::formatSize($this->getUsage()), \GO\Base\Util\Number::formatSize($this->getQuota())).$message;
		
		parent::__construct($message);
	}
	
	/**
	 * Get the quota limit that was overwritten
	 * @return integer quota in bytes
	 */
	protected function getQuota() {
		$quota = \GO::config()->quota * 1024;
		if($quota < $this->getTotalUsage() && $quota > 0)
			return $quota;
		if(\GO::user() && \GO::user()->disk_quota)
			return \GO::user()->getDiskQuota();
		return $quota;
	}
	
	/**
	 * Get the amount is diskspace used when exciding quota
	 * Depending on the quota that is reached
	 * @return integer Disk uage in bytes
	 */
	protected function getUsage() {
		$quota = \GO::config()->quota * 1024;
		if($quota < $this->getTotalUsage() && $quota > 0)
			return $this->getTotalUsage();
		if(\GO::user() && \GO::user()->disk_usage)
			return \GO::user()->disk_usage;
		return $this->getTotalUsage();
	}
	
	/**
	 * Query the file_storage_usage once;
	 * @return integer total file storage usage in bytes
	 */
	protected function getTotalUsage() {
		if(!isset($this->_total_file_storage))
			$this->_total_file_storage=\GO::config()->get_setting('file_storage_usage');
		return $this->_total_file_storage;
	}
}
