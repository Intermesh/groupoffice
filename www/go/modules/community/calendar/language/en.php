<?php
return [
	'name' => 'Calendar',
	'description' => 'Calendar module; Every user can add, edit or delete appointments Also appointments from other users can be viewed and if necessary it can be changed.',
	'CalendarEvent' => 'Event',


	'mayChangeCalendars'=> 'Change Calendars',
	'mayChangeCategories' => 'Change Categories',
	'mayChangeResources' => 'Change Resource',

	'newScheduleTitle' => 'Do you want to invite the participants?',
	'newScheduleText' => 'You have created an event with participants. By saving this even an invite will be sent to notify the participants.',

	'updateScheduleTitle' => 'Do you want to send an update to the participants?',
	'updateScheduleText' => 'You have made changes to an event with participants. By saving this event an update will be sent to notify them of these changes',

	'cancelScheduleTitle' => 'Do you want to delete the event and notify the participants?',
	'cancelScheduleText' => 'If you delete this event, the participants will be notified that the event is cancelled',

	'deleteScheduleTitle' => 'Do you want to delete the event and notify the organizer?',
	'deleteScheduleText' => 'If you delete this event, the organizer will be notified that you wont participate',

	'replyImipBody' => [
		'accepted' => '{name} accepted event "{title}" {date}',
		'declined' => '{name} declined event "{title}" {date}',
		'tentative' => '{name} is tentative about event "{title}" {date}',
	],
	'replyPageMessage' => [
		'accepted' => 'U have accepted the event',
		'declined' => 'U have declined the event',
		'tentative' => 'U are tentative about the event',
	],
];