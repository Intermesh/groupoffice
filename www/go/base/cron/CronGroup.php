<?php

namespace GO\Base\Cron;


class CronGroup extends \GO\Base\Db\ActiveRecord {

	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function primaryKey() {
		return array('cronjob_id','group_id');
	}
	
	public function tableName() {
		return 'go_cron_groups';
	}
	
	public function relations(){
		return array(	
			'cronjob' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Base\Cron\CronJob', 'field'=>'group_id', 'linkModel' => 'GO\Base\Model\Group'),
    );
	}
}
