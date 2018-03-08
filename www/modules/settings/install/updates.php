<?php
$updates["201206131430"][]="UPDATE `go_settings` AS s1, (SELECT value FROM go_settings WHERE name='calendar_name_template') AS s2 SET s1.value=s2.value
WHERE s1.name = 'name_template_GO_Calendar_Model_Calendar';";
$updates["201206131430"][]="UPDATE `go_settings` AS s1, (SELECT value FROM go_settings WHERE name='task_name_template') AS s2 SET s1.value=s2.value
WHERE s1.name = 'name_template_GO_Tasks_Model_Tasklist';";
$updates["201206131430"][]="UPDATE `go_settings` AS s1, (SELECT value FROM go_settings WHERE name='addressbook_name_template') AS s2 SET s1.value=s2.value
WHERE s1.name = 'name_template_GO_Addressbook_Model_Addressbook';";
$updates["201206131430"][]="DELETE FROM `go_settings` WHERE name='calendar_name_template' OR name='task_name_template' OR name='addressbook_name_template'";