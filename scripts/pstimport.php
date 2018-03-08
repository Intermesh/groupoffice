#!/usr/bin/php
<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: pstimport.php 7752 2011-07-26 13:48:43Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/**
 * This script can import Appointments, Tasks and contacts from a PST file.
 *
 * @copyright Copyright Intermesh
 * @version $Id: pstimport.php 7752 2011-07-26 13:48:43Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.utils
 * @since Group-Office 3.5.16
 *
 * Requires libpff : http://sourceforge.net/projects/libpff/
 * And libpst with readpst: apt-get install libpst
 *
 * Usage:

 sudo -u www-data pstimport.php --pst=/path/to/file.pst --username=gouser
 */

$go = '../www/';


chdir(dirname(__FILE__));
require($go.'cli-functions.inc.php');


$default_addressbook_name='Kontakte';




/*function psti_parse_appointment($props, $data){
	$event = array(
		'name'=>$props['subject'],
		'private'=>$props['sensitivity']!='None' ? '1' : '0',
		'description'=>$data,
		'location'=>$props['location'],
		'status'=>'COMPLETED',
		'background'=>'',
		'busy'=>$props['busy status']!='0x00000000' ? '1' : '0',
		'reminder'=>'',
		'all_day_event'=>0
	);

	$event['start_time']=strtotime($props['start time']);
	$event['end_time']=strtotime($props['end time']);
	$reminder_time = strtotime($props['reminder time']);
	if($reminder_time>0){
		$event['reminder']=$event['start_time']-$reminder_time;
	}
	$event['all_day_event']=date('Hi', $event['start_time']).date('Hi', $event['end_time']);
	return $event;
}*/

function psti_import_task($props, $tasklist){
	global $tasks;

	//var_dump($props);

	$task['name']=$props['subject'];
	$task['user_id']=$tasklist['user_id'];
	$task['tasklist_id']=$tasklist['id'];
	$task['start_time']=isset($props['start date']) ? strtotime($props['start date']) : strtotime($props['creation time']);
	$task['due_time']=isset($props['due date']) ? strtotime($props['due date']) : strtotime($props['creation time']);

	if(isset($props['body']))
		$task['description']=$props['body'];

	$task['status']=$props['is complete']=='no' ? 'NEEDS-ACTION' : 'COMPLETED';
	//$task['tasklist_id']=$_POST['tasklist_id'];
	if(isset($props['reminder time']))
	$task['reminder']=strtotime($props['reminder time']);

	$task_id = $tasks->add_task($task);
}

$args = parse_cli_args($argv);

require_once($go."Group-Office.php");

require_once($GLOBALS['GO_CONFIG']->class_path.'base/users.class.inc.php');
$GO_USERS = new GO_USERS();
$user = $GO_USERS->get_user_by_username($args['username']);

if(!$user)
	die("User ".$args['username']. " not found!\n");

echo "Importing PST file for user ".$user['username']."\n";

require_once($GLOBALS['GO_MODULES']->modules['tasks']['class_path'].'tasks.class.inc.php');
$tasks = new tasks();

$tasklist = $tasks->get_default_tasklist($user['id']);
if(!$tasklist)
	die("Failed to get tasklist\n");

$output_dir = basename($args['pst']).'.export';

chdir($GLOBALS['GO_CONFIG']->tmpdir);
exec('rm -Rf '.$output_dir);

system('pffexport '.escapeshellarg($args['pst']));

if(!is_dir($output_dir))
	exit('pffexport failed on '.$args['pst']."\n");

echo "Importing tasks to tasklist ".$tasklist['name']."\n";

$types = array('Task');

foreach($types as $type){

	exec('find '.$output_dir.' -name '.$type.'.txt', $files);
	//var_dump($output);

	foreach($files as $file){
		$data = file_get_contents($file);

		preg_match_all('/([^:]*):[\t]*(.*)\n/', $data, $matches);

		$props = array();

		for($i=0,$max=count($matches[1]);$i<$max;$i++){
			$props[trim(strtolower($matches[1][$i]))]=trim($matches[2][$i]);
		}

		$f = 'psti_import_'.strtolower($type);

		$f($props, $tasklist);
	}
}


echo "Tasks import complete\n\n----\n\n";


if(is_dir('readpst')){
	exec("rm -Rf readpst");
}
mkdir('readpst');
$files=array();
system("readpst -o readpst ".escapeshellarg($args['pst']), $files);

exec('find readpst -type f', $files);
//var_dump($files);

require_once ($GLOBALS['GO_MODULES']->modules['addressbook']['class_path']."addressbook.class.inc.php");
$ab = new addressbook();
require_once ($GLOBALS['GO_MODULES']->modules['addressbook']['class_path']."vcard.class.inc.php");
$vcard = new vcard();

require_once ($GLOBALS['GO_MODULES']->modules['calendar']['class_path']."calendar.class.inc.php");
require_once ($GLOBALS['GO_MODULES']->modules['calendar']['class_path']."go_ical.class.inc");
require_once($GLOBALS['GO_CONFIG']->class_path.'ical2array.class.inc');
$ical2array = new ical2array();
$cal = new calendar();

$calendar = $cal->get_default_calendar($user['id']);

if(!$calendar)
	die("Failed to get user calendar\n");

/*$addressbook  = $ab->get_addressbook();

if(!$addressbook)
	die("Failed to get user addressbook\n");*/



foreach($files as $file){
	//make sure the file is UTF8 encoded
	File::convert_to_utf8($file);

	$data = file_get_contents($file);
	if(strpos($data,'BEGIN:VEVENT')!==false){

		echo "Importing appointments to calendar ".$calendar['name']."\n";

		$cal->import_ical_string("BEGIN:VCALENDAR\n".$data."\nEND:VCALENDAR", $calendar['id']);
	}elseif(strpos($data,'BEGIN:VCARD')!==false){

		$addressbook_name = File::strip_extension(basename($file));

		if($addressbook_name==$default_addressbook_name){
			$addressbook_name = String::format_name($user,'','','last_name');
		}else
		{
			$addressbook_name = String::format_name($user,'','','last_name').' - '.$addressbook_name;
		}

		$user_addressbook = $ab->get_addressbook_by_name($addressbook_name);
		if(!$user_addressbook || $user_addressbook['user_id']!=$user['id']){
			$user_addressbook=$ab->add_addressbook($user['id'], $addressbook_name);
		}
		$addressbook_id=$user_addressbook['id'];
		echo "Importing contacts to addressbook ".$user_addressbook['name']."\n";


		$vcard->import($file, $user['id'], $addressbook_id);
	}
}



