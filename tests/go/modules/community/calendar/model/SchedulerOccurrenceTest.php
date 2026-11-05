<?php

namespace go\modules\community\calendar\model;

use go\core\auth\TemporaryState;
use go\core\http\PostResponseProcessor;
use go\core\orm\EntityType;
use go\core\util\DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Moving a single occurrence of a series sends an invite for that occurrence. The invite must be handled on the
 * saved event and not on the patched copy that is used for the occurrence, otherwise the change is registered with
 * a NULL entityId and the secret in the invite link is never saved.
 */
class SchedulerOccurrenceTest extends TestCase
{
	private bool $sendMessages;

	protected function setUp(): void
	{
		$this->sendMessages = CalendarEvent::$sendSchedulingMessages;
		CalendarEvent::$sendSchedulingMessages = true;
		go()->setAuthState(new TemporaryState(1));
	}

	protected function tearDown(): void
	{
		CalendarEvent::$sendSchedulingMessages = $this->sendMessages;
		// never actually send the queued invites
		$ref = new \ReflectionProperty(PostResponseProcessor::class, 'tasks');
		$ref->setValue(PostResponseProcessor::get(), []);
	}

	private function queuedEntityIds(): array
	{
		$ref = new \ReflectionProperty(EntityType::class, 'changes');
		$ids = [];
		foreach ($ref->getValue() as $entityTypeId => $changes) {
			if ($entityTypeId == CalendarEvent::entityType()->getId()) {
				foreach ($changes as $change) {
					$ids[] = $change['entityId'];
				}
			}
		}
		return $ids;
	}

	public function testMovedOccurrenceInviteUsesSavedEvent(): void
	{
		$calendar = Calendar::find()->where(['name' => 'Test'])->single();
		if (!$calendar) {
			$calendar = new Calendar();
			$calendar->name = 'Test';
			$this->assertTrue($calendar->save());
		}

		$admin = \go\core\model\User::findById(1, ['email']);
		$start = (new DateTime('+1 week'))->setTime(12, 0, 0, 0);

		$event = new CalendarEvent();
		$event->calendarId = $calendar->id;
		$event->title = 'Series';
		$event->timeZone = 'Europe/Amsterdam';
		$event->start = $start;
		$event->duration = 'PT1H';
		$event->setRecurrenceRule((object) ['frequency' => 'daily', 'interval' => 1]);
		$event->setValues(['participants' => [
			'owner' => ['email' => $admin->email, 'name' => 'Admin', 'roles' => ['owner' => true], 'kind' => 'individual', 'participationStatus' => 'accepted'],
			'ext' => ['email' => 'external@example.com', 'name' => 'External', 'roles' => ['attendee' => true], 'kind' => 'individual'],
		]]);
		$this->assertTrue($event->save(), json_encode($event->getValidationErrors()));

		$secretBefore = $this->secret($event);
		$this->assertNotEmpty($secretBefore);

		// move a single occurrence to another day
		$recurrenceId = $start->format('Y-m-d\TH:i:s');
		$event = CalendarEvent::findById($event->id);
		// the base event is modified too, so the invite contains the series and the moved occurrence
		$event->title = 'Series (updated)';
		$event->setValues(['recurrenceOverrides' => [
			$recurrenceId => ['start' => (clone $start)->modify('+2 days')->format('Y-m-d\TH:i:s')]
		]]);
		$this->assertTrue($event->save(), json_encode($event->getValidationErrors()));

		// a new secret is put in the invite link, so the saved participant must have it
		$this->assertNotEquals($secretBefore, $this->secret($event), 'The secret used in the invite link must be saved');

		// mail sending failures are handled by the mailer, we only need the registered change
		PostResponseProcessor::get()->runTasks();
		$this->assertNotContains(null, $this->queuedEntityIds(), 'A change was registered without entityId');
		$this->assertContains($event->id, $this->queuedEntityIds());
	}

	private function secret(CalendarEvent $event): ?string
	{
		return go()->getDbConnection()->selectSingleValue('scheduleSecret')
			->from('calendar_participant')
			->join('calendar_event', 'e', 'e.eventId = t.eventId')
			->where(['e.uid' => $event->uid, 'email' => 'external@example.com'])
			->single();
	}
}
