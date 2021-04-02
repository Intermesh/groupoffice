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
 * @package GO.modules.sync.model
 * @version $Id: Settings.php 22729 2017-12-12 15:33:06Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */
 
/**
 * The Settings model
 *
 * @package GO.modules.sync.model
 * @property boolean $delete_old_events
 * @property int $max_days_old
 * @property boolean $server_is_master
 * @property int $account_id
 * @property int $note_category_id
 * @property int $tasklist_id
 * @property int $calendar_id
 * @property int $addressbook_id
 * @property int $user_id
 */


namespace GO\Sync\Model;

use go\core\model\Acl;
use go\core\model\User;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\notes\model\NoteBook;
use go\modules\community\tasks\model\Tasklist;

class Settings extends \GO\Base\Db\ActiveRecord{
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return \GO\Calendar\Model\Settings 
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'sync_settings';
	}
	
	public function primaryKey() {
		return 'user_id';
	}
	
	public function relations() {
    
    return array(
				'addressbooks' => array('type'=>self::MANY_MANY, 'model'=>'GO\Addressbook\Model\Addressbook', 'field'=>'user_id', 'linkModel' => 'GO\Sync\Model\UserAddressbook'),
				//'tasklists' => array('type'=>self::MANY_MANY, 'model'=>'GO\Tasks\Model\Tasklist', 'field'=>'user_id', 'linkModel' => 'GO\Sync\Model\UserTasklist'),
				'calendars' => array('type'=>self::MANY_MANY, 'model'=>'GO\Calendar\Model\Calendar', 'field'=>'user_id', 'linkModel'=> 'GO\Sync\Model\UserCalendar'),
				//'noteCategories' => array('type'=>self::MANY_MANY, 'model'=>'GO\Notes\Model\Category', 'field'=>'user_id', 'linkModel' => 'GO\Sync\Model\UserNoteCategory'),
				'calendar' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Calendar\Model\Calendar', 'field'=>'calendar_id'),
				'account' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Email\Model\Account', 'field'=>'account_id'),
		);
  }
	
	public function getDefaultAddressbook(){
		return $this->addressbooks(\GO\Base\Db\FindParams::newInstance()
						->single()
						->criteria(\GO\Base\Db\FindCriteria::newInstance()
										->addCondition('default_addressbook', 1,'=','link_t'))
					);
	}

	public function getDefaultTasklist() {

		$tasklist = Tasklist::find()
			->join('sync_tasklist_user', 'su', 'su.tasklist_id = a.id')
			->filter(['permissionLevel' => Acl::LEVEL_WRITE])
			->where('su.user_id', '=', go()->getAuthState()->getUserId())
			->orderBy(['su.default_tasklist' => 'DESC'])
			->single();

		if (!$tasklist)
			throw new Exception("FATAL: No default tasklist configured");

		return $tasklist;
	}
	
	public function getDefaultCalendar(){
		return $this->calendars(\GO\Base\Db\FindParams::newInstance()
			->single()
			->criteria(\GO\Base\Db\FindCriteria::newInstance()
					->addCondition('default_calendar',1,'=','link_t'))
		);
	}
	
	private $_cache;
	
	public function findForUser(\GO\Base\Model\User $user) {
		if (!isset($this->_cache[$user->id])) {
			$settings = $this->findByPk($user->id);
			$save = false;
			if (!$settings) {
				$settings = new Settings();
				$settings->user_id = $user->id;
				$settings->save();
			}
			if (\GO::modules()->email) {
				$account = $settings->account;
				if (!$account) {
					$account = \GO\Email\Model\Account::model()->findSingleByAttribute('user_id', $user->id);
					if ($account) {
						$settings->account_id = $account->id;
						$save = true;
					}
				}
			}

			if (\GO::modules()->calendar) {
				$stmt = $this->calendars();
				if (!$stmt->rowCount()) {
					$calendar = \GO\Calendar\Model\Calendar::model()->findSingleByAttribute('user_id', $user->id);
					if ($calendar) {
						$settings->addManyMany('calendars', $calendar->id, array('default_calendar' => 1));
					}
				}
			}


			//$newUser = User::findById($user->id, ['syncSettings', 'addressBookSettings', 'notesSettings']);
			

//			if (\GO::modules()->addressbook) {
//				$stmt = $this->addressbooks();
//				if (!$stmt->rowCount()) {
//					$addressbook = \GO\Addressbook\Model\Addressbook::model()->findSingleByAttribute('user_id', $user->id);
//					if ($addressbook) {
//						$settings->addManyMany('addressbooks', $addressbook->id, array('default_addressbook' => 1));
//					}
//				}
//			}
// KAPOT MET 6.3 notes
//			if (\GO::modules()->notes) {
//				$stmt = $this->noteCategories();
//				if (!$stmt->rowCount()) {
//					$noteCategory = \GO\Notes\Model\Category::model()->findSingleByAttribute('user_id', $user->id);
//					if ($noteCategory) {
//						$settings->addManyMany('noteCategories', $noteCategory->id, array('default_category' => 1));
//					}
//				}
//			}




//			if (\GO::modules()->tasks) {
//				$stmt = $this->tasklists();
//				if (!$stmt->rowCount()) {
//					$tasklist = \GO\Tasks\Model\Tasklist::model()->findSingleByAttribute('user_id', $user->id);
//					if ($tasklist) {
//						$settings->addManyMany('tasklists', $tasklist->id, array('default_tasklist' => 1));
//					}
//				}
//			}

			if ($save)
				$settings->save();

			$this->_cache[$user->id] = $settings;
		}


		return $this->_cache[$user->id];
	}

}
