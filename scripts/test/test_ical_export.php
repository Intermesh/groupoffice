<?php
require('../../www/Group-Office.php');

require_once ($GLOBALS['GO_MODULES']->modules['calendar']['class_path']."calendar.class.inc.php");
require_once ($GLOBALS['GO_MODULES']->modules['calendar']['class_path']."go_ical.class.inc");
$cal = new calendar();

$event_id=2;

$go_ical = new go_ical('2.0', true);
//$go_ical->dont_use_quoted_printable=true;
$ical_event = $go_ical->export_event($event_id);

var_dump($ical_event);

//echo $tz = $go_ical->export_timezone();

