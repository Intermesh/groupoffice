<?php

namespace GO\Core\Controller;


class ReminderController extends \GO\Base\Controller\AbstractController {
	
	protected function actionSnooze($params){
		$reminderIds = json_decode($params['reminders'], true);
		
		foreach($reminderIds as $id){
			$r=\GO\Base\Model\Reminder::model()->findByPk($id);
			$r->setForUser(\GO::user()->id, time()+$params['snooze_time']);
		}
		$response['success']=true;
		
		return $response;
	}
	
	protected function actionDismiss($params){
		$reminderIds = json_decode($params['reminders'], true);
		
		foreach($reminderIds as $id){
			$r=\GO\Base\Model\Reminder::model()->findByPk($id);
			if($r)
				$r->removeUser(\GO::user()->id);
		}
		$response['success']=true;
		
		return $response;
	}
	
	protected function actionStore($params){
		$params = \GO\Base\Db\FindParams::newInstance()
						->order('vtime')
						->select('t.*')
						->join(\GO\Base\Model\ReminderUser::model()->tableName(),
									\GO\Base\Db\FindCriteria::newInstance()
											->addModel(\GO\Base\Model\Reminder::model())
											->addCondition('id', 'ru.reminder_id','=','t',true, true),
										'ru')						
						->criteria(\GO\Base\Db\FindCriteria::newInstance()
										->addModel(\GO\Base\Model\ReminderUser::model(),'ru')
										->addCondition('user_id', \GO::user()->id,'=','ru')
										->addCondition('time', time(),'<','ru')
										);
		
		$store = \GO\Base\Data\Store::newInstance(\GO\Base\Model\Reminder::model());
		$store->getColumnModel()->setFormatRecordFunction(array($this, 'formatReminderRecord'));
		
		$stmt = \GO\Base\Model\Reminder::model()->find($params);
		
		$store->setStatement($stmt);		
		
		return $store->getData();
	}
	
	public function formatReminderRecord($record, $model, $store){
		
		$record['iconCls']='go-icon-reminders';
		$record['type']=\GO::t("Other");
		$record['model_name']='';

		if(!empty($record['model_type_id'])){
			$modelType = \GO\Base\Model\ModelType::model()->findByPk($record['model_type_id']);
			try {
				if ($modelType && \GO::getModel($modelType->model_name)) {
					$record['iconCls'] = 'go-model-icon-' . $modelType->model_name;
					$record['type'] = \GO::getModel($modelType->model_name)->localizedName;
					$record['model_name'] = $modelType->model_name;
				}
			}
			catch(\Exception $e) {

			}
		}  
		
		$now = \GO\Base\Util\Date::clear_time(time());
		
		$time = $model->vtime ? $model->vtime: $model->time;
		if(\GO\Base\Util\Date::clear_time($time) != $now) {
			$record['local_time']=date(\GO::user()->completeDateFormat,$time);
		}else {
			$record['local_time']=date(\GO::user()->time_format,$time);
		}
		
		$record['text'] = htmlspecialchars_decode($record['text']);
		
		return $record;		
	}
	
	
	protected function actionDisplay($params){
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->select('count(*) AS count')
						->join(\GO\Base\Model\ReminderUser::model()->tableName(),
									\GO\Base\Db\FindCriteria::newInstance()
											->addModel(\GO\Base\Model\Reminder::model())
											->addCondition('id', 'ru.reminder_id','=','t',true, true),
										'ru')						
						->criteria(\GO\Base\Db\FindCriteria::newInstance()
										->addModel(\GO\Base\Model\ReminderUser::model(),'ru')
										->addCondition('user_id', \GO::user()->id,'=','ru')
										->addCondition('time', time(),'<','ru')
										);
		
		$model=\GO\Base\Model\Reminder::model()->findSingle($findParams);		
		
		$html="";
		
		$this->fireEvent('reminderdisplay', array($this, &$html, $params));
		
		$this->render("Reminder", array('count'=>intval($model->count),'html'=>$html));
	}
}
