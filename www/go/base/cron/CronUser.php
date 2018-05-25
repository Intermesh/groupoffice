<?php

namespace GO\Base\Cron;


class CronUser extends \GO\Base\Db\ActiveRecord {

	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function primaryKey() {
		return array('cronjob_id','user_id');
	}
	
	public function tableName() {
		return 'go_cron_users';
	}
	
	public function relations(){
		return array(	
			'cronjob' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Base\Cron\CronJob', 'field'=>'cronjob_id'),
    );
	}
}
