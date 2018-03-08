<?php

define('NO_EVENTS', true);
if (isset($argv[1])) {
	define('CONFIG_FILE', $argv[1]);
}

ini_set('max_execution_time', 0);

//change the path to Group-Office.php if necessary
require('Group-Office.php');

//We'll import custom fields to this category
$cf_category_name = 'Import';

//path to csv file
$path = './export__text.csv';

$del = ',';
$enc = '"';


require_once($GLOBALS['GO_CONFIG']->class_path.'base/users.class.inc.php');
$GO_USERS = new GO_USERS();

//login as admin
$GLOBALS['GO_SECURITY']->logged_in($GO_USERS->get_user(1));
$GLOBALS['GO_MODULES']->load_modules();

require_once($GLOBALS['GO_CONFIG']->class_path.'base/links.class.inc.php');
$GO_LINKS = new GO_LINKS();


require_once($GLOBALS['GO_MODULES']->modules['addressbook']['class_path'] . 'addressbook.class.inc.php');
$ab = new addressbook();

require_once($GLOBALS['GO_MODULES']->modules['customfields']['class_path'] . 'customfields.class.inc.php');
$cf = new customfields();

require_once($GLOBALS['GO_MODULES']->modules['notes']['class_path'] . 'notes.class.inc.php');
$no = new notes();

require_once($GLOBALS['GO_MODULES']->modules['tasks']['class_path'] . 'tasks.class.inc.php');
$ta = new tasks();

require_once($GLOBALS['GO_MODULES']->modules['calendar']['class_path'] . 'calendar.class.inc.php');
$ca = new calendar();


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


create_custom_fields(1, $cf_category_name, array(
                                            'createdbyname',
                                            'modifiedbyname',
                                            'scheduleddurationminutes',
                                            'actualdurationminutes',
                                            'statecode')
                                            );


create_custom_fields(4, $cf_category_name, array(
                                            'createdbyname',
                                            'modifiedbyname',
                                            'scheduleddurationminutes',
                                            'actualdurationminutes',
                                            'scheduledstart',
                                            'location',
                                            'statecode')
                                            );

create_custom_fields(12, $cf_category_name, array(
                                            'createdbyname',
                                            'modifiedbyname',
                                            'scheduleddurationminutes',
                                            'actualdurationminutes',
                                            'location',
                                            'statecode')
                                            );

if (true)
{

	//map the std fields to the csv file headers
	$std_fieldmap['owneridname'] = 'type_name';
	$std_fieldmap['alldayevent'] = 'all_day_event';
	$std_fieldmap['objectname'] = 'link_name';
	$std_fieldmap['activitytype'] = 'import_type';
	$std_fieldmap['isprivate'] = 'private';
	$std_fieldmap['scheduledstart'] = 'start_time';
	$std_fieldmap['scheduledend'] = 'end_time';
	$std_fieldmap['description'] = 'description';
	$std_fieldmap['subject'] = 'name';
	$std_fieldmap['createdon'] = 'ctime';
	$std_fieldmap['modifiedon'] = 'mtime';
	$std_fieldmap['location'] = 'location';	

	//File::convert_to_utf8($dir . '/export_test.csv');
        
	$fp = fopen($path, "r");
	if (!$fp)
		die('Failed to open import file');

	$headers = fgetcsv($fp, null, $del, $enc);

	if (!$headers)
		die("Failed to get headers from import file");

	$index_map = array();
	for ($i = 0, $m = count($headers); $i < $m; $i++)
        {
		$index_map[$i] = $headers[$i];
	}

        $num_tasks=0;      
        $num_events=0;
        $num_notes=0;
        
	while ($record = fgetcsv($fp, null, $del, $enc))
        {      
		try
                {                        
                        $import = array();
			$cf_values = array();
                        $temp_values = array();

                        for($i=0, $m=count($record); $i < $m; $i++)
                        {                                
				$field = $index_map[$i];                               
                                $temp_values[$field] = $record[$i];
			}

                        switch($temp_values['activitytype'])
                        {
                                case 'Event':
                                        $type = 1;                                                                                
                                        break;

                                case 'Note':
                                        $type = 4;
                                        break;

                                default:
                                        $type = 12;
                                        break;
                        }

                        foreach($cf_fieldmap[$type] as $field => $col)
                        {
                                if(isset($temp_values[$field]))
                                {
                                        $cf_values[$col] = $temp_values[$field];
                                }
                        }
                        foreach($std_fieldmap as $field => $value)
                        {
                                $import[$value] = $temp_values[$field];
                        }
                        
                        
			if(isset($import['name']))
                        {                     
				echo "Importing ".$import['import_type'].': ' . $import['name'] . "\n";

                                $import['ctime'] = strtotime($import['ctime']);
                                $import['mtime'] = strtotime($import['mtime']);
                                $import['user_id'] = 1;

                                if($import['import_type'] == 'Event')
                                {
                                        $event = $import;
                                        unset($event['import_type']);
                                        
                                        $event['start_time'] = strtotime($event['start_time']);
                                        $event['end_time'] = strtotime($event['end_time']);
                                        
                                        $calendar = $ca->get_calendar_by_name($event['type_name']);                                                                                
                                        if(!$calendar)
                                        {                                               
                                                $calendar['user_id'] = 1;
                                                $calendar['name'] = $event['type_name'];
                                                $calendar['acl_id'] = $GLOBALS['GO_SECURITY']->get_new_acl('calendar read: '.$calendar['name'], $calendar['user_id']);
                                                $calendar['id'] = $ca->add_calendar($calendar);
                                        }
                                        unset($event['type_name'], $event['link_name']);
                                        
                                        $event['calendar_id'] = $calendar['id'];                                       
                                        $link_id = $event_id = $ca->add_event($event);

                                        $cf_values['link_id'] = $event_id;
                                        $cf->replace_row('cf_1', $cf_values);

                                        $num_events++;
                                        
                                }else
                                if($import['import_type'] == 'Note')
                                {
                                        $note = $import;
                                        $note['content'] = $note['description'];

                                        unset(
                                                $note['import_type'],
                                                $note['description'],
                                                $note['location'],
                                                $note['start_time'],
                                                $note['end_time'],
                                                $note['all_day_event'],
                                                $note['private']
                                        );
                                        
                                        $category = $no->get_category_by_name($note['type_name']);
                                        if(!$category)
                                        {
                                                $category['user_id'] = 1;
                                                $category['name'] = $note['type_name'];
                                                $category['acl_id'] = $GLOBALS['GO_SECURITY']->get_new_acl('note-category read: '.$category['name'], $category['user_id']);

                                                $category['id'] = $no->add_category($category);
                                        }
                                        unset($note['type_name'], $note['link_name']);

                                        $note['category_id'] = $category['id'];
                                        $link_id = $note_id = $no->add_note($note);

                                        $cf_values['link_id'] = $note_id;
                                        $cf->replace_row('cf_4', $cf_values);

                                        $num_notes++;
                                }else
                                {                                       
                                        $task = $import;

                                        $task['completion_time'] = $task['due_time'] = $task['start_time'] = strtotime($task['start_time']);
                                        $task['name'] = ($import['import_type'] == 'Task') ? $task['name'] : 'Phonecall: '.$task['name'];

                                        unset(
                                                $task['import_type'],
                                                $task['location'],
                                                $task['end_time'],
                                                $task['all_day_event'],
                                                $task['private']
                                        );

                                        $tasklist = $ta->get_tasklist_by_name($task['type_name']);
                                        if(!$tasklist)
                                        {
                                                $tasklist['user_id'] = 1;
                                                $tasklist['name'] = $task['type_name'];
                                                $tasklist['acl_id'] = $GLOBALS['GO_SECURITY']->get_new_acl('tasklist read: '.$tasklist['name'], $tasklist['user_id']);

                                                $tasklist['id'] = $ta->add_tasklist($tasklist);
                                        }
                                        unset($task['type_name'], $task['link_name']);                                        

                                        $task['tasklist_id'] = $tasklist['id'];
                                        $link_id = $task_id = $ta->add_task($task);

                                        $cf_values['link_id'] = $task_id;
                                        $cf->replace_row('cf_12', $cf_values);

                                        $num_tasks++;
                                }

                                $ab->search_companies(1, $import['link_name']);
                                while($ab->next_record())
                                {
                                        $GO_LINKS->add_link($ab->f('id'), 3, $link_id, $type);
                                }

                                $import['link_name'] = explode(',', $import['link_name']);
                                if(count($import['link_name']) > 1)
                                {
                                        $import['link_name'] = $import['link_name'][1].$import['link_name'][0];
                                }else
                                {
                                        $import['link_name'] = $import['link_name'][0];
                                }
                                
                                $ab->search_contacts(1, $import['link_name'], 'name');
                                while($ab->next_record())
                                {
                                        $GO_LINKS->add_link($ab->f('id'), 2, $link_id, $type);
                                }                                
			}else
                        {
				echo "No name found. Skipping:" . var_export($import, true) . "\n\n";
			}
		} catch (Exception $e) {

		}
	}

        echo "\nNumber of tasks imported: ".$num_tasks."\n";
        echo 'Number of events imported: '.$num_events."\n";
        echo 'Number of notes imported: '.$num_notes."\n\n";
        
	fclose($fp);
}

echo 'Done!';
echo "\n\n";