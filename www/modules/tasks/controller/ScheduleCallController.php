<?php


namespace GO\Tasks\Controller;


class ScheduleCallController extends \GO\Base\Controller\AbstractJsonController {

	protected function actionLoad($params){
		$scheduleCall = new \GO\Tasks\Model\Task();

		$remoteComboFields = array(
				'category_id'=>'$model->category->name',
				'tasklist_id'=>'$model->tasklist->name'				
		);
		
		if(\GO::modules()->projects)
			$remoteComboFields['project_id']='$model->project->path';
		
		echo $this->renderForm($scheduleCall,$remoteComboFields);
	}
	
	protected function actionSave($params){		
		
		if(empty($params['number']) || empty($params['remind_date']) || empty($params['remind_time']))
			throw new \Exception('Not all parameters are given');

		$scheduleCall = new \GO\Tasks\Model\Task();
				
		$scheduleCall->setAttributes($params);
				
		// Check if the contact_id is really an ID or if it is a name. (The is_contact is true when it is an ID) 
		if(!empty($params['contact_id'])){
			$contact = \GO\Addressbook\Model\Contact::model()->findByPk($params['contact_id']);
			
			if(!empty($params['number']) && !empty($params['save_as'])){
				$contact->{$params['save_as']} = $params['number'];
				$contact->save();
			}
			
			$name = $contact->name;
		}else{ 
			$name = $params['contact_name'];
		}
		
		$scheduleCall->name = str_replace(array('{name}','{number}'),array($name, $params['number']),\GO::t('scheduleCallTaskName','tasks'));
		$scheduleCall->reminder= \GO\Base\Util\Date::to_unixtime($params['remind_date'].' '.$params['remind_time']);
		
		$scheduleCall->save();
		
		if(isset($contact))
			$scheduleCall->link($contact);
		
		echo $this->renderSubmit($scheduleCall);
	}
}

?>
