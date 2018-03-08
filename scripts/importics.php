<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: action.php 4347 2010-03-05 11:34:43Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */



$icsfile='/home/mschering/Bureaublad/calendar.ics';

if(isset($argv[1]))
{
	define('CONFIG_FILE', $argv[1]);
}

ini_set('memory_limit','100M');

require_once("../www/Group-Office.php");

require_once ($GLOBALS['GO_MODULES']->modules['calendar']['class_path']."calendar.class.inc.php");
require_once ($GLOBALS['GO_MODULES']->modules['calendar']['class_path']."go_ical.class.inc");
require_once($GLOBALS['GO_CONFIG']->class_path.'ical2array.class.inc');
$ical2array = new ical2array();

$cal = new calendar();

//make sure the file is UTF8 encoded
File::convert_to_utf8($icsfile);


$vcalendar = $ical2array->parse_icalendar_string(file_get_contents($icsfile));
var_dump($vcalendar);

exit();
$calendar = $cal->get_calendar_by_name('Test');

if(!$calendar){
	die("Calendar doesn't exist\n");
}

$cal->import_ical_file($icsfile, $calendar['name']);