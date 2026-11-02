<?php

namespace go\modules\community\calendar\model;

use go\core\auth\TemporaryState;
use go\core\model\User;
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


	/**
	 *
	 * @return void
	 * @throws \Exception
	 */

  public function testEvent() {
    $calendar = $this->getCalendar();

    $event = new CalendarEvent();
    $event->calendarId = $calendar->id;
    $event->title = "Test";
		$event->start = (new DateTime())->setTime(12,0,0,0);
		$event->duration = "PT1H";
		$event->freeBusyStatus = CalendarEvent::FREEBUSY_FREE;


    $success = $event->save();
		$this->assertEquals(true, $success);

		$this->assertEquals("13:00", $event->end()->format("H:i"));


		// another user. Don't rely on a fixed id, other tests create and delete users too.
		$name = uniqid('eventtest');
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));

		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState($user->id));

		try {
			// find for another user
			$e2 = CalendarEvent::findFor($user->id)->where(['id' => $event->id])->single();
			$this->assertEquals(null, 	$e2->freeBusyStatus);

			$this->assertEquals("1", 	$e2->modifiedBy);

			// this should not update modifiedBy
			$e2->icsBlob();

			$this->assertEquals("1", 	$e2->modifiedBy);


			$e2->title = "Test 2";
			$e2->save();

			$this->assertEquals($user->id, 	$e2->modifiedBy);
		} finally {
			go()->setAuthState($authState);
			User::delete(['id' => $user->id]);
		}

  }

}