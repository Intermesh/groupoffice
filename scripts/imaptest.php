<?php
require('../www/Group-Office.php');
require($GLOBALS['GO_CONFIG']->class_path.'mail/imap.class.inc.php');

$imap = new imap();
$ret = $imap->connect('localhost', 143, 'test@intermesh.dev', 'test', false);

var_dump($ret);
//$folders = $imap->get_folders();

$mailbox = $imap->select_mailbox('INBOX');
//var_dump($mailbox);

//$unseen = $imap->get_mailbox_unseen('INBOX');
//var_dump($unseen);

$uids = $imap->sort_mailbox('ARRIVAL', true);

//$uids = array_splice($uids, 0, 1);

//echo count($uids);


//$headers = $imap->get_message_headers(array($uids[0]));
//var_dump($headers);

$struct = $imap->get_message_structure($uids[4]);
var_dump($struct);
echo $imap->get_message_part($uids[4], '1.2');

$imap->disconnect();
