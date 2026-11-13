<?php

namespace go\modules\community\calendar\model;

use DateInterval;
use DateTime;
use DateTimeInterface;
use DateTimeZone;

/**
 * Occurrences of calendar events within a period, for CalendarEvent/query with "expandRecurrences"
 *
 * An occurrence of a recurring event has the ID "<event id>/<recurrence id>", like "42/2026-11-17T09:00:00", with the
 * recurrence ID in the event's time zone. An event that doesn't recur is its own single occurrence with its own ID.
 *
 * @link https://datatracker.ietf.org/doc/html/draft-ietf-jmap-calendars#name-calendarevent-query
 */
class Occurrences
{
	/**
	 * Format of a recurrence ID
	 */
	const RECURRENCE_ID_FORMAT = 'Y-m-d\TH:i:s';

	/**
	 * Find the occurrences of an event that overlap a period
	 *
	 * @param CalendarEvent $event
	 * @param DateTimeInterface $from Start of the period
	 * @param DateTimeInterface $until End of the period
	 * @param DateTimeZone $floatingTimeZone Time zone of events without a time zone
	 * @param int $max Stop after this many occurrences
	 * @return array<array{id: string, start: DateTime}>
	 */
	public static function between(CalendarEvent $event, DateTimeInterface $from, DateTimeInterface $until, DateTimeZone $floatingTimeZone, int $max): array
	{
		$timeZone = $event->timeZone() ?? $floatingTimeZone;

		if (!$event->isRecurring()) {
			$start = self::localTime($event->start->format(self::RECURRENCE_ID_FORMAT), $timeZone);
			return self::overlaps($start, self::addDuration($start, $event->duration), $from, $until)
				? [['id' => (string) $event->id, 'start' => $start]]
				: [];
		}

		$overrides = $event->recurrenceOverrides ?? [];
		$occurrences = [];

		// Occurrences of the rule. The ones with an override are added below, as they may be moved.
		$it = ICalendarHelper::makeRecurrenceIterator($event);
		$earliest = (clone self::toDateTime($from))->sub(new DateInterval($event->duration ?: 'PT0S'));
		$it->fastForward(self::localTime($earliest->setTimezone($timeZone)->format(self::RECURRENCE_ID_FORMAT), $it->current()->getTimezone()));
		while ($it->valid() && count($occurrences) < $max) {
			$recurrenceId = $it->current()->format(self::RECURRENCE_ID_FORMAT);
			$start = self::localTime($recurrenceId, $timeZone);
			if ($start >= $until) {
				break;
			}
			if (!isset($overrides[$recurrenceId]) && self::overlaps($start, self::addDuration($start, $event->duration), $from, $until)) {
				$occurrences[$recurrenceId] = ['id' => $event->id . '/' . $recurrenceId, 'start' => $start];
			}
			$it->next();
		}

		foreach ($overrides as $recurrenceId => $override) {
			if (count($occurrences) >= $max) {
				break;
			}
			if (!empty($override->excluded)) {
				continue;
			}
			$start = self::localTime($override->start ?? $recurrenceId, $timeZone);
			$end = self::addDuration($start, $override->duration ?? $event->duration);
			if (self::overlaps($start, $end, $from, $until)) {
				$occurrences[$recurrenceId] = ['id' => $event->id . '/' . $recurrenceId, 'start' => $start];
			}
		}

		return array_values($occurrences);
	}

	/**
	 * Get an occurrence of an event as a single event
	 *
	 * The properties of the event with the override of the occurrence applied, its start, and "recurrenceId". It has
	 * no "recurrenceRule" and "recurrenceOverrides".
	 *
	 * @param CalendarEvent $event The event, loaded with read permission checked
	 * @param string $recurrenceId For example "2026-11-17T09:00:00"
	 * @param array|null $properties Properties to return. "id" is always returned.
	 * @return array|null Null when the event has no such occurrence
	 */
	public static function toArray(CalendarEvent $event, string $recurrenceId, ?array $properties = null): ?array
	{
		if (!self::isOccurrence($event, $recurrenceId)) {
			return null;
		}

		$arr = $event->toArray();
		$override = $event->recurrenceOverrides[$recurrenceId] ?? null;
		if ($override) {
			if (!empty($override->excluded)) {
				return null;
			}
			foreach ($override->toArray() as $path => $value) {
				self::patch($arr, $path, $value);
			}
		}

		if ($event->showAsPrivate()) {
			// An override may have its own details. Others may not see them for a private event.
			$arr['title'] = '';
			$arr['description'] = '';
			$arr['location'] = '';
			$arr['categoryIds'] = [];
		}

		if (!isset($override) || !isset($override->start)) {
			$arr['start'] = $recurrenceId;
		}
		$arr['id'] = $event->id . '/' . $recurrenceId;
		$arr['recurrenceId'] = $recurrenceId;
		unset($arr['recurrenceRule'], $arr['recurrenceOverrides'], $arr['excluded']);

		if (!empty($properties)) {
			$arr = array_intersect_key($arr, array_flip(array_merge($properties, ['id'])));
		}

		return $arr;
	}

	/**
	 * Whether a recurrence ID is an occurrence of the rule of the event, or has an override
	 */
	private static function isOccurrence(CalendarEvent $event, string $recurrenceId): bool
	{
		$date = \DateTime::createFromFormat(self::RECURRENCE_ID_FORMAT, $recurrenceId);
		if (!$event->isRecurring() || !$date || $date->format(self::RECURRENCE_ID_FORMAT) !== $recurrenceId) {
			return false;
		}

		if (isset($event->recurrenceOverrides[$recurrenceId])) {
			return true;
		}

		$it = ICalendarHelper::makeRecurrenceIterator($event);
		$it->fastForward(self::localTime($recurrenceId, $it->current()->getTimezone()));
		return $it->valid() && $it->current()->format(self::RECURRENCE_ID_FORMAT) === $recurrenceId;
	}

	/**
	 * Set a value at a patch path like "title" or "participants/abc/participationStatus"
	 */
	private static function patch(array &$arr, string $path, mixed $value): void
	{
		$parts = explode('/', $path);
		$target = &$arr;
		$last = array_pop($parts);
		foreach ($parts as $part) {
			if (!isset($target[$part]) || !is_array($target[$part])) {
				$target[$part] = [];
			}
			$target = &$target[$part];
		}
		if ($value === null) {
			unset($target[$last]);
		} else {
			$target[$last] = $value;
		}
	}

	private static function overlaps(DateTime $start, DateTime $end, DateTimeInterface $from, DateTimeInterface $until): bool
	{
		if ($start >= $until) {
			return false;
		}
		// An event without duration is at one moment
		return $end > $from || ($start == $end && $start >= $from);
	}

	private static function addDuration(DateTime $start, ?string $duration): DateTime
	{
		$end = clone $start;
		if (!empty($duration)) {
			$end->add(new DateInterval($duration));
		}
		return $end;
	}

	/**
	 * A local date and time like "2026-11-17T09:00:00" in a time zone
	 */
	private static function localTime(string $local, DateTimeZone $timeZone): DateTime
	{
		return new DateTime($local, $timeZone);
	}

	private static function toDateTime(DateTimeInterface $dt): DateTime
	{
		return $dt instanceof DateTime ? $dt : DateTime::createFromInterface($dt);
	}
}
