<?php

define('NO_EVENTS', true);
if (isset($argv[1])) {
	define('CONFIG_FILE', $argv[1]);
}

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

$dir = '/home/mschering/Desktop/Multicomp';

require_once($GLOBALS['GO_MODULES']->modules['addressbook']['class_path'] . 'addressbook.class.inc.php');
$ab = new addressbook();

require_once($GLOBALS['GO_MODULES']->modules['customfields']['class_path'] . 'customfields.class.inc.php');
$cf = new customfields();

require_once($GLOBALS['GO_MODULES']->modules['notes']['class_path'] . 'notes.class.inc.php');
$no = new notes();

require_once($GLOBALS['GO_MODULES']->modules['tasks']['class_path'] . 'tasks.class.inc.php');
$ta = new tasks();


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

$addressbook = $ab->get_addressbook_by_name($addressbook_name);
if (!$addressbook) {
	$addressbook = $ab->add_addressbook(1, $addressbook_name);
}
$addressbook_id = $addressbook['id'];

create_custom_fields(3, $cf_category_name, array('Relatie-ID', 'Company-2', 'FirstContact', 'Employee-CD', 'KeySearch'));

if (true) {

	//map the std fields to the csv file headers
	$std_fieldmap['Company'] = 'name';
	$std_fieldmap['Adress1'] = 'address';
	$std_fieldmap['Adress1a'] = 'address_no';
	$std_fieldmap['Adress2'] = 'post_address';
	$std_fieldmap['Adress2a'] = 'post_address_no';
	$std_fieldmap['Postcode-A'] = 'zip';
	$std_fieldmap['Postcode-B'] = 'post_zip';
	$std_fieldmap['City-A'] = 'city';
	$std_fieldmap['City-B'] = 'post_city';
	$std_fieldmap['Telefoon'] = 'phone';
	$std_fieldmap['Telefax'] = 'fax';
	$std_fieldmap['State-A'] = 'state';
	$std_fieldmap['State-B'] = 'post_state';
	$std_fieldmap['Country'] = 'country';
	$std_fieldmap['Remarks'] = 'comment';
	$std_fieldmap['Internet'] = 'homepage';

	File::convert_to_utf8($dir . '/relaties_Sel.txt');

	//Import companies
	$fp = fopen($dir . '/relaties_Sel.txt', "r");
	if (!$fp)
		die('Failed to open companies file');

	$headers = fgetcsv($fp, null, $del, $enc);

	if (!$headers)
		die("Failed to get headers from companies file");

	$index_map = array();
	for ($i = 0, $m = count($headers); $i < $m; $i++) {
		$index_map[$i] = $headers[$i];
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

				$existing_company = $ab->get_company_by_name($addressbook_id, $company['name']);
				if (!$existing_company) {
					$company_id = $ab->add_company($company);
					$cf_values['link_id'] = $company_id;					
				}else
				{
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

create_custom_fields(2, $cf_category_name, array('Person-ID', 'Relatie-ID'));

//START OF CONTACTS
if (true) {

	$std_fieldmap['Family-Name'] = 'last_name';
	$std_fieldmap['FirstName'] = 'first_name';
	$std_fieldmap['Capitals'] = 'initials';
	$std_fieldmap['tussenvoegsel'] = 'middle_name';
	$std_fieldmap['Dear'] = 'salutation';
	$std_fieldmap['Gender'] = 'sex';
	$std_fieldmap['Telefoon'] = 'home_phone';
	$std_fieldmap['Telefax'] = 'fax';
	$std_fieldmap['Mobile'] = 'cellular';
	$std_fieldmap['Function'] = 'function';
	$std_fieldmap['Department'] = 'department';
	$std_fieldmap['Title'] = 'title';
	$std_fieldmap['Birthdate'] = 'birthday';
	$std_fieldmap['Email'] = 'email';

	File::convert_to_utf8($dir . '/Persons_sel.txt');

	//Import companies
	$fp = fopen($dir . '/Persons_sel.txt', "r");
	if (!$fp)
		die('Failed to open contacts file');

	$headers = fgetcsv($fp, null, $del, $enc);

	if (!$headers)
		die("Failed to get headers from contacts file");

	$index_map = array();
	for ($i = 0, $m = count($headers); $i < $m; $i++) {
		$index_map[$i] = $headers[$i];
	}

	while ($record = fgetcsv($fp, null, $del, $enc)) {

		$contact = array('addressbook_id' => $addressbook_id);
		$cf_values = array();

		for ($i = 0, $m = count($record); $i < $m; $i++) {
			$field = $index_map[$i];

			if (isset($std_fieldmap[$field])) {
				$contact[$std_fieldmap[$field]] = $record[$i];
			} elseif (isset($cf_fieldmap[2][$field])) {
				$cf_values[$cf_fieldmap[2][$field]] = $record[$i];
			}
		}


		try {

			/*
			//find company
			if (!empty($cf_values[$cf_fieldmap[2]['Relatie-ID']])) {
				$ab->search_companies(1, $cf_values[$cf_fieldmap[2]['Relatie-ID']], $cf_fieldmap[3]['Relatie-ID'], $addressbook_id);
				$company = $ab->next_record();
				if (!$company) {
					echo "Company with Relatie-ID " . $cf_values[$cf_fieldmap[2]['Relatie-ID']] . " not found!\n";
				} else {
					$contact['company_id'] = $company['id'];
				}
			} else {
				echo "No company set for Person-ID " . $cf_values[$cf_fieldmap[2]['Person-ID']] . "\n";
			}

			

			//$ab->search_contacts(1, $cf_values[$cf_fieldmap[2]['Person-ID']], $cf_fieldmap[2]['Person-ID'], $addressbook_id);
			if($company){
				$ab->search_contacts(1, $company['name'], 'ab_companies.name', $addressbook_id);
				$existing_contact = $ab->next_record();
			}
			if (!$existing_contact) {*/

				$sql = "SELECT * FROM ab_contacts WHERE addressbook_id=$addressbook_id AND last_name=? AND first_name=? AND email=?";
				$ab->query($sql, 'sss', array($contact['last_name'],$contact['first_name'],$contact['email']));
				//$ab->search_contacts(1, $contact['last_name'], 'last_name', $addressbook_id);
				$existing_contact = $ab->next_record();
			//}

			if ($existing_contact) {

				echo "Updating [".$cf_values[$cf_fieldmap[2]['Person-ID']]."] ". $contact['last_name'] . "\n";

				$contact['sex'] = $contact['sex'] == 2 ? 'F' : 'M';

				$cf_values['link_id'] = $existing_contact['id'];
				$cf->replace_row('cf_2', $cf_values);
			}else
			{
				echo "NOT FOUND: [".$cf_values[$cf_fieldmap[2]['Person-ID']]."] ". $contact['last_name'] . "\n";
			}
		} catch (Exception $e) {

		}
		//exit();
		
	}
	fclose($fp);
}

//START OF tasks
create_custom_fields(12, $cf_category_name, array('Action-ID','Person-ID', 'Relatie-ID', 'Letter', 'PersonName','Employee'));



function get_tasklist($tasklist_name){
	global $ta, $GO_SECURITY;
	$tasklist = $ta->get_tasklist_by_name($tasklist_name);
	if($tasklist)
		return $tasklist;

	$tasklist['name']=$tasklist_name;
	$tasklist['user_id']=1;
	$tasklist['acl_id']=$GLOBALS['GO_SECURITY']->get_new_acl('tasks', $tasklist['user_id']);
	$tasklist['id']=$ta->add_tasklist($tasklist);

	return $tasklist;
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


	File::convert_to_utf8($dir . '/Actions_sel.txt');

	//Import companies
	$fp = fopen($dir . '/Actions_sel.txt', "r");
	if (!$fp)
		die('Failed to open tasks file');

	$headers = fgetcsv($fp, null, '[', '|');

	if (!$headers)
		die("Failed to get headers from tasks file");

	$index_map = array();
	for ($i = 0, $m = count($headers); $i < $m; $i++) {
		$index_map[$i] = $headers[$i];
		$r_index_map[$headers[$i]] = $i;
	}

	while ($record = fgetcsv($fp, null, '[', '|')) {

		if(!isset($record[$r_index_map['Employee']])){
			var_dump($record);
			//exit();
			continue;
		}

		$tasklist_name=$record[$r_index_map['Employee']];

		if(!empty($tasklist_name)){
			$tasklist = get_tasklist($tasklist_name);

			
			$cf_values = array();

			for ($i = 0, $m = count($record); $i < $m; $i++) {
				$field = $index_map[$i];

				if (isset($cf_fieldmap[12][$field])) {
					$cf_values[$cf_fieldmap[12][$field]] = $record[$i];
				}
			}
			$ta->get_tasks(array($tasklist['id']),1,
			true,
			'due_time',
			'ASC',
			0,
			0,
			true,
			$search_query=$cf_values[$cf_fieldmap[12]['Action-ID']],
			$search_field=$cf_fieldmap[12]['Action-ID']);
			$existing_task = $ta->next_record();

			if(!$existing_task){

				echo "Importing [".$cf_values[$cf_fieldmap[12]['Action-ID']]."] ".$record[$r_index_map['Action-Type']]."\n";

				$task=array(
					'tasklist_id'=>$tasklist['id'],
					'name'=>$record[$r_index_map['Action-Type']],
					'user_id'=>1,
					'start_time'=>strtotime($record[$r_index_map['Start']]),
					'due_time'=>strtotime($record[$r_index_map['Start']]),
					'description'=>$record[$r_index_map['Remark']],
					'status'=>$record[$r_index_map['Status']]=='OPEN' ? 'ACCEPTED' : 'COMPLETED'
					);


				$task_id=$ta->add_task($task);


				$cf_values['link_id'] = $task_id;
				$cf->insert_row('cf_12', $cf_values);

				//link the task to the company
				if(!empty($cf_values[$cf_fieldmap[12]['Relatie-ID']])){
					$ab->search_companies(1, $cf_values[$cf_fieldmap[12]['Relatie-ID']], $cf_fieldmap[3]['Relatie-ID'], $addressbook_id);
					$company = $ab->next_record();
					if (!$company) {
						echo "Company with Relatie-ID " . $cf_values[$cf_fieldmap[12]['Relatie-ID']] . " not found!";
					} else {
						$GO_LINKS->add_link($task_id, 12, $company['id'], 3);
					}
				}

				//link the task to the contact
				if(!empty($cf_values[$cf_fieldmap[12]['Person-ID']])){
					$ab->search_contacts(1, $cf_values[$cf_fieldmap[12]['Person-ID']], $cf_fieldmap[2]['Person-ID'], $addressbook_id);
					$contact = $ab->next_record();
					if (!$contact) {
						echo "Contact with Person-ID " . $cf_values[$cf_fieldmap[12]['Person-ID']] . " not found!";
					} else {
						$GO_LINKS->add_link($task_id, 12, $contact['id'], 2);
					}
				}
			}
		}else
		{
			echo "Skipping because of empty employee\n";
		}

	}
	fclose($fp);
}

//START OF notes
echo "Starting with notes\n";

create_custom_fields(4, $cf_category_name, array('Event-ID','Person-ID', 'Relatie-ID', 'PersonName','Employee','Action-ID','Mark-ID'));

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
	File::convert_to_utf8($dir . '/Events_sel.txt');

	//Import companies
	$fp = fopen($dir . '/Events_sel.txt', "r");
	if (!$fp)
		die('Failed to open notes file');

	$headers = fgetcsv($fp, null, '[', '|');

	if (!$headers)
		die("Failed to get headers from notes file\n");

	$index_map = array();
	for ($i = 0, $m = count($headers); $i < $m; $i++) {
		$index_map[$i] = $headers[$i];
		$r_index_map[$headers[$i]] = $i;
	}

	//var_dump($r_index_map);

	while ($record = fgetcsv($fp, null, '[', '|')) {
		$category_name=$record[$r_index_map['Employee']];

		if(!empty($category_name)){
			$category = get_category($category_name);


			$cf_values = array();

			for ($i = 0, $m = count($record); $i < $m; $i++) {
				$field = $index_map[$i];

				if (isset($cf_fieldmap[4][$field])) {
					$cf_values[$cf_fieldmap[4][$field]] = $record[$i];
				}
			}
			$no->get_notes(
					$cf_values[$cf_fieldmap[4]['Event-ID']],
					$category['id'],
					'id',
					'ASC',
					0,
					0,
					$cf_fieldmap[4]['Event-ID']);

			$existing_note = $no->next_record();

			if(!$existing_note){


				echo "Importing [".$cf_values[$cf_fieldmap[4]['Event-ID']]."] ".$record[$r_index_map['Event-Type']]."\n";

				$note=array(
					'category_id'=>$category['id'],
					'name'=>$record[$r_index_map['Event-Type']],
					'content'=>$record[$r_index_map['Remarks']]
					);


				$note_id=$no->add_note($note);


				$cf_values['link_id'] = $note_id;
				$cf->insert_row('cf_4', $cf_values);

				//link the note to the company
				if(!empty($cf_values[$cf_fieldmap[4]['Relatie-ID']])){
					$ab->search_companies(1, $cf_values[$cf_fieldmap[4]['Relatie-ID']], $cf_fieldmap[3]['Relatie-ID'], $addressbook_id);
					$company = $ab->next_record();
					if (!$company) {
						echo "Company with Relatie-ID " . $cf_values[$cf_fieldmap[4]['Relatie-ID']] . " not found!";
					} else {
						$GO_LINKS->add_link($note_id, 4, $company['id'], 3);
					}
				}

				//link the note to the connoct
				if(!empty($cf_values[$cf_fieldmap[4]['Person-ID']])){
					$ab->search_contacts(1, $cf_values[$cf_fieldmap[4]['Person-ID']], $cf_fieldmap[2]['Person-ID'], $addressbook_id);
					$contact = $ab->next_record();
					if (!$contact) {
						echo "Company with Person-ID " . $cf_values[$cf_fieldmap[4]['Person-ID']] . " not found!\n";
					} else {
						$GO_LINKS->add_link($note_id, 4, $contact['id'], 2);
					}
				}
				
				if(!empty($cf_values[$cf_fieldmap[4]['Action-ID']])){

					$ta->get_tasks(array(),0,
						true,
						'due_time',
						'ASC',
						0,
						0,
						true,
						$search_query=$cf_values[$cf_fieldmap[4]['Action-ID']],
						$search_field=$cf_fieldmap[12]['Action-ID']);
					$task = $ta->next_record();

					if (!$task) {
						echo "Task with Action-ID " . $cf_values[$cf_fieldmap[4]['Action-ID']] . " not found!\n";
					} else {
						$GO_LINKS->add_link($note_id, 4, $task['id'], 12);
					}
				}

				//break;
			}
		}else
		{
			echo "Skipping because of empty employee\n";
		}
	}
}






if (true) {
	File::convert_to_utf8($dir . '/Markings_sel.txt');

	//Import companies
	$fp = fopen($dir . '/Markings_sel.txt', "r");
	if (!$fp)
		die('Failed to open Markings_sel file');

	$headers = fgetcsv($fp, null, $del, $enc);

	if (!$headers)
		die("Failed to get headers from Markings_sel file");

	$index_map = array();
	for ($i = 0, $m = count($headers); $i < $m; $i++) {
		$index_map[$i] = $headers[$i];
		$r_index_map[$headers[$i]] = $i;
	}

	//var_dump($r_index_map);

	while ($record = fgetcsv($fp, null, $del, $enc)) {
		$category_name='Markings';//$record[$r_index_map['Employee']];

		if(!empty($category_name)){
			$category = get_category($category_name);


			$cf_values = array();

			for ($i = 0, $m = count($record); $i < $m; $i++) {
				$field = $index_map[$i];

				if (isset($cf_fieldmap[4][$field])) {
					$cf_values[$cf_fieldmap[4][$field]] = $record[$i];
				}
			}
			$no->get_notes(
					$cf_values[$cf_fieldmap[4]['Mark-ID']],
					$category['id'],
					'id',
					'ASC',
					0,
					0,
					$cf_fieldmap[4]['Mark-ID']);

			$existing_note = $no->next_record();

			if(!$existing_note){
				$note=array(
					'category_id'=>$category['id'],
					'name'=>$record[$r_index_map['Topic']].' / '.$record[$r_index_map['SubTopic']],
					'content'=>$record[$r_index_map['Remarks']],
					'ctime'=>strtotime($record[$r_index_map['MarkDate']]),
					'mtime'=>strtotime($record[$r_index_map['MarkDate']])
					);


				$note_id=$no->add_note($note);


				$cf_values['link_id'] = $note_id;
				$cf->insert_row('cf_4', $cf_values);

				//link the note to the company
				if(!empty($cf_values[$cf_fieldmap[4]['Relatie-ID']])){
					$ab->search_companies(1, $cf_values[$cf_fieldmap[4]['Relatie-ID']], $cf_fieldmap[3]['Relatie-ID'], $addressbook_id);
					$company = $ab->next_record();
					if (!$company) {
						echo "Company with Relatie-ID " . $cf_values[$cf_fieldmap[4]['Relatie-ID']] . " not found!\n";
					} else {
						$GO_LINKS->add_link($note_id, 4, $company['id'], 3);
					}
				}

				//link the note to the connoct
				if(!empty($cf_values[$cf_fieldmap[4]['Person-ID']])){
					$ab->search_contacts(1, $cf_values[$cf_fieldmap[4]['Person-ID']], $cf_fieldmap[2]['Person-ID'], $addressbook_id);
					$contact = $ab->next_record();
					if (!$contact) {
						echo "Contact with Person-ID " . $cf_values[$cf_fieldmap[4]['Person-ID']] . " not found!\n";
					} else {
						$GO_LINKS->add_link($note_id, 4, $contact['id'], 2);
					}
				}

				if(!empty($cf_values[$cf_fieldmap[4]['Action-ID']])){

					$ta->get_tasks(array($tasklist['id']),1,
						true,
						'due_time',
						'ASC',
						0,
						0,
						true,
						$search_query=$cf_values[$cf_fieldmap[4]['Action-ID']],
						$search_field=$cf_fieldmap[12]['Action-ID']);
					$task = $ta->next_record();

					if (!$task) {
						echo "Task with Action-ID " . $cf_values[$cf_fieldmap[4]['Action-ID']] . " not found!\n";
					} else {
						$GO_LINKS->add_link($note_id, 4, $task['id'], 12);
					}
				}

				//break;
			}
		}else
		{
			echo "Skipping because of empty employee\n";
		}
	}
}

echo 'Done!';
echo "\n\n";