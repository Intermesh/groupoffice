<?php

namespace go\core\dav\schedule;

use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\ITip\Broker as SabreBroker;
use Sabre\VObject\Reader;

/**
 * iTip broker that can handle an attendee deleting an event without a master component.
 *
 * When an attendee is invited to single occurrences of a series, the calendar object only contains VEVENTs with a
 * RECURRENCE-ID. On delete, Sabre declines the "master" instance, which doesn't exist, and throws
 * "The supplied iCalendar datetime value is incorrect: master". That aborts the DELETE request.
 */
class Broker extends SabreBroker
{
	/**
	 * When an attendee deletes an event without a master component, decline every occurrence in it instead.
	 *
	 * @param VCalendar|string|null $calendar
	 * @param string|array $userHref
	 * @param VCalendar|string|null $oldCalendar
	 * @return array
	 */
	public function parseEvent($calendar, $userHref, $oldCalendar = null)
	{
		if ($calendar === null && $oldCalendar !== null) {
			if (is_string($oldCalendar)) {
				$oldCalendar = Reader::read($oldCalendar);
			}
			$declined = $this->declineOccurrences($oldCalendar, (array) $userHref);
			if ($declined !== null) {
				return parent::parseEvent($declined, $userHref, $oldCalendar);
			}
		}

		return parent::parseEvent($calendar, $userHref, $oldCalendar);
	}

	/**
	 * Returns a copy of the calendar with the user's participation status set to DECLINED in every occurrence, or null
	 * when the calendar has a master component or the user is the organizer, so Sabre can handle it.
	 *
	 * @param VCalendar $calendar
	 * @param string[] $userHref
	 * @return VCalendar|null
	 */
	private function declineOccurrences(VCalendar $calendar, array $userHref): ?VCalendar
	{
		$vevents = $calendar->select('VEVENT');
		if (empty($vevents)) {
			return null;
		}

		foreach ($vevents as $vevent) {
			if (!isset($vevent->{'RECURRENCE-ID'})) {
				return null;
			}
			if (isset($vevent->ORGANIZER) && in_array($vevent->ORGANIZER->getNormalizedValue(), $userHref)) {
				return null;
			}
		}

		$declined = clone $calendar;
		foreach ($declined->select('VEVENT') as $vevent) {
			foreach ($vevent->select('ATTENDEE') as $attendee) {
				if (in_array($attendee->getNormalizedValue(), $userHref)) {
					$attendee['PARTSTAT'] = 'DECLINED';
				}
			}
		}

		return $declined;
	}
}
