<?php


namespace GO\Calendar;

use GO\Calendar\Model\Calendar;
use GO\Calendar\Model\Event;
use GO\Calendar\Model\UserSettings;
use go\core\db\Criteria;
use go\core\db\Query;
use go\core\jmap\Entity;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\Link;
use go\core\model\User;
use go\core\orm\EntityType;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\util\DateTime;

class CalendarModule extends \GO\Base\Module{
	
	
	public function defineListeners() {
		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
		Link::on(Entity::EVENT_FILTER, static::class, 'onLinkFilter');
		User::on(User::EVENT_BEFORE_SAVE, static::class, 'onUserBeforeSave');
	}
	
	public static function onMap(Mapping $mapping) {
		$mapping->addHasOne('calendarSettings', UserSettings::class, ['id' => 'user_id'], true);
		return true;
	}

	public static function onLinkFilter(Filters $filters) {
		$filters->add('pastEvents', function(Criteria $criteria, $value, Query $query, array $filter){
			$query->join('cal_events', 'e', 's.entityId = e.id');
			$criteria
				->where('s.entityTypeId', '=', Event::model()->modelTypeId())
				->andWhere('e.start_time','<', time());
		});

		$filters->add('forthComingEvents', function(Criteria $criteria, $value, Query $query, array $filter){
			$query->join('cal_events', 'e', 's.entityId = e.id');
			$criteria
				->where('s.entityTypeId', '=', Event::model()->modelTypeId())
				->andWhere('e.start_time','>=', time());
		});
		return true;
	}
	
	public function author() {
		return 'Merijn Schering';
	}
	
	public function authorEmail() {
		return 'mschering@intermesh.nl';
	}
	
	public function autoInstall() {
		return true;
	}
	
	/**
	 * 
	 * When a user is created, updated or logs in this function will be called.
	 * The function can check if the default calendar, addressbook, notebook etc.
	 * is created for this user.
	 * 
	 */
	public static function firstRun(){
		parent::firstRun();

	}
	
	public static function getDefaultCalendar($userId){
		$user = \GO\Base\Model\User::model()->findByPk($userId, false, true);
		$calendar = Model\Calendar::model()->getDefault($user);		
		return $calendar;
	}
	
	public static function commentsRequired(){
		return isset(\GO::config()->calendar_category_required)?\GO::config()->calendar_category_required:false;
	} 
	
	public static function initListeners() {		
		\GO\Base\Model\User::model()->addListener('delete', "GO\Calendar\CalendarModule", "deleteUser");
		\GO\Base\Model\Reminder::model()->addListener('dismiss', "GO\Calendar\Model\Event", "reminderDismissed");
	}
	
	public static function deleteUser($user){
		Model\Calendar::model()->deleteByAttribute('user_id', $user->id);
		Model\View::model()->deleteByAttribute('user_id', $user->id);		
	}
	
	
	public function install() {
		parent::install();
		
		$group = new Model\Group();
		$group->name=\GO::t("Calendars", "calendar");
		$group->save();
		
		
		$cron = new \GO\Base\Cron\CronJob();
		
		$cron->name = 'Calendar publisher';
		$cron->active = true;
		$cron->runonce = false;
		$cron->minutes = '0';
		$cron->hours = '*';
		$cron->monthdays = '*';
		$cron->months = '*';
		$cron->weekdays = '*';
		$cron->job = 'GO\Calendar\Cron\CalendarPublisher';

		$cron->save();

		//Share calendars with internal by default
		Calendar::entityType()->setDefaultAcl([Group::ID_INTERNAL => Acl::LEVEL_WRITE]);		
		
	}


	public static function onUserBeforeSave(User $user)
	{
		if (!$user->isNew() && $user->isModified('displayName')) {
			$cal = self::getDefaultCalendar($user->id);
			$cal->name = $user->displayName;
			$cal->save(true);
		}
	}
}
