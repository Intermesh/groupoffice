<?php

namespace go\modules\community\calendar\model;

use go\core\auth\TemporaryState;
use go\core\util\DateTime;
use PHPUnit\Framework\TestCase;

/**
 * A participant replies on the public invite page (Module::pageInvite()) without being logged in. Saving the event
 * failed with a TypeError in CalendarEvent::updateAlerts() because forUserId() is null.
 */
class CalendarEventUnauthenticatedSaveTest extends TestCase
{
	private bool $sendMessages;
	private ?int $eventId = null;

	protected function setUp(): void
	{
		$this->sendMessages = CalendarEvent::$sendSchedulingMessages;
		CalendarEvent::$sendSchedulingMessages = false;
		go()->setAuthState(new TemporaryState(1));
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(1));
		if ($this->eventId) {
			CalendarEvent::delete(['id' => $this->eventId]);
		}
		CalendarEvent::$sendSchedulingMessages = $this->sendMessages;
	}

	public function testReplyWithoutLoggedInUser(): void
	{
		$calendarId = Calendar::fetchPersonal(1);
		$this->assertNotEmpty($calendarId);
		$admin = \go\core\model\User::findById(1, ['email']);

		$event = new CalendarEvent();
		$event->calendarId = $calendarId;
		$event->title = 'Invite';
		$event->timeZone = 'Europe/Amsterdam';
		$event->start = (new DateTime('+1 week'))->setTime(19, 0, 0, 0);
		$event->duration = 'PT1H';
		$event->replyTo = $admin->email;
		$event->setValues(['participants' => [
			'owner' => ['email' => $admin->email, 'name' => 'Admin', 'roles' => ['owner' => true], 'kind' => 'individual', 'participationStatus' => 'accepted'],
			'ext' => ['email' => 'external@example.com', 'name' => 'External', 'roles' => ['attendee' => true], 'kind' => 'individual'],
		]]);
		$this->assertTrue($event->save(), json_encode($event->getValidationErrors()));
		$this->eventId = $event->id;

		// like the public invite page: nobody is logged in
		go()->setAuthState(new TemporaryState());

		$event = CalendarEvent::findById($this->eventId);
		$event->participants['ext']->participationStatus = Participant::Accepted;
		$this->assertTrue($event->save(), json_encode($event->getValidationErrors()));

		$this->assertEquals(Participant::Accepted, CalendarEvent::findById($this->eventId)->participants['ext']->participationStatus);
	}
}
