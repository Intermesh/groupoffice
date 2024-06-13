<?php
/** @var string $GO_SCRIPTS_JS */
$calendar = \GO\Calendar\Model\Calendar::model()->getDefault(\GO::user());

$settings = \GO\Calendar\Model\Settings::model()->getDefault(\GO::user());

if($calendar)
	$GO_SCRIPTS_JS .= 'GO.calendar.defaultCalendar = '.json_encode($calendar->getAttributes()).';';

$GO_SCRIPTS_JS .='GO.calendar.categoryRequired="'.\GO\Calendar\CalendarModule::commentsRequired().'";';
$GO_SCRIPTS_JS .='GO.calendar.disablePublishing='.(!empty(\GO::config()->calendar_disable_publishing)?'true':'false').';';

if($settings)
	$GO_SCRIPTS_JS .='GO.calendar.showStatuses='.($settings->show_statuses ? 'true;' : 'false;');
