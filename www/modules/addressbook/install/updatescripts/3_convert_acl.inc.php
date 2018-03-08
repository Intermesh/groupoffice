<?php
require_once($GLOBALS['GO_CONFIG']->root_path.'install/updatescripts/functions.inc.php');

$db->query("SELECT * FROM ab_addressbooks");
while($folder=$db->next_record()){
	apply_write_acl($folder['acl_read'], $folder['acl_write']);
}

$db->query("ALTER TABLE `ab_addressbooks` CHANGE `acl_read` `acl_id` INT( 11 ) NOT NULL DEFAULT '0'");
