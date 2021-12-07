<?php


namespace GO\Calendar;

use Faker\Generator;
use GO\Base\Model\User as GOUser;
use GO\Base\Util\Date;
use GO\Calendar\Model\Calendar;
use GO\Calendar\Model\Event;
use GO\Calendar\Model\Participant;
use GO\Calendar\Model\UserSettings;
use go\core\db\Criteria;
use go\core\db\Query;
use go\core\jmap\Entity;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\Link;
use go\core\model\Module as ModuleModel;
use go\core\model\User;
use go\core\orm\EntityType;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\util\DateTime;
use go\modules\community\comments\Module;

class CalendarModule extends \GO\Base\Module{
	
	
	public function defineListeners() {
		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
		Link::on(Entity::EVENT_FILTER, static::class, 'onLinkFilter');
		User::on(User::EVENT_SAVE, static::class, 'onUserBeforeSave');
	}
	
	public static function onMap(Mapping $mapping) {
		$mapping->addHasOne('calendarSettings', UserSettings::class, ['id' => 'user_id'], true);
		return true;
	}

	public static function onLinkFilter(Filters $filters) {
		$filters->add('pastEvents', function(Criteria $criteria, $value, Query $query, array $filter){
			$query->join('cal_events', 'e', 'search.entityId = e.id');
			$criteria
				->where('search.entityTypeId', '=', Event::model()->modelTypeId())
				->andWhere('e.start_time','<', time());
		});

		$filters->add('forthComingEvents', function(Criteria $criteria, $value, Query $query, array $filter){
			$query->join('cal_events', 'e', 'search.entityId = e.id');
			$criteria
				->where('search.entityTypeId', '=', Event::model()->modelTypeId())
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

	public function demo(Generator $faker)
	{
		$users = User::find(['id'])->limit(10)->all();
		$userCount = count($users) - 1;

		foreach(GOUser::model()->find() as $user) {

			$calendar = Calendar::model()->getDefault($user);

			$calendar->acl->addGroup(\GO::config()->group_internal, \GO\Base\Model\Acl::READ_PERMISSION);

			for($i = 0; $i < 20; $i++) {

				$time = Date::date_add(Date::get_last_sunday(time()), $faker->numberBetween(1, 21));

				$event = new Event();
				$event->name = $faker->company;
				$event->location = $faker->address;
				$event->start_time = Date::clear_time($time, $faker->numberBetween(7,20));
				$event->end_time = $event->start_time + 3600;
				$event->user_id = $user->id;
				$event->calendar_id = Calendar::model()->getDefault($user)->id;
				$event->save();

				$participant = new Participant();
				$participant->is_organizer = true;
				$participant->email = $user->email;
				$participant->name = $user->displayName;
				$participant->user_id = $user->id;
				$event->addParticipant($participant);

				$user2 = $users[$faker->numberBetween(0, $userCount)];
				$user3 = $users[$faker->numberBetween(0, $userCount)];

				if($user2->id != $user->id) {
					$participant = new Participant();
					$participant->email = $user2->email;
					$participant->name = $user2->displayName;
					$participant->user_id = $user2->id;
					$event->addParticipant($participant);
				}

				if($user2->id != $user->id) {
					$participant = new Participant();
					$participant->email = $user3->email;
					$participant->name = $user3->displayName;
					$participant->user_id = $user3->id;
					$event->addParticipant($participant);
				}

				if (ModuleModel::isInstalled("community", "comments")) {
					Module::demoComments($faker, $event);
				}

				Link::demo($faker, $event);

				echo ".";
			}
		}
	}
}
