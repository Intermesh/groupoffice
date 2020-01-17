<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: Folder.php 7607 2011-09-01 15:44:36Z <<USERNAME>> $
 * @copyright Copyright Intermesh
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */

/**
 * The Folder model
 *

 * @property int $user_id
 * @property int $folder_id
 */

namespace GO\Files\Model;

use GO\Base\Model\Acl;

class FolderNotification extends \GO\Base\Db\ActiveRecord {


	/**
	 * Returns a static model of itself
	 *
	 * @param String $className
	 * @return FolderNotification
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}


	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'fs_notifications';
	}

	public function primaryKey() {
		return array('user_id', 'folder_id');
	}

	/**
	 * Get users to notify by folder id
	 *
	 * @param int $folder_id
	 *
	 * @return array
	 */
	public static function getUsersToNotify($folder_id) {

		$folder = Folder::model()->findByPk($folder_id, false, true);
		if(!$folder) {
			$stmt = self::model()->findByAttribute('folder_id', $folder_id);
			$stmt->callOnEach('delete');
			return [];
		}
		$acl = $folder->getAcl();
		

		$stmt = self::model()->findByAttribute('folder_id', $folder_id);
		$users = array();
		while ($fnRow = $stmt->fetch()) {
			//ignore user who changed file(s)
			if (\GO::user() && $fnRow->user_id == \GO::user()->id)
				continue;

			if(empty($acl) || !Acl::getUserPermissionLevel($acl->id, $fnRow->user_id)) {
				$fnRow->delete();
				continue;
			}
			$users[] = $fnRow->user_id;
		}
		return $users;
	}

	/**
	 *
	 * @param int|array $folders
	 * @param type $type
	 * @param type $arg1
	 * @param type $arg2
	 */
	public function storeNotification($folders, $type, $arg1, $arg2 = '') {

		if (is_numeric($folders))
			$folders = array((int)$folders);
		elseif (is_array($folders))
			$folders = array_map('intval', $folders);
		else
			return false;

		$users = array();
		foreach ($folders as $folder_id) {
			$users+= self::getUsersToNotify($folder_id);
		}

		$users = array_unique($users);

		if (count($users)) {
			foreach($users as $user_id) {
				$notification = new FolderNotificationMessage();
				$notification->type = $type;
				$notification->arg1 = $arg1;
				$notification->arg2 = $arg2;
				$notification->user_id = $user_id;
				$notification->save();
			}
		}
	}

	public function notifyUser($user_id=null) {

		$notifications = FolderNotificationMessage::getNotifications($user_id);
		if (empty($notifications))
			return false;

		//userCache
		$users = array();
		$messages = array();

		$currentLang = \GO::language()->getLanguage();
		
		$toUser = false;
		if(!empty($user_id)){
			$toUser = \GO::user()->findByPk($user_id);
		} 
		
		if(!$toUser){
			$toUser = \GO::user();
		}
		
		\GO::language()->setLanguage($toUser->language);
		
		foreach ($notifications as $notification) {
			if (!isset($messages[$notification->type]))
				$messages[$notification->type] = array();

			if (!isset($users[$notification->modified_user_id])) {
				$user = \GO::user()->findByPk($notification->modified_user_id, false, true);
				if ($user){					
					$users[$notification->modified_user_id] = $user->getName();
				}else {					
					$users[$notification->modified_user_id] = \GO::t('Deleted user', 'files');
				}
			}

			switch ($notification->type) {
				case FolderNotificationMessage::ADD_FOLDER:
					$messages[$notification->type][] = sprintf(
						\GO::t("Folder %s was add to %s by %s", "files"),
						$notification->arg1,
						$notification->arg2,
						$users[$notification->modified_user_id]
					);
					break;
				case FolderNotificationMessage::RENAME_FOLDER:
					$messages[$notification->type][] = sprintf(
						\GO::t("Folder %s was renamed to %s by %s", "files"),
						$notification->arg1,
						$notification->arg2,
						$users[$notification->modified_user_id]
					);
					break;
				case FolderNotificationMessage::MOVE_FOLDER:
					$messages[$notification->type][] = sprintf(
						\GO::t("Folder %s was moved to %s by %s", "files"),
						$notification->arg1,
						$notification->arg2,
						$users[$notification->modified_user_id]
					);
					break;
				case FolderNotificationMessage::DELETE_FOLDER:
					$messages[$notification->type][] = sprintf(
						\GO::t("Folder %s was deleted by %s", "files"),
						$notification->arg1,
						$users[$notification->modified_user_id]
					);
					break;
				case FolderNotificationMessage::ADD_FILE:
					$messages[$notification->type][] = sprintf(
						\GO::t("File %s was add to %s by %s", "files"),
						$notification->arg1,
						$notification->arg2,
						$users[$notification->modified_user_id]
					);
					break;
				case FolderNotificationMessage::RENAME_FILE:
					$messages[$notification->type][] = sprintf(
						\GO::t("File %s was renamed to %s by %s", "files"),
						$notification->arg1,
						$notification->arg2,
						$users[$notification->modified_user_id]
					);
					break;
				case FolderNotificationMessage::MOVE_FILE:
					$messages[$notification->type][] = sprintf(
						\GO::t("File %s was moved to %s by %s", "files"),
						$notification->arg1,
						$notification->arg2,
						$users[$notification->modified_user_id]
					);
					break;
				case FolderNotificationMessage::DELETE_FILE:
					$messages[$notification->type][] = sprintf(
						\GO::t("File %s was deleted by %s", "files"),
						$notification->arg1,
						$users[$notification->modified_user_id]
					);
					break;
				case FolderNotificationMessage::UPDATE_FILE:
					$messages[$notification->type][] = sprintf(
						\GO::t("File %s was updated by %s", "files"),
						$notification->arg1,
						$users[$notification->modified_user_id]
					);
					break;
			}
			
			//switch status of notification to sent
			//$notification->status = 1;
			//$notification->save();
			$notification->delete();
		}

		//TODO: create emailBody
		$emailBody = '';
		$types = array_keys($messages);
		foreach ($types as $type) {
			foreach ($messages[$type] as $message) {
				$emailBody.= $message . "\n";
			}
		}

		
		
		$message = new \GO\Base\Mail\Message();
		$message->setSubject(\GO::t("Updates in folder", "files"))
				->setTo(array($toUser->email=>$toUser->name))
				->setFrom(array(\GO::config()->webmaster_email=>\GO::config()->title))
				->setBody($emailBody);
		\GO\Base\Mail\Mailer::newGoInstance()->send($message);
		
		\GO::language()->setLanguage($currentLang);
	}
}
