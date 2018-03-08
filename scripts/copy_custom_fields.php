<?php

define('NO_EVENTS', true);
if (isset($argv[1])) {
	define('CONFIG_FILE', $argv[1]);
}

ini_set('max_execution_time', 0);

require('../www/Group-Office.php');

require_once($GLOBALS['GO_MODULES']->modules['addressbook']['class_path'] . 'addressbook.class.inc.php');
$ab = new addressbook();


require_once($GLOBALS['GO_MODULES']->modules['customfields']['class_path'] . 'customfields.class.inc.php');
$cf = new customfields();



$contact_id_col='col_327';
$ab_col = 'col_396';
$birthday_contact_col = 'col_325';
$birthday_file_col = 'col_397';

$db = new db();
$db2 = new db();

$db->query("SELECT * FROM fs_files f INNER JOIN cf_6 c ON (c.link_id=f.id) WHERE c.$contact_id_col>0");

$count=0;
$limit=10;
while($r = $db->next_record()){

	$contact = $ab->get_contact($r[$contact_id_col]);

	if($contact){
		$addressbook = $ab->get_addressbook($contact['addressbook_id']);

		$custom = $cf->get_values(1, 2, $contact['id'], false, false);

		$ur[$ab_col]=$addressbook['name'];
		$ur[$birthday_file_col]=$custom[$birthday_contact_col];
		$ur['col_324']=$custom['col_398'];
		$ur['link_id']=$r['id'];

		//var_dump($ur);

		$db2->update_row('cf_6','link_id', $ur);

		echo '.';


	}

	$count++;
//	if($count==$limit){
//		break;
//	}


}