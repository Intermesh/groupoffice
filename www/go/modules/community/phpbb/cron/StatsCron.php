<?php
namespace go\modules\community\phpbb\cron;

use go\core\model\CronJob;
use go\core\db\Connection;

class StatsCron extends CronJob {
	
	public function run() {
		$users = \go\core\model\User::find(['id', 'username']);

		$forumConn = $this->getForumDbConnection();

		foreach($users as $user) {
			$forumUserId = $forumConn->selectSingleValue('user_id')->from('phpbb_users')->where(['username_clean' => $user->username])->single();

			if(!$forumUserId) {
				$user->setCustomFields(['isForumUser' => false, 'numberOfPosts' => 0]);				
			}else{
				$numberOfPosts = $forumConn->selectSingleValue('count(*)')->from('phpbb_posts')->where(['poster_id' => $forumUserId, 'post_visibility' => 1])->single();
				$user->setCustomFields(['isForumUser' => true, 'numberOfPosts' => $numberOfPosts]);
			}

			if(!$user->save()) {
				throw new \Exception("Could not save user");
			}
		}
	}

	/**
	 * Get DB connection to forum
	 * 
	 * @return Connection
	 */
	private function getForumDbConnection() {
		$dbName = 'go_forum';

		$db = GO()->getConfig()['core']['db'];
		$dsn = 'mysql:host=' . $db['host'] . ';port=' . $db['port'] . ';dbname=' . $dbName;
		return new Connection($dsn, $db['username'], $db['password']);
	}
}
