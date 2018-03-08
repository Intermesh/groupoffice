<?php
/*
 * This script will subscribe all IMAP folders of Group-Office users.
 *  It will also set the special folders to the names specified below.
 *
 * To do this for all users just run this script without parameters. To do it
 * for one username do:
 *
 * php fiximapsubscribtions.php --username=username
 */

$sent='Gesendet';
$drafts='Entwürfe';
$trash='Papierkorb';

$go='../www/';
require($go.'Group-Office.php');

chdir(dirname(__FILE__));
require($go.'cli-functions.inc.php');

require_once($GLOBALS['GO_CONFIG']->class_path.'base/users.class.inc.php');
$GO_USERS = new GO_USERS();

$GLOBALS['GO_SECURITY']->logged_in($GO_USERS->get_user(1));

require($GLOBALS['GO_CONFIG']->class_path.'mail/imap.class.inc');
require_once ($GLOBALS['GO_MODULES']->modules['email']['class_path']."cached_imap.class.inc.php");
require_once ($GLOBALS['GO_MODULES']->modules['email']['class_path']."email.class.inc.php");
require_once ($GLOBALS['GO_LANGUAGE']->get_language_file('email'));

$email = new email();
$email2 = new email();
$email->mail = new cached_imap();

$args = parse_cli_args($argv);

$user_id=0;
if(isset($args['username'])){
	$user = $GO_USERS->get_user_by_username($args['username']);
	$user_id=$user['id'];
}

$count = $email->get_accounts($user_id);

while($email->next_record()) {
	try{
		$account = $email->mail->open_account($email->f('id'), 'INBOX', false);

		echo "Processing ". $account['username']."\n";

		if($account) {
			$mailboxes =  $email->mail->get_folders($account['mbroot']);
			$subscribed =  $email->mail->get_folders($account['mbroot'], true);

			$mailbox_names = array();
			foreach($mailboxes  as $mailbox) {
				$mailbox_names[]=$mailbox['name'];
			}

			$subscribed_names = array();
			foreach($subscribed as $mailbox) {
				$subscribed_names[]=$mailbox['name'];
			}
			
			$up_account['id']=$account['id'];

			if($email->_add_folder($account['mbroot'].$sent, $mailbox_names, $subscribed_names)) {
				$up_account['sent'] = $account['mbroot'].$sent;
			}
			if($email->_add_folder($account['mbroot'].$drafts, $mailbox_names, $subscribed_names)) {
				$up_account['drafts'] = $account['mbroot'].$drafts;
			}
			if($email->_add_folder($account['mbroot'].$trash, $mailbox_names, $subscribed_names)) {
				$up_account['trash'] = $account['mbroot'].$trash;
			}

			foreach($mailbox_names as $mailbox){
				if(!in_array($mailbox, $subscribed_names)){
					$email->mail->subscribe($mailbox);
				}
			}

			$subscribed =  $email->mail->get_folders($account['mbroot'], true);

			$email->_synchronize_folders($account, $mailboxes, $subscribed);

			$email->mail->disconnect();

			$email2->_update_account($up_account);
		}else {
			$email->mail->clear_errors();
		}
	}
	catch(Exception $e){
		echo $e->getMessage()."\n";
	}
}


