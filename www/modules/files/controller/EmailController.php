<?php
namespace GO\Files\Controller;

use GO;

class EmailController extends GO\Base\Controller\AbstractJsonController {
	
	protected function actionCheckDeleteCron( $params ) {
		
		
		$cronJob = \go\core\model\CronJobSchedule::findByName('DeleteExpiredLinksJob', 'legacy', 'files');
		
		if (!$cronJob) {
			echo json_encode(array('success'=>true,'data'=>array('enabled'=>false,'reason'=>'noCronJob')));
			return;
		}
		
		echo json_encode(array('success'=>true,'data'=>array('enabled'=>$cronJob->enabled)));
		
	}
	
}
