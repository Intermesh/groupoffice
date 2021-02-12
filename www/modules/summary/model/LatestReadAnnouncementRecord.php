<?php
namespace GO\Summary\Model;

use GO;

/**
 * @property int $user_id
 * @property int $announcement_id
 * @property int $announcement_ctime
 */

class LatestReadAnnouncementRecord extends \GO\Base\Db\ActiveRecord {
	
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function getLocalizedName(){
		return GO::t("Record of latest read announcement", "summary");
	}
	
	public function tableName(){
		return 'su_latest_read_announcement_records';
	}
	
	public function primaryKey() {
		return 'user_id';
	}
	
	/**
	 * Returns true if there is a recorded unread message for $userId. Meant to be
	 * used to check if the Summary module needs to be shown in the view.
	 * @param Int $userId
	 * @return Boolean
	 */
	public static function userHasUnreadAnnouncement($userId) {
		$latestReadAnnouncementRecord = self::_getRecord($userId);
		$latestAnnouncementModel = self::_getLatestAnnouncement($userId);
		if (empty($latestReadAnnouncementRecord) && !empty($latestAnnouncementModel))
			return true;
		return !empty($latestReadAnnouncementRecord) && !empty($latestAnnouncementModel)
			&& $latestAnnouncementModel->ctime > $latestReadAnnouncementRecord->announcement_ctime;
	}
		
	public static function updateLatestRecord($userId) {
		$latestReadAnnouncementRecord = self::_getRecord($userId);
		if (empty($latestReadAnnouncementRecord)) {
			$latestReadAnnouncementRecord = new LatestReadAnnouncementRecord();
			$latestReadAnnouncementRecord->user_id = $userId;
		}
		$latestAnnouncementModel = self::_getLatestAnnouncement($userId);
		if (!empty($latestAnnouncementModel)) {
			$latestReadAnnouncementRecord->announcement_id = $latestAnnouncementModel->id;
			$latestReadAnnouncementRecord->announcement_ctime = $latestAnnouncementModel->ctime;
		} else {
			$latestReadAnnouncementRecord->announcement_id = null;
			$latestReadAnnouncementRecord->announcement_ctime = 0;
		}
		$latestReadAnnouncementRecord->save();
		return $latestReadAnnouncementRecord;
	}
	
	private static function _getRecord($userId) {
		return self::model()->findByPk($userId);
	}
	
	/**
	 * Returns the latest Announcement model that user $userId has at least read
	 * permission to.
	 * @param Integer $userId
	 * @return \GO\Summary\Model\Announcement or false
	 */
	private static function _getLatestAnnouncement($userId) {
		
		$announcementsStmt = Announcement::model()->find(
			\GO\Base\Db\FindParams::newInstance()
				->permissionLevel(\GO\Base\Model\Acl::READ_PERMISSION,$userId)
//				->criteria(
//					\GO\Base\Db\FindCriteria::newInstance()
//						->addCondition('due_time',time(),'<')
//				)
				->order('ctime','DESC')
		);
		
		if (!empty($announcementsStmt) && $announcementsStmt->rowCount()>0)
			return $announcementsStmt->fetch();
		else
			return false;
		
	}
	
}
