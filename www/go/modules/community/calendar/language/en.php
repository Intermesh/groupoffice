<?php
return [
	'name' => 'Calendar',
	'description' => 'A powerful scheduling calendar for teams and individuals. It helps you organize events, manage availability, and coordinate with others in real-time. With support for recurring events, shared calendars, and automatic invite handling, it simplifies planning across time zones and organizations. The fast, modern web interface offers multiple views to give you full control over tracking personal appointments or managing team schedules. Smart filters and custom views make navigating busy calendars easy.',
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
	'deleteScheduleText' => 'If you delete this event, the organizer will be notified that you will not participate',

	'statusScheduleTitle' => 'Do you want to notify the organizer?',
	'statusScheduleText' => 'You have changed your participation status, the organizer will be notified about your participation.',

	'replyImipBody' => [
		'needs-action' => '{name} didn\'t decide for "{title}" {date} yet.',
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