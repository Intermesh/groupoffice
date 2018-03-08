<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/**
 * 
 * The Note controller
 * 
 */

namespace GO\Reminders\Controller;


class ReminderController extends \GO\Base\Controller\AbstractModelController{
	
	protected $model = 'GO\Base\Model\Reminder';
	
	public function formatStoreRecord($record, $model, $store) {
		$record['time'] = \GO\Base\Util\Date::format_long_date($model->time);
		return parent::formatStoreRecord($record, $model, $store);
	}
		
	protected function getStoreParams($params) {
		
		return \GO\Base\Db\FindParams::newInstance()
						->select('t.*')
						->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('manual','1'));
	}
		
	protected function beforeSubmit(&$response, &$model, &$params) {
		if(!empty($params['link']))
		{
			$link = explode(':', $params['link']);
			$params['model_id']=$link[1];
			$params['model_type_id']= \GO::getModel($link[0])->modelTypeId();
		}else
		{
			$params['model_id']=0;
			$params['model_type_id']=0;
		}
		
		$params['manual'] = 1;
		$params['vtime']=$params['time']=$params['date'].' '.$params['time'];
		
		return parent::beforeSubmit($response, $model, $params);
	}
	
	protected function actionReminderUsers($params) {
		
		\GO::setIgnoreAclPermissions();
		
		$reminderModel = \GO\Base\Model\Reminder::model()->findByPk($params['reminder_id']);
		$response['success'] = true;
		$response['total'] = 0;
		$response['results'] = array();
		$addUserIds = isset($params['add_users']) ? json_decode($params['add_users']) : array();
		$delUserIds = isset($params['delete_keys']) ? json_decode($params['delete_keys']) : array();
		$addGroupIds = isset($params['add_groups']) ? json_decode($params['add_groups']) : array();
		
		try {
			$response['deleteSuccess']=true;
			foreach($delUserIds as $delUserId)
			{
				$reminderModel->removeManyMany('users', $delUserId);
			}
		} catch(\Exception $e) {
			$response['deleteSuccess']=false;
			$response['deleteFeedback']=$e->getMessage();
		}

		foreach ($addGroupIds as $addGroupId) {
			$groupModel = \GO\Base\Model\Group::model()->findByPk($addGroupId);
			$stmt = $groupModel->users;
			while ($userModel = $stmt->fetch()) {
				if (!in_array($userModel->id,$addUserIds)) {
					$addUserIds[] = $userModel->id;
				}
			}
		}
		
		foreach ($addUserIds as $addUserId) {
			$remUserModel = \GO\Base\Model\ReminderUser::model()
				->findSingleByAttributes(array(
					'user_id' => $addUserId,
					'reminder_id' => $reminderModel->id
				));
			if (empty($remUserModel))
				$remUserModel = new \GO\Base\Model\ReminderUser();
			
			$remUserModel->setAttributes(array(
					'reminder_id'	=> $reminderModel->id,
					'user_id' => $addUserId,
					'time' => $reminderModel->time
				));
			$remUserModel->save();
		}
		
		if (!empty($reminderModel->users)) {
			$stmt = $reminderModel->users;
			while ($remUserModel = $stmt->fetch()) {
				$response['results'][] = array(
					'id' => $remUserModel->id,
					'name' => $remUserModel->name
				);
				$response['total']+=1;
			}
		}
		
		return $response;
		
	}

	protected function beforeLoad(&$response, &$model, &$params) {
		if($model->model_id>0){
			$modelType = \GO\Base\Model\ModelType::model()->findByPk($model->model_type_id);
			
			$response['data']['link']=$modelType->model_name.':'.$model->model_id;
			$searchCacheRecord = \GO\Base\Model\SearchCacheRecord::model()
				->findSingleByAttributes( array('model_id' => $model->model_id,'model_type_id' => $model->model_type_id) );
			
			if ($searchCacheRecord)
				$response['data']['link_name'] = $searchCacheRecord->name;
		}else
		{
			$response['data']['link_name'] = '';
		}			
		return $response;
	}
	
	protected function afterLoad(&$response, &$model, &$params) {
		$response['data']['date'] = date(\GO::user()->completeDateFormat, $model->time);
		$response['data']['time'] = date(\GO::user()->time_format, $model->time);
		return parent::afterLoad($response, $model, $params);
	}
	
}

