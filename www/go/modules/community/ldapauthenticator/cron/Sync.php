<?php
namespace go\modules\community\ldapauthenticator\cron;

use go\core\model\CronJob;
use go\modules\community\ldapauthenticator\cli\controller\Sync as SyncCtrl;

class Sync extends CronJob {
  
	public function run(\go\core\model\CronJobSchedule $schedule) {
    $records = go()->getDbConnection()
    ->select('id,syncGroups,syncUsers,syncUsersDelete,syncGroupsDelete,syncUsersMaxDeletePercentage,syncGroupsMaxDeletePercentage')
    ->from('ldapauth_server')
    ->where('syncUsers = true OR syncGroups = true');

    $c = new SyncCtrl();

    foreach($records as $record) {
      if($record['syncUsers']) {
        $c->users([
					'id' => $record['id'],
	        'dryRun' => false,
	        'delete' => !empty($record['syncUsersDelete']),
	        'maxDeletePercentage' => $record['syncUsersMaxDeletePercentage']
        ]);
      }

      if($record['syncGroups']) {
	      $c->groups([
		      'id' => $record['id'],
		      'dryRun' => false,
		      'delete' => !empty($record['syncGroupsDelete']),
		      'maxDeletePercentage' => $record['syncGroupsMaxDeletePercentage']
	      ]);
      }
    }
	}
}
