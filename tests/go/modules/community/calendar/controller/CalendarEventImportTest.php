<?php

namespace go\modules\community\calendar\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\exception\NotFound;
use go\core\fs\Blob;
use go\core\model\User;
use go\core\util\DateTime;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\calendar\model\ICalendarHelper;
use PHPUnit\Framework\TestCase;

/**
 * The import must check the permission on the target calendar and may only read files from the blob store. The print
 * reports must not show the details of private events of others.
 */
class CalendarEventImportTest extends TestCase
{
	private $authState;
	private bool $sendMessages;
	private User $user;
	private int $adminCalendarId;
	private array $eventIds = [];

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->sendMessages = CalendarEvent::$sendSchedulingMessages;
		CalendarEvent::$sendSchedulingMessages = false;

		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$name = uniqid('calimport');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));

		$this->adminCalendarId = (int) Calendar::fetchPersonal(User::ID_SUPER_ADMIN);
		$this->assertNotEmpty($this->adminCalendarId);
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if ($this->eventIds) {
			CalendarEvent::delete(['id' => $this->eventIds]);
		}
		User::delete(['id' => $this->user->id]);
		CalendarEvent::$sendSchedulingMessages = $this->sendMessages;
		go()->setAuthState($this->authState);
	}

	/**
	 * The controller without the constructor, which checks the module permissions of the test user
	 */
	private function controller(): \go\modules\community\calendar\controller\CalendarEvent
	{
		return (new \ReflectionClass(\go\modules\community\calendar\controller\CalendarEvent::class))->newInstanceWithoutConstructor();
	}

	private function icsBlob(string $uid): Blob
	{
		$start = (new DateTime('+1 week'))->format('Ymd\T100000');
		$end = (new DateTime('+1 week'))->format('Ymd\T110000');
		$blob = Blob::fromString("BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//test//EN\r\nBEGIN:VEVENT\r\nUID:$uid\r\nDTSTART:$start\r\nDTEND:$end\r\nSUMMARY:Imported\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n");
		$blob->name = 'import.ics';
		$blob->type = 'text/calendar';
		$blob->staleAt = new DateTime('+1 hour');
		$this->assertTrue($blob->save(), var_export($blob->getValidationErrors(), true));
		return $blob;
	}

	public function testUserCannotImportIntoCalendarWithoutPermission()
	{
		$blob = $this->icsBlob(uniqid('imp'));
		go()->setAuthState(new TemporaryState($this->user->id));

		$controller = $this->controller();

		$this->expectException(Forbidden::class);
		$controller->import(['blobIds' => [$blob->id], 'calendarId' => $this->adminCalendarId]);
	}

	public function testOwnerCanImport()
	{
		$uid = uniqid('imp');
		$blob = $this->icsBlob($uid);

		$controller = $this->controller();
		$result = $controller->import(['blobIds' => [$blob->id], 'calendarId' => $this->adminCalendarId]);

		$this->eventIds = CalendarEvent::find()->selectSingleValue('cce.id')->where(['eventdata.uid' => $uid])->all();
		$this->assertSame(1, $result->saved);
		$this->assertCount(1, $this->eventIds);
	}

	public function testBlobIdCannotPointOutsideTheBlobStore()
	{
		$this->expectException(NotFound::class);
		foreach (ICalendarHelper::calendarEventFromFile('../../../../../../etc/passwd') as $event) {
			$this->fail('A file outside the blob store was read');
		}
	}

	public function testPrivateDetailsAreHiddenForOthers()
	{
		$event = new CalendarEvent();
		$event->calendarId = $this->adminCalendarId;
		$event->title = 'Secret meeting';
		$event->location = 'Room 1';
		$event->description = 'Private notes';
		$event->privacy = CalendarEvent::Private;
		$event->timeZone = 'Europe/Amsterdam';
		$event->start = (new DateTime('+1 week'))->setTime(10, 0, 0, 0);
		$event->duration = 'PT1H';
		$this->assertTrue($event->save(), json_encode($event->getValidationErrors()));
		$this->eventIds[] = $event->id;

		// The owner sees the details
		$own = CalendarEvent::findById($event->id);
		$own->hidePrivateDetails();
		$this->assertSame('Secret meeting', $own->title);

		go()->setAuthState(new TemporaryState($this->user->id));
		$other = CalendarEvent::findById($event->id);
		$other->hidePrivateDetails();
		$this->assertSame('', $other->title);
		$this->assertSame('', $other->location);
		$this->assertSame('', $other->description);
	}
}
