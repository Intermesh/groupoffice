<?php


namespace GO\Core\Controller;


class DeveloperController extends \GO\Base\Controller\AbstractController {

	protected function allowGuests() {
		return array('testvobject');
	}
	
	protected function init() {
		
		if(!\GO::config()->debug)
			throw new \Exception("Developer controller can only be accessed in debug mode");
		
		return parent::init();
	}
	
	public function actionManyGroups($params) {
		
		if(!\GO::user()->isAdmin())
			throw new \Exception("You must be logged in as admin");
		
		for ($i = 1; $i <= 600; $i++) {	
			$group = new \GO\Base\Model\Group();
			$group->name = 'group'.$i;
			$group->save();
		}
	}

	public function actionCreateManyUsers($params) {
		
		if(!\GO::user()->isAdmin())
			throw new \Exception("You must be logged in as admin");
		
		\GO::config()->password_validate = false;
		
		\GO::session()->closeWriting();
		
		$amount = 50;
		$prefix = 'user';
		$domain = 'intermesh.dev';
		
		echo '<pre>';

		for ($i = 1; $i <= $amount; $i++) {		

			echo "Creating $prefix$i\n";
			
			$user = \GO\Base\Model\User::model()->findSingleByAttribute('username', $prefix . $i);
			if(!$user){
				$user = new \GO\Base\Model\User();
				$user->username = $prefix . $i;
				$user->email = $prefix . $i . '@' . $domain;
				$user->password = $prefix . $i;
				$user->first_name = $prefix;
				$user->last_name = $i;
				if(!$user->save()){
					var_dump($user->getValidationErrors());
					exit();
				}
				$user->checkDefaultModels();
			}

			if (\GO::modules()->isInstalled('email') && \GO::modules()->isInstalled('postfixadmin')) {

				$domainModel = \GO\Postfixadmin\Model\Domain::model()->findSingleByAttribute('domain', $domain);

				if (!$domainModel) {
					$domainModel = new \GO\Postfixadmin\Model\Domain();
					$domainModel->domain = $domain;
					$domainModel->save();
				}

				$mailboxModel = \GO\Postfixadmin\Model\Mailbox::model()->findSingleByAttributes(array('domain_id' => $domainModel->id, 'username' => $user->email));

				if (!$mailboxModel) {
					$mailboxModel = new \GO\Postfixadmin\Model\Mailbox();
					$mailboxModel->domain_id = $domainModel->id;
					$mailboxModel->username = $user->email;
					$mailboxModel->password = $prefix . $i;
					$mailboxModel->name = $user->name;	
					$mailboxModel->save();	
				}
				
				
				
				$accountModel = \GO\Email\Model\Account::model()->findSingleByAttributes(array('user_id'=>$user->id, 'username'=>$user->email));
				
				if(!$accountModel){
					$accountModel = new \GO\Email\Model\Account();
					$accountModel->user_id = $user->id;
					$accountModel->host = "localhost";
					$accountModel->port = 143;

					$accountModel->name = $user->name;
					$accountModel->username = $user->email;

					$accountModel->password = $prefix . $i;

					$accountModel->smtp_host = 'localhost';
					$accountModel->smtp_port = 25;
					$accountModel->save();

					$accountModel->addAlias($user->email, $user->name);
				}
			}
		}
		
		echo "Done\n\n";
	}
	
	
	public function actionTestVObject($params){
		
		\GO::session()->runAsRoot();
		
		$ical_str='BEGIN:VCALENDAR
VERSION:1.0
BEGIN:VEVENT
UID:762
SUMMARY:weekly test
DTSTART:20040503T160000Z
DTEND:20040503T170000Z
X-EPOCAGENDAENTRYTYPE:APPOINTMENT
CLASS:PUBLIC
DCREATED:20040502T220000Z
RRULE:W1 MO #0
LAST-MODIFIED:20040503T101900Z
PRIORITY:0
STATUS:NEEDS ACTION
END:VEVENT
END:VCALENDAR';
		
	
		
		$vobject = \GO\Base\VObject\Reader::read($ical_str);
		
		$event = new \GO\Calendar\Model\Event();
		$event->importVObject($vobject->vevent[0]);
		
		var_dump($event->getAttributes());
	}
	
	
	protected function actionGrouped($params){
		
		$stmt = \GO\Base\Model\Grouped::model()->load(
						'GO\Calendar\Model\Event',
						'c.name', 
						'c.name, count(*) AS count',
						\GO\Base\Db\FindParams::newInstance()
						->joinModel(array(
								'model'=>'GO\Calendar\Model\Calendar',
								'localField'=>'calendar_id',
								'tableAlias'=>'c'
						))
						);
		
		echo '<pre>';
		
		foreach($stmt as $calendar){
			echo $calendar->name.' : '.$calendar->count."\n";
		}
		
	}
	
	protected function actionAddRelation($params){
		\GO\Base\Model\User::model()->addRelation('events', array(
				'type'=>  \GO\Base\Db\ActiveRecord::HAS_MANY, 
				'model'=>'GO\Calendar\Model\Event',
				'field'=>'user_id'				
		));
		
		
		$stmt = \GO::user()->events;
		
		foreach($stmt as $event){
			echo $event->toHtml();
			echo '<hr>';
		}
		
	}
	
	
	protected function actionGroupRelation($params){
		\GO\Base\Model\User::model()->addRelation('events', array(
				'type'=>  \GO\Base\Db\ActiveRecord::HAS_MANY, 
				'model'=>'GO\Calendar\Model\Event',
				'field'=>'user_id'				
		));
		
		$fp = \GO\Base\Db\FindParams::newInstance()->groupRelation('events', 'count(events.id) as eventCount');

				
		$stmt = \GO\Base\Model\User::model()->find($fp);
		
		foreach($stmt as $user){
			echo $user->name.': '.$user->eventCount."<br />";
			echo '<hr>';
		}
		
	}
	
	
	protected function actionCreateEvents($params){
		
		$now = \GO\Base\Util\Date::clear_time(time());
		
		for($i=0;$i<30;$i++){
			$time = \GO\Base\Util\Date::date_add($now, -$i);
			
			for($n=0;$n<10;$n++){
				
				$event = new \GO\Calendar\Model\Event();
				$event->name = 'test '.$n;
				
				$event->description = str_repeat('All work and no play, makes Jack a dull boy. ',100);
				
				$event->start_time = \GO\Base\Util\Date::date_add($time, 0,0,0,$n+7);
				$event->end_time = \GO\Base\Util\Date::date_add($time, 0,0,0,$n+8);
				
				$event->save();
					
				
				
			}			
		}		
	}
	
	protected function actionTest($params){
		
		$content = '<html>
			
		<site:img id="1" lightbox="1" path="testing">
		<img src="blabla" />
		</site:img>
		

		<site:img id="2" lightbox="0" path="testing2"><img src="blabla2" /></site:img>

		<site:img id="2" lightbox="0" path="testing3"></site:img>
		
<p>Paragraph</p>
';
		
		
		$tags = \GO\Base\Util\TagParser::getTags('site:img', $content);
		
		var_dump($tags);
		
		
	}
	
	
	protected function actionJoinRelation($params){
		$product = \GO\Billing\Model\Product::model()->findByPk(426	);
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->order(array('book.name', 'order.btime'),array('ASC','DESC'))
						->joinRelation('order.book');
		
		$findParams->getCriteria()
						->addCondition('product_id', $product->id)
						->addCondition('btime', time(), '<', 'order')
						->addCondition('btime', 0, '>', 'order');
		
		$stmt = \GO\Billing\Model\Item::model()->find($findParams);
		
		$item = $stmt->fetch();
		
		//no queries needed to get this value
		echo $item->order->book->name;
	}
	
	
	protected function actionTestParams($test1,$test2,$hasDefault=true){
		
		var_dump($test1);
		
		var_dump($test2);
		
		var_dump($hasDefault);
		
	}
	
	
	protected function actionTestDbClose(){
		
//		\GO::unsetDbConnection();
		
		$stmt = \GO\Base\Model\User::model()->find();
		sleep(10);
		
		echo "Done";
		
	}
	
	
	protected function actionDefaultVat(){
		
		$order = \GO\Billing\Model\Order::model()->findSingle();
		
		$item = new \GO\Billing\Model\Item();
		$item->description="test";
		$item->amount=1;
		$item->unit_price=10;
		$item->order_id=$order->id;
		$item->save();
		
		$order->syncItems();
		
		echo $order->order_id;
		
	}
	
	
	protected function actionDuplicateCF(){
		
		$stmt = \GO\Customfields\Model\Category::model()->findByModel("GO\Projects2\Model\Project");
		$stmt->callOnEach('delete');
		
		$sql = "DROP TABLE IF EXISTS cf_pr2_projects";
		\GO::getDbConnection()->query($sql);
		
		$sql = "CREATE TABLE IF NOT EXISTS `cf_pr2_projects` (
  `model_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB;";
		\GO::getDbConnection()->query($sql);
		

		$stmt = \GO\Customfields\Model\Category::model()->findByModel("GO\Projects\Model\Project");

		foreach($stmt as $category){
			$category->duplicate(array(
					'extends_model'=>"GO\Projects2\Model\Project"
			));
		}
		
		
		
		
		
		
		
		

		
		
		$sql = "INSERT INTO cf_pr2_projects SELECT * FROM cf_pm_projects";
		\GO::getDbConnection()->query($sql);


		$stmt = \GO\Customfields\Model\Category::model()->findByModel("GO\Projects2\Model\TimeEntry");
		$stmt->callOnEach('delete');
		
		
		$sql = "DROP TABLE IF EXISTS cf_pr2_hours";
		\GO::getDbConnection()->query($sql);
		
		$sql = "CREATE TABLE IF NOT EXISTS `cf_pr2_hours` (
  `model_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB;";
		\GO::getDbConnection()->query($sql);

		$stmt = \GO\Customfields\Model\Category::model()->findByModel("GO\Projects\Model\Hour");

		foreach($stmt as $category){
			$category->duplicate(array(
					'extends_model'=>"GO\Projects2\Model\TimeEntry"
			));
		}

		
		
		$sql = "INSERT INTO cf_pr2_hours SELECT * FROM cf_pm_hours";
		\GO::getDbConnection()->query($sql);
			

	}
	
	
	public function actionIcs(){
		$data='BEGIN:VCALENDAR
CALSCALE:GREGORIAN
PRODID:-//Apple Inc.//iOS 7.0//EN
VERSION:2.0
BEGIN:VTIMEZONE
TZID:Europe/Amsterdam
BEGIN:DAYLIGHT
DTSTART:19810329T020000
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
TZNAME:CEST
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
END:DAYLIGHT
BEGIN:STANDARD
DTSTART:19961027T030000
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
TZNAME:CET
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
CREATED:20140120T131840Z
DTEND;TZID=Europe/Amsterdam:20140120T160000
DTSTAMP:20140120T131925Z
DTSTART;TZID=Europe/Amsterdam:20140120T141500
LAST-MODIFIED:20140120T131840Z
RRULE:FREQ=WEEKLY;INTERVAL=2;UNTIL=20140120T131500Z;BYDAY=MO,WE
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:Weekly1
TRANSP:OPAQUE
UID:26ae3ffb-546c-5a32-b87e-49306b68de91
X-GO-REMINDER-TIME:20140120T140500
BEGIN:VALARM
ACTION:DISPLAY
DESCRIPTION:Alarm
TRIGGER:-PT10M
UID:A1AA22FB-D65D-438F-A03E-D93DC744B7EF
X-WR-ALARMUID:A1AA22FB-D65D-438F-A03E-D93DC744B7EF
END:VALARM
END:VEVENT
END:VCALENDAR';
		
		
		$vcalendar = \GO\Base\VObject\Reader::read($data);
		
		echo (string) $vcalendar->vevent[0]->rrule;
	}
	
}
