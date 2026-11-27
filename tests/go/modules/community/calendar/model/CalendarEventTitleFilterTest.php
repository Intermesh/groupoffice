<?php

namespace go\modules\community\calendar\model;

use go\core\auth\TemporaryState;
use go\core\util\DateTime;
use PHPUnit\Framework\TestCase;

/**
 * The "title" filter matches the title (SUMMARY) of an event only, unlike the "text" filter that also searches the
 * description, location and calendar name.
 */
class CalendarEventTitleFilterTest extends TestCase
{
	private static $authState;
	private static ?Calendar $calendar = null;
	/** @var array<string, int> event data id by key */
	private static array $eventIds = [];

	public static function setUpBeforeClass(): void
	{
		self::$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(1));

		try {
			self::$calendar = new Calendar();
			self::$calendar->name = 'Title filter test ' . uniqid();
			if (!self::$calendar->save()) {
				throw new \Exception(self::$calendar->getValidationErrorsAsString());
			}

			$events = [
				'title' => ['title' => 'Urlaub titlefiltertest', 'description' => ''],
				'description' => ['title' => 'Meeting titlefiltertest', 'description' => 'Urlaub?'],
				'other' => ['title' => 'Homeoffice titlefiltertest', 'description' => ''],
			];
			foreach ($events as $key => $values) {
				$event = new CalendarEvent();
				$event->calendarId = self::$calendar->id;
				$event->title = $values['title'];
				$event->description = $values['description'];
				$event->timeZone = 'Europe/Amsterdam';
				$event->start = (new DateTime('+1 week'))->setTime(12, 0, 0, 0);
				$event->duration = 'PT1H';
				if (!$event->save()) {
					throw new \Exception($event->getValidationErrorsAsString());
				}
				self::$eventIds[$key] = (int)$event->customFieldsModelId();
			}
		} catch (\Throwable $e) {
			self::tearDownAfterClass();
			throw $e;
		}
	}

	public static function tearDownAfterClass(): void
	{
		if (self::$calendar && isset(self::$calendar->id)) {
			CalendarEvent::delete(['calendarId' => self::$calendar->id]);
			Calendar::delete(['id' => self::$calendar->id]);
		}
		go()->setAuthState(self::$authState);
	}

	/**
	 * @return int[] event data ids in the test calendar that match the filter
	 */
	private function find(array $filter): array
	{
		return array_map('intval', CalendarEvent::find()
			->filter(['calendarid' => self::$calendar->id] + $filter)
			->selectSingleValue('cce.eventId')
			->all());
	}

	public function testMatchesTitleOnly(): void
	{
		$ids = $this->find(['title' => '%Urlaub%']);

		$this->assertEquals([self::$eventIds['title']], $ids, 'An event with the word only in the description must not match');
	}

	public function testMatchesAnyOfSeveralValues(): void
	{
		$ids = $this->find(['title' => ['%Urlaub%', '%Homeoffice%']]);
		sort($ids);
		$expected = [self::$eventIds['title'], self::$eventIds['other']];
		sort($expected);

		$this->assertEquals($expected, $ids);
	}
}
