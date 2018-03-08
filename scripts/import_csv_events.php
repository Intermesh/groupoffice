<?php

define('NO_EVENTS', true);
if (isset($argv[1])) {
	define('CONFIG_FILE', $argv[1]);
}

$dir = '/home/mschering/Downloads/';

ini_set('max_execution_time', 0);

require('../www/Group-Office.php');

require_once($GLOBALS['GO_CONFIG']->class_path.'base/users.class.inc.php');
$GO_USERS = new GO_USERS();

//login as admin
$GLOBALS['GO_SECURITY']->logged_in($GO_USERS->get_user(1));
$GLOBALS['GO_MODULES']->load_modules();

require_once($GLOBALS['GO_CONFIG']->class_path.'base/links.class.inc.php');
$GO_LINKS = new GO_LINKS();


$del = ',';
$enc = '"';

//We'll import custom fields to this category
$cf_category_name = 'Import';
$addressbook_name = 'Import';


require_once($GLOBALS['GO_MODULES']->modules['customfields']['class_path'] . 'customfields.class.inc.php');
$cf = new customfields();

require_once($GLOBALS['GO_MODULES']->modules['calendar']['class_path'] . 'calendar.class.inc.php');
$cal = new calendar();


$cf_fieldmap = array();

//create custom fields with category and create a map
function create_custom_fields($type, $cf_category_name, $custom_fields) {
	global $cf_fieldmap, $cf, $GO_SECURITY;
	//create custom fields category
	$category = $cf->get_category_by_name($type, $cf_category_name);
	if (!$category) {
		$category['name'] = $cf_category_name;
		$category['type'] = $type;
		$category['acl_id'] = $GLOBALS['GO_SECURITY']->get_new_acl();
		$category_id = $cf->add_category($category);
	} else {
		$category_id = $category['id'];
	}

	$cf_fieldmap[$type] = array();

	foreach ($custom_fields as $f) {
		$field = $cf->get_field_by_name($category_id, $f);
		if (!$field) {
			$field = array('name' => $f, 'datatype' => 'text', 'category_id' => $category_id);
			$cf_fieldmap[$type][$f] = 'col_' . $cf->add_field($field);
		} else {
			$cf_fieldmap[$type][$f] = 'col_' . $field['id'];
		}
	}
	return $category_id;
}

//START OF tasks
create_custom_fields(1, $cf_category_name, array("AfspraakID","AfspraakSoort","Werknummer","InVoerDoor","Gemeld","Voorgrondkleur","Achtergrondkleur","DeclarabeleUren","DeclarabeleKM","Kmstandbegin","KmstandEinde","Administratief","DerdenAktie","DerdenKoppeling"));



function get_calendar($username){
	global $GO_USERS, $cal;

	if(empty($username)){
		return false;
		//die('Empty username!');
	}

	$calendar = $cal->get_calendar_by_name($username);
	if($calendar){
		return $calendar;
	}

	$user = $GO_USERS->get_user_by_username($username);

	if(!$user){

		$user['username']=$username;
		$user['password']=$username;
		$user['email']=$username.'@domain.com';
		$user['first_name']=$username;
		$user['last_name']=$username;

		var_dump($user);

		$user['id']=$GO_USERS->add_user($user);
	}

	return $cal->get_default_calendar($user['id']);	
}

$count['imported'] = 0;
$count['updated'] = 0;
$count['deleted'] = 0;

if (true) {

/*
 * Toelichting bij velden voor acties:
"Action-ID", - Sleutel ID
"Relatie-ID" verwijzing naar relatie
,"Person-ID" verwijzing naar persoon
,"PersonName"  ook een kopie van naam
,"Employee"  werknemer bij actie
,"Action-Type" actie type/soort
,"Status",
"Start" enkel datum
,"Time1", begin tijd
"Time2" eindtijd
,"Remark" memo met alle tekst
,"Letter" gekoppelde brief indien aanwezig link naar file
 */


	File::convert_to_utf8($dir . 'agenda_import.csv');

	//Import companies
	$fp = fopen($dir . 'agenda_import.csv', "r");
	if (!$fp)
		die('Failed to open tasks file');

	$headers = fgetcsv($fp, null, ',', '"');

	if (!$headers)
		die("Failed to get headers from tasks file");

	$index_map = array();
	for ($i = 0, $m = count($headers); $i < $m; $i++) {
		$index_map[$i] = $headers[$i];
		$r_index_map[$headers[$i]] = $i;
	}

	while ($record = fgetcsv($fp, null, ',', '"')) {

		if(!isset($record[$r_index_map['GebruikersNaam']])){
			var_dump($record);
			//exit();
			continue;
		}

		$username=$record[$r_index_map['GebruikersNaam']];

		$calendar = get_calendar($username);
		if(!$calendar){
			echo 'Could not get calendar for '.$username;
			continue;
		}

				
			$cf_values = array();

			for ($i = 0, $m = count($record); $i < $m; $i++) {
				$field = $index_map[$i];

				if (isset($cf_fieldmap[1][$field])) {
					$cf_values[$cf_fieldmap[1][$field]] = $record[$i];
				}
			}
			$cals = array($calendar['id']);
			$cal->get_events($cals,
			1,
			0,
			0,
			'name',
			'ASC',
			0,
			0,
			false,
			$cf_fieldmap[1]['AfspraakID'],
			$cf_values[$cf_fieldmap[1]['AfspraakID']]
			);
			
			$existing_event = $cal->next_record();
			
			$deleted = strpos($record[$r_index_map['Opmerkingen']], 'VERWIJDERD')!==false;

			$event=array(
						'calendar_id'=>$calendar['id'],
						'name'=>empty($record[$r_index_map['Onderwerp']]) ? 'Geen onderwerp' : $record[$r_index_map['Onderwerp']],
						'user_id'=>$calendar['user_id'],
						'start_time'=>strtotime($record[$r_index_map['BeginDatum']]),
						'end_time'=>strtotime($record[$r_index_map['EindDatum']]),
						'ctime'=>strtotime($record[$r_index_map['InVoerDatum']]),
						'mtime'=>strtotime($record[$r_index_map['GewijzigdDatum']]),
						'description'=>$record[$r_index_map['Opmerkingen']],
						'status'=>'ACCEPTED',
						'busy'=>"1"
						);

			if(!$existing_event){

				$count['imported']++;

				if(!$deleted){
					echo '.';
					//echo "Importing [".$cf_values[$cf_fieldmap[1]['AfspraakID']]."] ".$record[$r_index_map['Onderwerp']]."\n";
					$event_id=$cal->add_event($event);

					$cf_values['link_id'] = $event_id;
					$cf->insert_row('cf_1', $cf_values);
				}
			}elseif($deleted)
			{
				$count['deleted']++;
				$cal->delete_event($existing_event['id']);
			}else
			{
				$event['id']=$existing_event['id'];
				$count['updated']++;
				$cal->update_event($event);
			}
		}
	
	fclose($fp);
}

echo "\n\n";

var_dump($count);

echo 'Done!';
echo "\n\n";