<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * The Task controller
 *
 * @package GO.modules.Tasks
 * @version $Id: TaskController.php 7607 2011-09-20 10:09:05Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits <wsmits@intermesh.nl>
 */


namespace GO\Tasks\Controller;

use go\modules\community\comments\model\Comment;

class TaskController extends \GO\Base\Controller\AbstractModelController{
	
	protected $model = 'GO\Tasks\Model\Task';
	
	protected function afterDisplay(&$response, &$model,&$params) {
		$response['data']['user_name']=$model->user ?  \GO\Base\Util\StringHelper::encodeHtml($model->user->name) : '';
		$response['data']['tasklist_name']=  \GO\Base\Util\StringHelper::encodeHtml($model->tasklist->name);
		$statuses = \GO::t("statuses", "tasks");
		$response['data']['status_text']=isset($statuses[$model->status]) ? $statuses[$model->status] : $model->status;
		
		
		$response['data']['late']=$model->isLate();
		
		if($model->percentage_complete>0 && $model->status!='COMPLETED')
			$response['data']['status_text'].= ' ('.$model->percentage_complete.'%)';
		
		$response['data']['project_name']='';
		if(\GO::modules()->projects && $model->project){
			$response['data']['project_name']= \GO\Base\Util\StringHelper::encodeHtml($model->project->name);
		}
		if(\GO::modules()->projects2 && $model->project2){
			$response['data']['project_name']= \GO\Base\Util\StringHelper::encodeHtml($model->project2->name);
		}
		
		
		return parent::afterDisplay($response, $model, $params);
	}
	
	protected function afterLoad(&$response, &$model, &$params) {
		$user = \go\core\App::get()->getAuthState()->getUser();
		if(!empty($model->rrule)) {
			$rRule = new \GO\Base\Util\Icalendar\Rrule();
			$rRule->readIcalendarRruleString($model->due_time, $model->rrule);
			$createdRule = $rRule->createJSONOutput();

			$response['data'] = array_merge($response['data'],$createdRule);
		}else
		{
			$response['data']['repeat_forever'] = 1;
		}
		
		$response['data']['remind_before'] = $user->taskSettings->reminder_days;
		
		if(!empty($response['data']['reminder'])) {			
			$response['data']['remind']=1;
			$response['data']['remind_date']=date(\GO::user()->completeDateFormat, $model->reminder);
			$response['data']['remind_time']=date(\GO::user()->time_format, $model->reminder);
		}	else {
			$response['data']['remind_date']=$model->getDefaultReminder($model->start_time) == 0 ? date(\GO::user()->completeDateFormat):date(\GO::user()->completeDateFormat, $model->getDefaultReminder($model->start_time));
			$response['data']['remind_time']=$model->getDefaultReminder($model->start_time) == 0 ? date(\GO::user()->time_format) : date(\GO::user()->time_format, $model->getDefaultReminder($model->start_time));
		}
		
		if(!empty($params['project_id']) && empty($params['id'])){
			$findParams = \GO\Base\Db\FindParams::newInstance()
							->select('count(*) AS count')
							->single();
			
			$findParams->getCriteria()->addCondition('project_id', $params['project_id']);
			$record = \GO\Tasks\Model\Task::model()->find($findParams);
			
			$response['data']['name']='['.($record->count+1).'] ';
		}
			
		//$response['data']['remind_time']=date(\GO::user()->time_format, strtotime($response['data']['reminder']));
		
		return parent::afterLoad($response, $model, $params);
	}

	protected function beforeSubmit(&$response, &$model, &$params) {
					
		if(isset($params['freq'])){
			if(!empty($params['freq'])){
				$rRule = new \GO\Base\Util\Icalendar\Rrule();
				$rRule->readJsonArray($params);		
				$model->rrule = $rRule->createRrule();
			} else {
				$model->rrule = '';
			}
		}
		
		if(!empty($params['remind'])) // Check for a setted reminder
			$model->reminder= \GO\Base\Util\Date::to_unixtime($params['remind_date'].' '.$params['remind_time']);
		else
			$model->reminder = 0; 
		
		if($model->isNew && empty($params['remind']) && !isset($params['priority'])){ //This checks if it is called from the quickadd bar
		  $model->reminder = $model->getDefaultReminder(\GO\Base\Util\Date::to_unixtime ($params['start_time']));
		}
	  
		return parent::beforeSubmit($response, $model, $params);
	}
	
	protected function afterSubmit(&$response, &$model, &$params, $modifiedAttributes) {		
		if(!empty($params['comment']) && \GO::modules()->comments){
				
			$comment = new  Comment();
			$comment->setEntity('Task');
			$comment->entityId = $model->id;
			$comment->text = $params['comment'];
			if(!$comment->save()) {
				throw new \Exception('Could not save comment: '. var_export($comment->getValidationErrors(), true));
			}
		}
		
		if(\GO::modules()->files){
		 $f = new \GO\Files\Controller\FolderController();
		 $f->processAttachments($response, $model, $params);
		}
		
		return parent::afterSubmit($response, $model, $params, $modifiedAttributes);
	}

	protected function remoteComboFields(){
		$combos= array(
				'category_id'=>'$model->category->name',
				'tasklist_id'=>'$model->tasklist->name'				
				);
		
		if(\GO::modules()->projects)
			$combos['project_id']='$model->project->path';
		if(\GO::modules()->projects2)
			$combos['project_id']='$model->project2->path';
		
		return $combos;
	}


	
	protected function beforeStoreStatement(array &$response, array &$params, \GO\Base\Data\AbstractStore &$store, \GO\Base\Db\FindParams $storeParams) {
		$multiSel = new \GO\Base\Component\MultiSelectGrid(
						'ta-taskslists', 
						"GO\Tasks\Model\Tasklist",$store, $params, true);
		$multiSel->addSelectedToFindCriteria($storeParams, 'tasklist_id');
		$multiSel->setButtonParams($response);
		$multiSel->setStoreTitle();
		
		$catMultiSel = new \GO\Base\Component\MultiSelectGrid(
            'categories', 
            "GO\Tasks\Model\Category",
            $store, 
            $params
        );		
		$catMultiSel->addSelectedToFindCriteria($storeParams, 'category_id');
		
		return parent::beforeStoreStatement($response, $params, $store, $storeParams);
	}
	
	
	protected function beforeStore(&$response, &$params, &$store) {
		
		if(isset($params['completed_task_id'])) {
			$updateTask = \GO\Tasks\Model\Task::model()->findByPk($params['completed_task_id']);
			
			if(isset($params['checked']))
				$updateTask->setCompleted($params['checked']=="true");
		}

//		
		return parent::beforeStore($response, $params, $store);
	}
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		
		$columnModel->formatColumn('completion_time','$model->getAttribute("completion_time","formatted")',array(),'completion_time');
		$columnModel->formatColumn('completed','$model->status=="COMPLETED" ? 1 : 0');
		$columnModel->formatColumn('is_active','$model->isActive()');
//		$columnModel->formatColumn('status', '$l["statuses[\'".$model->status."\']"');
		//$columnModel->formatColumn('category_name','$model->category->name',array(),'category_id');
		$columnModel->formatColumn('tasklist_name','$model->tasklist_name');
		$columnModel->formatColumn('late','$model->isLate();');
		$columnModel->formatColumn('user_name','$model->user->name');
		
		if (\GO::modules()->projects2) {
			$columnModel->formatColumn('project_name','$model->getProjectName()');
		}
		
		return parent::formatColumns($columnModel);
	}

	protected function afterStore(&$response, &$params, &$store, $storeParams) {
		
		if(isset($params['ta-taskslists'])){
			
			$findParams = \GO\Base\Db\FindParams::newInstance()->select('t.id,t.name')->limit(\GO::config()->nav_page_size);
			$findParams->getCriteria()->addInCondition('id', json_decode($params['ta-taskslists']));
			$tasklists = \GO\Tasks\Model\Tasklist::model()->find($findParams);
			
			$response['selectable_tasklists'] = array();
			foreach($tasklists as $tasklist){
				$response['selectable_tasklists'][] = array('data'=>array('id'=>$tasklist->id,'name'=>$tasklist->name));
			}
		}
		
		return parent::afterStore($response, $params, $store, $storeParams);
	}
	
	protected function getStoreParams($params) {
		
		if(!isset($params['show'])){
			$from_setting = \GO::config()->get_setting("tasks_filter", \GO::user()->id);
			if(empty($from_setting))
				$params['show']='active';
			else
				$params['show'] = $from_setting;
		}
		\GO::config()->save_setting('tasks_filter', $params['show'],\GO::user()->id);
		
		$fields = \GO\Tasks\Model\Task::model()->getDefaultFindSelectFields();
		
		$storeParams = \GO\Base\Db\FindParams::newInstance()
			->export("tasks")
			->joinCustomFields()
			->criteria(\GO\Base\Db\FindCriteria::newInstance()
				->addModel(\GO\Tasks\Model\Task::model(),'t')
			)										
			//->select('t.*, tl.name AS tasklist_name')
			->select($fields.', tl.name AS tasklist_name, cat.name AS category_name, completion_time=0 AS incomplete')
			->joinModel(array(
				'model'=>'GO\Tasks\Model\Tasklist',
				'localField'=>'tasklist_id',
				'tableAlias'=>'tl', //Optional table alias
			));
		
		
		$storeParams->joinModel(array(
			'type'=>'LEFT',
			'model'=>'GO\Tasks\Model\Category',
			'localField'=>'category_id',
			'tableAlias'=>'cat', //Optional table alias
		));
		
		$storeParams = $this->checkFilterParams($params['show'],$storeParams);
		
		if(\GO::modules()->projects){
		//	$storeParams->select("t.*, tl.name AS tasklist_name,p.name AS project_name");
			$storeParams->select("t.*, tl.name AS tasklist_name,p.name AS project_name, cat.name AS category_name");
			$storeParams->joinModel(array('model'=>'GO\Projects\Model\Project', 'foreignField'=>'id', 'localField'=>'project_id', 'tableAlias'=>'p',  'type'=>'LEFT' ));
		} elseif(\GO::modules()->projects2) {
			$storeParams->select("t.*, tl.name AS tasklist_name,p.name AS project_name, cat.name AS category_name");
			$storeParams->joinModel(array('model'=>'GO\Projects2\Model\Project', 'foreignField'=>'id', 'localField'=>'project_id', 'tableAlias'=>'p',  'type'=>'LEFT' ));
		}
		
		return $storeParams;
	}
	
	private function checkFilterParams($show, \GO\Base\Db\FindParams $params) {

		// Check for a given filter on the statusses
		if(!empty($show)) {
			$statusCriteria = $params->getCriteria();

			switch($show) {
				case 'today':
					$start_time = mktime(0,0,0);
					$end_time = \GO\Base\Util\Date::date_add($start_time, 1);
					break;

				case 'sevendays':
					$start_time = mktime(0,0,0);
					$end_time = \GO\Base\Util\Date::date_add($start_time, 7);
					$show_completed=false;	
					break;

				case 'overdue':
					$start_time = 0;
					$end_time = mktime(0,0,0);
					$show_completed=false;
					$show_future=false;
					break;

				case 'completed':
					$start_time = 0;
					$end_time = 0;
					$show_completed=true;
					//$show_future=false;
					break;

				case 'future':
					$start_time = 0;
					$end_time = 0;
					$show_completed=false;				
					$show_future=true;
					break;
                  
        case 'incomplete':
					$start_time = 0;
					$end_time = 0;
					$show_completed=false;
					break;

				case 'active':
				case 'portlet':
					$start_time = 0;
					$end_time = 0;
					$show_completed=false;
					$show_future=false;
				break;

				default:
					// Nothing
				break;
			}
			
			if(isset($show_completed)) {
				if($show_completed)
					$statusCriteria->addCondition('completion_time', 0, '>');
				else
					$statusCriteria->addCondition('completion_time', 0, '=');
			}
			
			if(!empty($start_time)) 
				$statusCriteria->addCondition('due_time', $start_time, '>=');
				
			if(!empty($end_time)) 
				$statusCriteria->addCondition('due_time', $end_time, '<');

			if(isset($show_future)) {
				$now = \GO\Base\Util\Date::date_add(mktime(0,0,0),1);
				if($show_future) 
					$statusCriteria->addCondition('start_time', $now, '>=');
				else
					$statusCriteria->addCondition('start_time', $now, '<');
			}
			
			//$params->getCriteria()->mergeWith($statusCriteria);
			//			$params['criteriaObject']=$statusCriteria;
		}
		
//		// Check for a given filter on the categories
//		if(isset($params['categoryFilter'])) {
//			$categoryCriteria = \GO\Base\Db\FindCriteria::newInstance()
//				->addModel(\GO\Tasks\Model\Task::model(),'t');
//			
//			$categories = json_decode($params['categoryFilter'], true);
//			
////			foreach($categories as $category) 
////				$categoryCriteria->addCondition('category_id', $category, '=','t',false);
//			//if(count($categories))
//			$categoryCriteria->addInCondition('category_id', $categories,'t',false,false);
//
//			if(isset($params['criteriaObject']))
//				$params['criteriaObject']->mergeWith($categoryCriteria);
//			else
//				$params['criteriaObject'] = $categoryCriteria;
//		}
		
		return $params;
	}
	
	
	
	protected function actionImportIcs($params){
		
		$file = new \GO\Base\Fs\File($params['file']);
		
		$data = $file->getContents();


		$vcalendar = \GO\Base\VObject\Reader::read($data);
		
		\GO\Base\VObject\Reader::convertVCalendarToICalendar($vcalendar);
		
		foreach($vcalendar->vtodo as $vtodo)
		{			
			$task = new \GO\Tasks\Model\Task();
			$task->importVObject($vtodo);
		}
	}
	
	protected function actionIcs($params) {
		$task = \GO\Tasks\Model\Task::model()->findByPk($params['id']);
		header('Content-Type: text/plain');
		echo $task->toICS();
	}
	
	/**
	 * Move the selected tasks to an other addressbook.
	 * 
	 * @param array $params
	 * @return StringHelper $response
	 */
	protected function actionMove($params){
		$response = array();
		
		if(!empty($params['items']) && !empty($params['tasklist_id'])){
			$items = json_decode($params['items']);
			
			$num_updated = 0;
			$success = true;
			foreach($items as $taskId){
				$task = \GO\Tasks\Model\Task::model()->findByPk($taskId);
				$task->tasklist_id = $params['tasklist_id'];
				$success = $success&&$task->save();
				
				$num_updated++;
			}
			
			if($num_updated > 0)
				$response['reload_store'] = true;
			
			$response['success'] = $success;
			
		}		
		
		return $response;
	}
	
	
	public function actionExportIcs($params){
				
		$tasklist = \GO\Tasks\Model\Tasklist::model()->findByPk($params["tasklist_id"]);		
		
		if(!$tasklist->checkPermissionLevel(\GO\Base\Model\Acl::READ_PERMISSION)) {
			throw new \GO\Base\Exception\AccessDenied();
		}
		
		$findParams = \GO\Base\Db\FindParams::newInstance();
		$findParams->getCriteria()->addCondition("tasklist_id", $tasklist->id);
		
		$stmt = \GO\Tasks\Model\Task::model()->find($findParams);	
		
		
		\GO\Base\Util\Http::outputDownloadHeaders(new \GO\Base\FS\File($tasklist->name.'.ics'));
		
		//start vtodo
		echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Intermesh//NONSGML ".\GO::config()->product_name." ".\GO::config()->version."//EN\r\n";
		
		
		$t = new \GO\Base\VObject\VTimezone();
		echo $t->serialize();
		
		while($event = $stmt->fetch()){
			$v = $event->toVObject();
			echo $v->serialize();
		}
		
		echo "END:VCALENDAR\r\n";
	}
	
}
	
