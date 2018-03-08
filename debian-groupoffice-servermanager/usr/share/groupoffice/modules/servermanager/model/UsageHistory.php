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
 * @version $Id UsageHistory.php 2012-09-03 10:13:14 mdhart $
 * @author Michael de Hart <mdehart@intermesh.nl> 
 * @package GO.servermanager.model
 */
/**
 * The active record for logging the usage of an installation
 *
 * @package GO.servermanager.model
 * @copyright Copyright Intermesh
 * @version $Id UsageHistory.php 2012-09-03 10:13:14 mdhart $ 
 * @author Michael de Hart <mdehart@intermesh.nl> 
 * 
 * @property int $id PK
 * @property int $ctime the time this usage data was created
 * @property string $user_count the amount of users in the system (trial and payed)
 * @property double $database_usage mailbox size in bytes
 * @property double $file_storage_usage file storage folder size in bytes
 * @property double $mailbox_usage mailbox folder size i nbytes
 * @property int $total_logins the amount of logins into the system
 */

namespace GO\ServerManager\Model;


class UsageHistory extends \GO\Base\Db\ActiveRecord
{
	
	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'sm_usage_history';
	}
	
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function getDatabaseUsageText()
	{
		return \GO\Base\Util\Number::formatSize($this->database_usage);
	}
	public function getFileStorageUsageText()
	{
		return \GO\Base\Util\Number::formatSize($this->file_storage_usage);
	}
	public function getMailboxUsageText()
	{
		return \GO\Base\Util\Number::formatSize($this->mailbox_usage);
	}
	public function getTotalUsageText()
	{
		return \GO\Base\Util\Number::formatSize($this->getTotalUsage());
	}
	
	/**
	 * Get the total usage of database, files and mailbox
	 * Size is in bytes
	 * @return double $totalUsage 
	 */
	public function getTotalUsage()
	{
		return $this->database_usage + $this->file_storage_usage + $this->mailbox_usage;
	}

}

?>
