<?php

namespace go\modules\community\calendar\model;

use go\core\auth\TemporaryState;
use go\core\util\DateTime;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase {

  private function getCalendar() {
    $calendar = Calendar::find()->where(['name' => 'Test'])->single();
    if(!$calendar) {
      $calendar = new Calendar();
      $calendar->name = "Test";
      $success = $calendar->save();

      $this->assertEquals(true, $success);
    }
    return $calendar;
  }

  public function testEvent() {
    $calendar = $this->getCalendar();

    $event = new CalendarEvent();
    $event->calendarId = $calendar->id;
    $event->title = "Test";
		$event->start = new DateTime()->setTime(12,0,0,0);
		$event->duration = "PT1H";
		$event->freeBusyStatus = CalendarEvent::FREEBUSY_FREE;


    $success = $event->save();
		$this->assertEquals(true, $success);

		$this->assertEquals("13:00", $event->end()->format("H:i"));


		go()->setAuthState(new TemporaryState(2));


		// find for another user
		$e2 = CalendarEvent::findFor(2)->where(['id' => $event->id])->single();
		$this->assertEquals(null, 	$e2->freeBusyStatus);

		$this->assertEquals("1", 	$e2->modifiedBy);

		// this should not update modifiedBy
		$e2->icsBlob();

		$this->assertEquals("1", 	$e2->modifiedBy);


		$e2->title = "Test 2";
		$e2->save();

		$this->assertEquals("2", 	$e2->modifiedBy);


		go()->setAuthState(new TemporaryState(1));

  }

}