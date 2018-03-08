<?php

define('NO_EVENTS', true);
if (isset($argv[1])) {
	define('CONFIG_FILE', $argv[1]);
}

$dir = '/home/mschering/Desktop/mms/';

ini_set('max_execution_time', 0);

require('../www/Group-Office.php');

require_once($GLOBALS['GO_CONFIG']->class_path.'base/users.class.inc.php');
$GO_USERS = new GO_USERS();

//login as admin
$GLOBALS['GO_SECURITY']->logged_in($GO_USERS->get_user(1));
$GLOBALS['GO_MODULES']->load_modules();

require_once($GLOBALS['GO_CONFIG']->class_path.'base/links.class.inc.php');
$GO_LINKS = new GO_LINKS();


$check_existing=false;
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
create_custom_fields(1, $cf_category_name, array("Volgnummer","Gereed","Medewerker","Relatie_code","Oude_code"));



function get_calendar($username){
	global $GO_USERS, $cal, $GO_SECURITY;

	if(empty($username)){
		return false;
		//die('Empty username!');
	}

	$calendar = $cal->get_calendar_by_name($username);
	if($calendar){
		return $calendar;
	}
	
	
	$calendar['user_id'] = 1;
	$calendar['group_id'] = 1;
	$calendar['show_bdays'] = 0;
	$calendar['name']=$username;
	$calendar['acl_id']=$GLOBALS['GO_SECURITY']->get_new_acl('calendar');
	
	$calendar['id']=$cal->add_calendar($calendar);


	return $calendar['id'];	
}

$count['imported'] = 0;
$count['updated'] = 0;
$count['deleted'] = 0;




require_once($GLOBALS['GO_MODULES']->modules['addressbook']['class_path'] . 'addressbook.class.inc.php');
$ab = new addressbook();

$addressbook = $ab->get_addressbook_by_name($addressbook_name);
if (!$addressbook) {
	$addressbook = $ab->add_addressbook(1, $addressbook_name);
}
$addressbook_id = $addressbook['id'];

create_custom_fields(3, $cf_category_name, array('Relatie_code', 'Telefoon_2'));

if (true) {

	//map the std fields to the csv file headers
	$std_fieldmap['Naam'] = 'name';
	$std_fieldmap['Adres'] = 'address';	
	$std_fieldmap['Postcode'] = 'zip';
	$std_fieldmap['Plaats'] = 'city';
	$std_fieldmap['Telefoon_1'] = 'phone';
	$std_fieldmap['Fax'] = 'fax';
	$std_fieldmap['Email'] = 'email';
	
	File::convert_to_utf8($dir . '/relaties.CSV');

	//Import companies
	$fp = fopen($dir . '/relaties.CSV', "r");
	if (!$fp)
		die('Failed to open companies file');

	$headers = fgetcsv($fp, null, $del, $enc);

	if (!$headers)
		die("Failed to get headers from companies file");

	$index_map = array();
	for ($i = 0, $m = count($headers); $i < $m; $i++) {
		$index_map[$i] = $headers[$i];
		$r_index_map[$headers[$i]] = $i;
	}

	while ($record = fgetcsv($fp, null, $del, $enc)) {
		try {
			$company = array('addressbook_id' => $addressbook_id);
			$cf_values = array();

			for ($i = 0, $m = count($record); $i < $m; $i++) {
				$field = $index_map[$i];

				if (isset($std_fieldmap[$field])) {
					$company[$std_fieldmap[$field]] = $record[$i];
				} elseif (isset($cf_fieldmap[3][$field])) {
					$cf_values[$cf_fieldmap[3][$field]] = $record[$i];
				}
			}


			if (isset($company['name'])) {

				echo "Importing " . $company['name'] . "\n";
				
				if(!empty($record[$r_index_map['Postadres']])){
					$address = str_replace("\r\n","\n",$record[$r_index_map['Postadres']]);
					$address = str_replace("\r","\n",$address);
					
					$arr = explode("\n", $address);
					$company['post_address']=$arr[0];
					
					if(isset($arr[1])){
						$zip_city = explode(' ',$arr[1]);
						$company['post_city']=array_pop($zip_city);
						$company['post_zip']=implode(' ',$zip_city);
					}
					
				}
				
				

				$existing_company = $check_existing ? $ab->get_company_by_name($addressbook_id, $company['name']) : false;
				if (!$existing_company) {
					$company_id = $ab->add_company($company);
					$cf_values['link_id'] = $company_id;					
				}else
				{
					$company['id']=$existing_company['id'];
					$ab->update_company($company);
					$cf_values['link_id'] = $existing_company['id'];
				}
				$cf->replace_row('cf_3', $cf_values);
			} else {
				echo "No company name found. Skipping:" . var_export($company, true) . "\n\n";
			}
		} catch (Exception $e) {

		}
	}
	fclose($fp);
}





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


	File::convert_to_utf8($dir . 'Agenda.CSV');

	//Import companies
	$fp = fopen($dir . 'Agenda.CSV', "r");
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

		if(!isset($record[$r_index_map['Medewerker']])){
			var_dump($record);
			//exit();
			continue;
		}

		$username=$record[$r_index_map['Medewerker']];

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
			
			if($check_existing){
				$cal->get_events($cals,
				1,
				0,
				0,
				'name',
				'ASC',
				0,
				0,
				false,
				$cf_fieldmap[1]['Volgnummer'],
				$cf_values[$cf_fieldmap[1]['Volgnummer']]
				);

				$existing_event = $cal->next_record();
			}  else {
				$existing_event =false;
			}
			
			$start_time = strtotime($record[$r_index_map['Datum']].' '.$record[$r_index_map['Tijd']]);
			
			$duration = strtotime($record[$r_index_map['Tijdsduur']]);
			$duration = empty($duration) ? $duration : 1800;
			
			$end_time = $start_time+$duration;
	
			$event=array(
						'calendar_id'=>$calendar['id'],
						'name'=>empty($record[$r_index_map['Omschrijving']]) ? 'Geen onderwerp' : $record[$r_index_map['Omschrijving']],
						'user_id'=>$calendar['user_id'],
						'start_time'=>$start_time,
						'end_time'=>$end_time,
						'ctime'=>strtotime($record[$r_index_map['Last_modified']]),
						'mtime'=>strtotime($record[$r_index_map['Last_modified']]),
						'description'=>$record[$r_index_map['Aantekening']],
						'status'=>'ACCEPTED',
						'busy'=>"1"
						);

			if(!$existing_event){

				$count['imported']++;

					echo '.';
					//echo "Importing [".$cf_values[$cf_fieldmap[1]['AfspraakID']]."] ".$record[$r_index_map['Onderwerp']]."\n";
					$event_id=$cal->add_event($event);
					if($event_id){
						$cf_values['link_id'] = $event_id;
						$cf->insert_row('cf_1', $cf_values);
					}
					
					//link the note to the company
					if(!empty($cf_values[$cf_fieldmap[1]['Relatie_code']])){
						$ab->search_companies(1, $cf_values[$cf_fieldmap[1]['Relatie_code']], $cf_fieldmap[3]['Relatie_code'], array($addressbook_id));
						$company = $ab->next_record();
						if (!$company) {
							echo "Company with Relatie-ID " . $cf_values[$cf_fieldmap[1]['Relatie_code']] . " not found!";
						} else {
							$GO_LINKS->add_link($event_id, 1, $company['id'], 3);
						}
					}
				
			}else
			{
				$event['id']=$event_id=$existing_event['id'];
				$count['updated']++;
				$cal->update_event($event);
			}
			
				

		}
	
	fclose($fp);
}

echo "\n\n";

var_dump($count);


require_once($GLOBALS['GO_MODULES']->modules['notes']['class_path'] . 'notes.class.inc.php');
$no = new notes();



//START OF notes
echo "Starting with notes\n";

create_custom_fields(4, $cf_category_name, array('Relatie_code','Datum_start', 'Datum_eind', 'Licentie_code','Actief','Import_id'));
/*
 * ,"Related-Type" nvt
,"Event-Type" soort event
,"Remarks" memo tekst
,"Vervolg" vervolg in de vorm van actie
,"Employee"  werknemer
,"Action-ID"  Link naar gekoppelde actie

Deze als notes toevoegen
Naam wordt  :> event type
Omschrijving wordt : Memo
wordt gekoppeld aan persoon en contact en indien een vervolg ook aan deze actie
 */


function get_category($category_name){
	global $no, $GO_SECURITY;
	$category = $no->get_category_by_name($category_name);
	if($category)
		return $category;

	$category['name']=$category_name;
	$category['user_id']=1;
	$category['acl_id']=$GLOBALS['GO_SECURITY']->get_new_acl('notes', $category['user_id']);
	$category['id']=$no->add_category($category);

	return $category;
}


if (true) {
	
	$category = get_category("Modules");
			
	File::convert_to_utf8($dir . '/Modules.CSV');

	//Import companies
	$fp = fopen($dir . '/Modules.CSV', "r");
	if (!$fp)
		die('Failed to open notes file');

	$headers = fgetcsv($fp, null, ',', '"');

	if (!$headers)
		die("Failed to get headers from notes file\n");

	$index_map = array();
	for ($i = 0, $m = count($headers); $i < $m; $i++) {
		$index_map[$i] = $headers[$i];
		$r_index_map[$headers[$i]] = $i;
	}
	
	$index_map[]='Import_id';
	$r_index_map['Import_id'] = count($index_map)-1;

//	var_dump($r_index_map);
//	exit();
$note_count=0;
	while ($record = fgetcsv($fp, null, ',', '"')) {
		
			$record[]=$note_count;
			$note_count++;
			

			$cf_values = array();

			for ($i = 0, $m = count($record); $i < $m; $i++) {
				$field = $index_map[$i];

				if (isset($cf_fieldmap[4][$field])) {
					$cf_values[$cf_fieldmap[4][$field]] = $record[$i];
				}
			}
			
			echo '.';
			
			if($check_existing){
				$no->get_notes(
						$cf_values[$cf_fieldmap[4]['Import_id']],
						array($category['id']),
						'id',
						'ASC',
						0,
						0,
						$cf_fieldmap[4]['Import_id']);

				$existing_note = $no->next_record();
			}  else {
				$existing_note = false;
			}

			if(!$existing_note){
				

				//echo "Importing [".$cf_values[$cf_fieldmap[4]['Event-ID']]."] ".$record[$r_index_map['Event-Type']]."\n";

				$note=array(
					'category_id'=>$category['id'],
					'name'=>$record[$r_index_map['Mod_code']],
					'content'=>$record[$r_index_map['Aantekening']]
					);


				$note_id=$no->add_note($note);


				$cf_values['link_id'] = $note_id;
				$cf->insert_row('cf_4', $cf_values);

				//link the note to the company
				if(!empty($cf_values[$cf_fieldmap[4]['Relatie_code']])){
					$ab->search_companies(1, $cf_values[$cf_fieldmap[4]['Relatie_code']], $cf_fieldmap[3]['Relatie_code'], array($addressbook_id));
					$company = $ab->next_record();
					if (!$company) {
						echo "Company with Relatie-ID " . $cf_values[$cf_fieldmap[4]['Relatie_code']] . " not found!";
					} else {
						$GO_LINKS->add_link($note_id, 4, $company['id'], 3);
					}
				}

				
				
			

				//break;
			}
	}
}





require_once ($GLOBALS['GO_MODULES']->modules['tickets']['class_path'] . "tickets.class.inc.php");
$tickets = new tickets();


//START OF notes
echo "Starting with tickets\n";

create_custom_fields(20, $cf_category_name, array('Callnr','Status', 'Relatie_code', 'Mod_code','Medewerker','Probleemsoort','Fax'),'Meldingswijze','Terugbeldatum','Document','LaatsteMail');
/*
 * ,"Related-Type" nvt
,"Event-Type" soort event
,"Remarks" memo tekst
,"Vervolg" vervolg in de vorm van actie
,"Employee"  werknemer
,"Action-ID"  Link naar gekoppelde actie

Deze als notes toevoegen
Naam wordt  :> event type
Omschrijving wordt : Memo
wordt gekoppeld aan persoon en contact en indien een vervolg ook aan deze actie
 */


function get_type($name){
	global $tickets, $GO_SECURITY;
	
	$type = $tickets->get_type_by_name($name);
	if($type )
		return $type ;

	$type ['name']=$name;
	$type ['user_id']=1;
	$type ['acl_id']=$GLOBALS['GO_SECURITY']->get_new_acl('type', $type['user_id']);
	$type ['id']=$tickets->add_type($type);

	return $type;
}


if (true) {
	
	$lang['tickets']['ticket']='Ticket';
	
	$type = get_type("Import");
			
	File::convert_to_utf8($dir . '/Tickets.CSV');

	//Import companies
	$fp = fopen($dir . '/Tickets.CSV', "r");
	if (!$fp)
		die('Failed to open notes file');

	$headers = fgetcsv($fp, null, ',', '"');

	if (!$headers)
		die("Failed to get headers from notes file\n");

	$index_map = array();
	for ($i = 0, $m = count($headers); $i < $m; $i++) {
		$index_map[$i] = $headers[$i];
		$r_index_map[$headers[$i]] = $i;
	}
	
	$index_map[]='Import_id';
	$r_index_map['Import_id'] = count($index_map)-1;

//	var_dump($r_index_map);
//	exit();
$note_count=0;
	while ($record = fgetcsv($fp, null, ',', '"')) {
		
			$record[]=$note_count;
			$note_count++;
			

			$cf_values = array();

			for ($i = 0, $m = count($record); $i < $m; $i++) {
				$field = $index_map[$i];

				if (isset($cf_fieldmap[20][$field])) {
					$cf_values[$cf_fieldmap[20][$field]] = $record[$i];
				}
			}
			
			echo '.';
			
//			$no->get_notes(
//					$cf_values[$cf_fieldmap[4]['Import_id']],
//					array($category['id']),
//					'id',
//					'ASC',
//					0,
//					0,
//					$cf_fieldmap[4]['Import_id']);
//
//			$existing_note = $no->next_record();
$existing_ticket=false;
			if(!$existing_ticket){
				

				//echo "Importing [".$cf_values[$cf_fieldmap[4]['Event-ID']]."] ".$record[$r_index_map['Event-Type']]."\n";

				$ticket=array(
					'type_id'=>$type['id']					
					);
				
				$ticket['id'] = $ticket_id =  $tickets->nextid('ti_tickets');
				$ticket['priority'] = !empty($record[$r_index_map['Urgent']]) ? 2 : 1;
				$ticket['status_id'] = -1;
				$ticket['agent_id'] = 1;
				$ticket['mtime'] = $ticket['ctime'] = strtotime($record[$r_index_map['Datum']]);
				$ticket['first_name'] = $record[$r_index_map['Contactpersoon']];
				$ticket['email'] = '';//$record[$r_index_map['Email']];
				$ticket['phone'] = $record[$r_index_map['Telefoon']];
				$ticket['ticket_number'] = $tickets->generate_ticket_number($ticket['ctime'], $ticket['id']);
				$ticket['ticket_verifier'] = mt_rand(10000000, 99999999);
				
				$v = str_replace("\r\n","\n", $record[$r_index_map['Vraag']]);
				$v = str_replace("\r","\n", $v);
				$v = explode("\n",$v);
				
				$ticket['subject'] = $v[0];
				
				
				
				
				//link the note to the company
				if(!empty($cf_values[$cf_fieldmap[20]['Relatie_code']])){
					$ab->search_companies(1, $cf_values[$cf_fieldmap[20]['Relatie_code']], $cf_fieldmap[3]['Relatie_code'], array($addressbook_id));
					$company = $ab->next_record();
					if (!$company) {
						echo "Company with Relatie-ID " . $cf_values[$cf_fieldmap[20]['Relatie_code']] . " not found!";
					} else {
						$GO_LINKS->add_link($ticket_id, 20, $company['id'], 3);
						$ticket['company_id']=$company['id'];
						$ticket['company']=$company['name'];
					}
				}
				
				$tickets->create_ticket($ticket, $type);
				
				$message['ticket_id'] = $ticket_id;
				
				
				
				$message['is_note'] = 0;
				$message['user_id'] = $GLOBALS['GO_SECURITY']->user_id;
				$message['mtime'] = $message['ctime'] = $ticket['mtime'];						
				$message['content']=$record[$r_index_map['Vraag']];
				
				$tickets->create_message($message);
				
				if(!empty($record[$r_index_map['Oplossing']])){
					$message['content']=$record[$r_index_map['Oplossing']];
					$tickets->create_message($message);
				}
				
				if(!empty($record[$r_index_map['Voortgang']])){
					$message['content']=$record[$r_index_map['Voortgang']];
					$tickets->create_message($message);
				}


				$cf_values['link_id'] = $ticket_id;
				$cf->insert_row('cf_20', $cf_values);

				//link the note to the company
				if(!empty($cf_values[$cf_fieldmap[20]['Relatie_code']])){
					$ab->search_companies(1, $cf_values[$cf_fieldmap[20]['Relatie_code']], $cf_fieldmap[3]['Relatie_code'], array($addressbook_id));
					$company = $ab->next_record();
					if (!$company) {
						echo "Company with Relatie-ID " . $cf_values[$cf_fieldmap[20]['Relatie_code']] . " not found!";
					} else {
						$GO_LINKS->add_link($ticket_id, 20, $company['id'], 3);
					}
				}
			}
	}
}




echo 'Done!';
echo "\n\n";