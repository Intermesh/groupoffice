<?php

namespace go\modules\community\calendar\controller;

use go\core\auth\TemporaryState;
use go\core\model\User;
use go\modules\community\calendar\model\CalendarEvent as EventModel;
use PHPUnit\Framework\TestCase;

/**
 * CalendarEvent/set switches on flags for the saves of the client. They must be off again afterwards, or later saves
 * in the same process, like another call of a JMAP batch or a CalDAV request, send mail or are refused as client edits.
 */
class CalendarEventSetTest extends TestCase
{
	public function testSetRestoresTheFlags(): void
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			// An id that doesn't exist, so nothing is changed or sent
			(new CalendarEvent())->set(['sendSchedulingMessages' => true, 'destroy' => ['999999999']]);
		} finally {
			go()->setAuthState($authState);
		}

		$this->assertFalse(EventModel::$fromClient);
		$this->assertFalse(EventModel::$sendSchedulingMessages);
	}
}
