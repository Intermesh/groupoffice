<?php
namespace GO\Files\Controller;

use GO;

class EmailController extends GO\Base\Controller\AbstractJsonController {
	
	protected function actionCheckDeleteCron( $params ) {
		
		
		$cronJob = GO\Base\Cron\CronJob::model()->findSingleByAttribute('job','GO\Files\Cron\DeleteExpiredLinks');
		
		if (!$cronJob) {
			echo json_encode(array('success'=>true,'data'=>array('enabled'=>false,'reason'=>'noCronJob')));
			return;
		}
		
		echo json_encode(array('success'=>true,'data'=>array('enabled'=>$cronJob->active)));
		
	}
	
}
