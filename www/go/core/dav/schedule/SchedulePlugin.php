<?php

namespace go\core\dav\schedule;

use Sabre\CalDAV\Schedule\Plugin;
use Sabre\VObject\ITip\Message;

/**
 * CalDAV scheduling without delivery to local inboxes.
 *
 * GroupOffice stores an event once for all calendars it's in. When an event is saved, CalendarEvent adds it to the
 * personal calendar of every local participant, so invitations to local users always end up in their calendar.
 * Sabre's local delivery would write the iTip result into the recipient's calendar on its own, before the organizer's
 * change is saved and without permission checks. Messages are still sent by e-mail by the IMipPlugin.
 */
class SchedulePlugin extends Plugin
{
	/**
	 * Local delivery is handled by the CalendarEvent model.
	 *
	 * @param Message $iTipMessage
	 */
	public function scheduleLocalDelivery(Message $iTipMessage)
	{
	}
}
