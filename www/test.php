<?php
define('GO_CONFIG_FILE' , '/etc/groupoffice/config.php');
require('GO.php');

GO::session()->runAsRoot();

$account = \GO\Email\Model\Account::model()->findByPk(3);
$conn = $account->openImapConnection();

//$uids = $conn->search('SINCE ' . date("j-M-Y", strtotime("-1 month")));
$headers = $conn->get_flags('1:*');
$messages = [];
/* Create messages array */
				foreach ($headers as $header) {
					
					$message = array();
					$message["mod"] = $header['date'];
					$message["id"] = $header['uid'];
					// 'flagged' aka 'FollowUp' aka 'starred'
					$message["star"] = in_array("\Flagged", $header['flags']);
					// 'seen' aka 'read' is the only flag we want to know about
					$message["flags"] = in_array("\Seen", $header['flags']);

					$messages[] = $message;
				}
				
				var_dump($messages);
				
				var_dump(memory_get_peak_usage());