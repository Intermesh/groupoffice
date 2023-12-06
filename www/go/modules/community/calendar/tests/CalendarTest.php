<?php

use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\calendar\model\Calendar;

class CalendarTest extends \PHPUnit\Framework\TestCase
{
	public function testCalendar() {
		$cal = new Calendar();
		$cal->setValues([
			'name'=>'Agenda',
			'color'=>'0000FF',
			'description'=> 'test files'
		]);
		$this->assertTrue($cal->save());

		$this->assertTrue($cal->isSubscribed); //server-set
		$this->assertTrue($cal->isVisible); //server-set
		$this->assertEquals($cal->includeInAvailability, 'all');
		return $cal;
	}

	/**
	 * @depends testCalendar
	 */
	public function testUpdateCalendar($cal) {
		$cal->isVisible = false;
		$cal->includeInAvailability = 'attending';
		$cal->defaultAlertsWithTime = (object)[
			"1"=>(object)[]
		];
		$this->assertTrue($cal->save());
	}

	public function testPerUserProperties() {

	}

	public function testSharingAndNotifications() {
		// share a calendar
		// receive share notification
		// share free-busy
		// get principal identities
		// principal availability
	}

	/**
	 * @depends testCalendar
	 */
	public function testCreateEvents($calendar) {
		$event1 = new CalendarEvent();
		$event1->setValues([
			'calendarId'=>$calendar->id,
			'title'=> 'Weekly allday',
			'start' => '2017-09-02',
			'duration'=> 'P1D',
			'showWithoutTime'=>true,
			'recurrenceRule'=>(object)['frequence'=>'weekly']
		]);


		$event2 = new CalendarEvent();
		$event2->setValues([
			'calendarId'=>$calendar->id,
			'title'=> 'allday but once',
			'start' => '2017-09-22',
			'duration'=> 'P1D',
			'showWithoutTime'=>true,
		]);

		$event3 = new CalendarEvent();
		$event3->setValues([
			'calendarId'=>$calendar->id,
			'title'=> 'monthly at 15u',
			'timeZone' => 'Europe/Amsterdam',
			'start' => '2019-09-24T15:00:00',
			'duration'=> 'PT2H',
			'showWithoutTime'=>false,
			'recurrenceRule'=>(object)['frequence'=>'monthly']
		]);

		$event4 = new CalendarEvent();
		$event4->setValues([
			'calendarId'=>$calendar->id,
			'title'=> '25 sept 15u (once)',
			'timeZone' => 'Europe/Amsterdam',
			'start' => '2019-09-25T15:00:00',
			'duration'=> 'PT1H30M',
			'showWithoutTime'=>false,
		]);

		$event5 = new CalendarEvent();
		$event5->setValues([
			'calendarId'=>$calendar->id,
			'title'=> 'allday none floating',
			'timeZone' => 'Europe/Amsterdam',
			'start' => '2017-09-22',
			'duration'=> 'P1D',
			'showWithoutTime'=>true,
		]);

		$event1->save();
		$event2->save();
		$event3->save();
		$event4->save();
	}

	public function testParseICalendar() {
		// create valid and invalid ics files
	}

	public function testInviteParticipants() {
		// iTip method
		// add local
		// add remote
	}

	public function testReceivingInvite() {
		// insert remote event.
		// check sequence and cocurrent
		// set participation status
		// try to set other properties that aren't allowed
	}

	public function testRecurrence() {

	}

	public function testrecurrenceOverrides() {
		// move
		// delete
		// undo
		// this-and-future
		// failings tests
	}

	public function testAlerts() {
		//receiving
		//snoozing
		//acknowledging
	}
}